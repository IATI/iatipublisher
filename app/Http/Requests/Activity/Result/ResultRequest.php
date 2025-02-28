<?php

declare(strict_types=1);

namespace App\Http\Requests\Activity\Result;

use App\Http\Requests\Activity\ActivityBaseRequest;
use App\IATI\Services\Activity\ResultService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use JsonException;

/**
 * Class ResultRequest.
 */
class ResultRequest extends ActivityBaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     *
     * @throws BindingResolutionException
     */
    public function rules(): array
    {
        $data = request()->except(['_token']);

        $totalRules = [
            $this->getWarningForResult($data),
            $this->getErrorsForResult($data),
        ];

        return mergeRules($totalRules);
    }

    /**
     * Get the error message as required.
     *
     * @return array
     */
    public function messages(): array
    {
        return $this->getMessagesForResult(request()->except(['_token']));
    }

    /**
     * Returns rules for result.
     *
     * @param  array  $formFields
     * @param $fileUpload
     * @param  array  $indicators
     * @param  null  $resultId
     *
     * @return array
     *
     * @throws BindingResolutionException
     */
    public function getWarningForResult(
        array $formFields,
        bool $fileUpload = false,
        array $indicators = [],
        $resultId = null
    ): array {
        $rules = [];

        $tempRules = [
            $this->getWarningForNarrative($formFields['title'][0]['narrative'], 'title.0'),
            $this->getWarningForNarrative($formFields['description'][0]['narrative'], 'description.0'),
            $this->getWarningForDocumentLink($formFields['document_link']),
            $this->getWarningForReferences($formFields['reference'], $fileUpload, $indicators, $resultId),
        ];

        foreach ($tempRules as $key => $tempRule) {
            foreach ($tempRule as $idx => $rule) {
                $rules[$idx] = $rule;
            }
        }

        return $rules;
    }

    /**
     * Returns critical rules for result.
     *
     * @param  array  $formFields
     * @param $fileUpload
     * @param  array  $indicators
     *
     * @return array
     *
     * @throws JsonException
     */
    public function getErrorsForResult(array $formFields, bool $fileUpload = false, array $indicators = []): array
    {
        $rules = [];
        $rules['type'] = sprintf(
            'nullable|in:%s',
            implode(
                ',',
                array_keys(
                    $this->getCodeListForRequestFiles('ResultType', 'Activity', false)
                )
            )
        );
        $rules['aggregation_status'] = sprintf('nullable|in:0,1');

        $tempRules = [
            $this->getErrorsForNarrative(
                Arr::get($formFields, 'title.0.narrative', []),
                'title.0'
            ),
            $this->getErrorsForNarrative(
                Arr::get($formFields, 'description.0.narrative', []),
                'description.0'
            ),
            $this->getErrorsForDocumentLink(Arr::get($formFields, 'document_link')),
            $this->getErrorsForReferences(Arr::get($formFields, 'reference', []), $fileUpload, $indicators),
        ];

        foreach ($tempRules as $key => $tempRule) {
            foreach ($tempRule as $idx => $rule) {
                $rules[$idx] = $rule;
            }
        }

        return $rules;
    }

    /**
     * Returns messages for transaction validations.
     *
     * @param  array  $formFields
     * @param  array  $fileUpload
     * @param  array  $resultId
     *
     * @return array
     */
    public function getMessagesForResult(array $formFields, bool $fileUpload = false, $resultId = null): array
    {
        $messages = [];

        $tempMessages = [
            $this->getMessagesForNarrative(
                Arr::get($formFields, 'title.0.narrative', []),
                'title.0'
            ),
            $this->getMessagesForNarrative(
                Arr::get($formFields, 'description.0.narrative', []),
                'description.0'
            ),
            $this->getMessagesForDocumentLink(Arr::get($formFields, 'document_link', [])),
            $this->getMessagesForReferences(Arr::get($formFields, 'reference', []), $fileUpload, $resultId),
        ];

        foreach ($tempMessages as $key => $tempMessage) {
            foreach ($tempMessage as $idx => $message) {
                $messages[$idx] = $message;
            }
        }

        return $messages;
    }

    /**
     * returns rules for Reference.
     *
     * @param $formFields
     * @param $fileUpload
     * @param  array  $indicators
     * @param  int|null  $resultId
     *
     * @return array
     *
     * @throws BindingResolutionException
     */
    protected function getWarningForReferences(
        $formFields,
        $fileUpload = false,
        array $indicators = [],
        int $resultId = null
    ): array {
        $resultService = app()->make(ResultService::class);
        $rules = [];

        Validator::extendImplicit(
            'indicator_ref_code_present',
            function () {
                return false;
            }
        );

        Validator::extendImplicit(
            'indicator_ref_vocabulary_present',
            function () {
                return false;
            }
        );

        if ($fileUpload) {
            $hasResultId = (bool) $resultId;
        } else {
            $params = $this->route()->parameters();
            $hasResultId = array_key_exists('resultId', $params);
            $resultId = $hasResultId ? Arr::get($params, 'resultId') : $resultId;
        }

        foreach ($formFields as $referenceIndex => $reference) {
            $referenceForm = sprintf('reference.%s', $referenceIndex);

            if (!is_array_value_empty($reference)) {
                if ($fileUpload) {
                    $hasCode = false;
                    $hasVocabulary = false;

                    foreach ((Arr::get($indicators, 'reference', [])) as $ref) {
                        if (Arr::get($ref, 'code', false)) {
                            $hasCode = true;
                            break;
                        }

                        if (Arr::get($ref, 'vocabulary', false)) {
                            $hasVocabulary = true;
                            break;
                        }
                    }

                    $codePresent = $hasResultId ? $resultService->indicatorHasRefCode($resultId) : $hasCode;
                    $vocabularyPresent = $hasResultId ? $resultService->indicatorHasRefVocabulary(
                        $resultId
                    ) : $hasVocabulary;

                    $rules[sprintf('%s.code', $referenceForm)][] = $codePresent ? 'indicator_ref_code_present' : false;
                    $rules[sprintf(
                        '%s.vocabulary',
                        $referenceForm
                    )][]
                        = $vocabularyPresent ? 'indicator_ref_vocabulary_present' : false;
                } else {
                    $rules[sprintf('%s.code', $referenceForm)]
                        = $hasResultId && $resultService->indicatorHasRefCode(
                            $resultId
                        ) ? 'indicator_ref_code_present' : false;
                    $rules[sprintf(
                        '%s.vocabulary',
                        $referenceForm
                    )]
                        = $hasResultId && $resultService->indicatorHasRefVocabulary(
                            $resultId
                        ) ? 'indicator_ref_vocabulary_present' : false;
                }
            }
        }

        return $rules;
    }

    /**
     * returns critical rules for Reference.
     *
     * @param $formFields
     * @param $fileUpload
     * @param  array  $indicators
     *
     * @return array
     * @throws JsonException
     */
    protected function getErrorsForReferences($formFields, $fileUpload = false, array $indicators = []): array
    {
        $rules = [];

        foreach ($formFields as $referenceIndex => $reference) {
            $referenceForm = sprintf('reference.%s', $referenceIndex);
            $rules[sprintf('%s.vocabulary_uri', $referenceForm)] = 'nullable|url';
            $rules[sprintf('%s.vocabulary', $referenceForm)] = sprintf(
                'nullable|in:%s',
                implode(
                    ',',
                    array_keys(
                        $this->getCodeListForRequestFiles('ResultVocabulary', 'Activity')
                    )
                )
            );
        }

        return $rules;
    }

    /**
     * returns messages for Reference.
     *
     * @param $formFields
     * @param $fileUpload
     * @param $resultId
     *
     * @return array
     */
    protected function getMessagesForReferences($formFields, $fileUpload = false, $resultId = null): array
    {
        $messages = [];

        if ($fileUpload) {
            $hasResultId = (bool) $resultId;
        } else {
            $params = $this->route()->parameters();
            $hasResultId = array_key_exists('resultId', $params);
        }

        foreach ($formFields as $referenceIndex => $reference) {
            $referenceForm = sprintf('reference.%s', $referenceIndex);
            $messages[sprintf(
                '%s.vocabulary_uri.url',
                $referenceForm
            )]
                = trans('validation.url_valid');

            if (!empty($reference['code']) && $hasResultId) {
                $messages[sprintf(
                    '%s.code.indicator_ref_code_present',
                    $referenceForm
                )]
                    = trans('validation.activity_results.reference.code_present');
            }

            if (!empty($reference['vocabulary']) && $hasResultId) {
                $messages[sprintf(
                    '%s.vocabulary.indicator_ref_vocabulary_present',
                    $referenceForm
                )]
                    = trans('validation.activity_results.reference.vocabulary_present');
            }
        }

        return $messages;
    }
}
