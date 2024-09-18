<?php

declare(strict_types=1);

namespace App\Http\Requests\Activity\Period;

use App\Http\Requests\Activity\ActivityBaseRequest;
use App\IATI\Services\Activity\IndicatorService;
use App\Rules\RequiredEitherNumericTargetValueOrActualValue;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

/**
 * Class PeriodRequest.
 */
class PeriodRequest extends ActivityBaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     * @throws BindingResolutionException
     */
    public function rules(): array
    {
        $data = request()->except(['_token']);
        $totalRules = [$this->getWarningForPeriod($data), $this->getErrorsForPeriod($data)];

        return mergeRules($totalRules);
    }

    /**
     * Get the error message as required.
     *
     * @return array
     * @throws BindingResolutionException
     */
    public function messages(): array
    {
        return $this->getMessagesForPeriod(request()->except(['_token']));
    }

    /**
     * Returns rules for result indicator.
     *
     * @param  array  $formFields
     * @param  bool  $fileUpload
     * @param  array  $indicator
     * @param  array  $periodBase
     * @param $indicatorId
     *
     * @return array
     * @throws BindingResolutionException
     */
    public function getWarningForPeriod(
        array $formFields,
        bool $fileUpload = false,
        array $indicator = [],
        $periodBase = [],
        $indicatorId = null
    ): array {
        $rules = [];

        $tempRules = [
            $this->getWarningForIndicatorPeriodStart($formFields['period_start'], 'period_start'),
            $this->getWarningForIndicatorPeriodEnd($formFields['period_end'], 'period_end', $periodBase),
            $this->getWarningForTargetOrActual($formFields, 'target', $fileUpload, $indicator, $indicatorId),
            $this->getWarningForTargetOrActual($formFields, 'actual', $fileUpload, $indicator, $indicatorId),
        ];

        foreach ($tempRules as $index => $tempRule) {
            foreach ($tempRule as $idx => $rule) {
                $rules[$idx] = $rule;
            }
        }

        return $rules;
    }

    /**
     * Returns rules for result indicator.
     *
     * @param  array  $formFields
     * @param  bool  $fileUpload
     * @param  array  $indicator
     * @param  array  $periodBase
     *
     * @return array
     * @throws BindingResolutionException
     */
    public function getErrorsForPeriod(
        array $formFields,
        bool $fileUpload = false,
        array $indicator = [],
        $periodBase = []
    ): array {
        $rules = [];
        $tempRules = [
            $this->getErrorsForResultPeriodStart($formFields['period_start'], 'period_start'),
            $this->getErrorsForResultPeriodEnd($formFields['period_end'], 'period_end', $periodBase),
            $this->getErrorsForTarget($formFields['target'], 'target', $fileUpload, $indicator),
            $this->getErrorsForTarget($formFields['actual'], 'actual', $fileUpload, $indicator),
        ];

        foreach ($tempRules as $index => $tempRule) {
            foreach ($tempRule as $idx => $rule) {
                $rules[$idx] = $rule;
            }
        }

        return $rules;
    }

    /**
     * Returns messages for result indicator validations.
     *
     * @param  array  $formFields
     * @param  bool  $fileUpload
     * @param  array  $indicator
     * @param $indicatorId
     *
     * @return array
     * @throws BindingResolutionException
     */
    public function getMessagesForPeriod(
        array $formFields,
        bool $fileUpload = false,
        array $indicator = [],
        $indicatorId = null
    ): array {
        $messages = [];
        $tempMessages = [
            $this->getMessagesForResultPeriod($formFields['period_start'], 'period_start'),
            $this->getMessagesForResultPeriod($formFields['period_end'], 'period_end'),
            $this->getMessagesForTargetOrActual(
                $formFields['target'],
                'target',
                $fileUpload,
                $indicator,
                $indicatorId
            ),
            $this->getMessagesForTargetOrActual(
                $formFields['actual'],
                'actual',
                $fileUpload,
                $indicator,
                $indicatorId
            ),
        ];

        foreach ($tempMessages as $index => $tempMessage) {
            foreach ($tempMessage as $idx => $message) {
                $messages[$idx] = $message;
            }
        }

        return $messages;
    }

    /**
     * returns rules for period start.
     *
     * @param $formFields
     * @param $periodType
     *
     * @return array
     */
    protected function getWarningForIndicatorPeriodStart($formFields, $periodType): array
    {
        $rules = [];

        foreach ($formFields as $periodStartKey => $periodStartVal) {
            $rules[sprintf('%s.%s.date', $periodType, $periodStartKey)] = 'nullable|date_greater_than:1900';
        }

        return $rules;
    }

    /**
     * returns rules for period start.
     *
     * @param $formFields
     * @param $periodType
     *
     * @return array
     */
    protected function getErrorsForResultPeriodStart($formFields, $periodType): array
    {
        $rules = [];

        foreach ($formFields as $periodStartKey => $periodStartVal) {
            $rules[sprintf('%s.%s.date', $periodType, $periodStartKey)] = 'nullable|date';
        }

        return $rules;
    }

    /**
     * returns rules for period end.
     *
     * @param $formFields
     * @param $periodType
     *
     * @return array
     */
    protected function getWarningForIndicatorPeriodEnd($formFields, $periodType, $periodBase): array
    {
        $rules = [];

        foreach ($formFields as $periodEndKey => $periodEndVal) {
            $date = $periodBase ?
                $periodBase . '.period_start.' . $periodEndKey . '.date' : 'period_start.' . $periodEndKey . '.date';

            if ($periodBase) {
                $rules[sprintf('%s.%s.date', $periodType, $periodEndKey)] = sprintf(
                    'nullable|date_greater_than:1900|after:%s',
                    $date
                );
            } else {
                $rules[sprintf('%s.%s.date', $periodType, $periodEndKey)]
                    = sprintf('nullable|after:%s', $date);
            }
        }

        return $rules;
    }

    /**
     * returns rules for period end.
     *
     * @param $formFields
     * @param $periodType
     *
     * @return array
     */
    protected function getErrorsForResultPeriodEnd($formFields, $periodType, $periodBase): array
    {
        $rules = [];

        foreach ($formFields as $periodEndKey => $periodEndVal) {
            if ($periodBase) {
                $rules[sprintf('%s.%s.date', $periodType, $periodEndKey)] = 'nullable|date';
            } else {
                $rules[sprintf('%s.%s.date', $periodType, $periodEndKey)] = 'nullable|date';
            }
        }

        return $rules;
    }

    /**
     * returns messages for period start and period end.
     *
     * @param $formFields
     * @param $periodType
     *
     * @return array
     */
    protected function getMessagesForResultPeriod($formFields, $periodType): array
    {
        $messages = [];

        foreach ($formFields as $periodStartKey => $periodStartVal) {
            $messages[sprintf(
                '%s.%s.date.date',
                $periodType,
                $periodStartKey
            )]
                = trans('validation.date_is_invalid');

            $messages[sprintf(
                '%s.%s.date.after',
                $periodType,
                $periodStartKey
            )]
                = trans('validation.activity_periods.date.after');

            $messages[sprintf(
                '%s.%s.date.date_greater_than',
                $periodType,
                $periodStartKey
            )]
                = trans('validation.date_must_be_after_1900');

            $messages[sprintf(
                '%s.%s.date.period_start_end',
                $periodType,
                $periodStartKey
            )]
                = trans('validation.period_end_cannot_be_more_than_one_year');
        }

        return $messages;
    }

    /**
     * returns rules for Target.
     *
     * @param $formFields
     * @param $valueType
     * @param $fileUpload
     * @param $indicator
     * @param $indicatorId
     *
     * @return array
     * @throws BindingResolutionException
     */
    protected function getWarningForTargetOrActual(
        $formFields,
        $valueType,
        $fileUpload,
        $indicator,
        $indicatorId
    ): array {
        $targetOrActualFields = $formFields[$valueType];
        $fieldToValidateAgainst = $valueType === 'target' ? 'actual' : 'target';

        /** @var IndicatorService $indicatorService */
        $rules = [];
        $indicatorService = app()->make(IndicatorService::class);

        if ($fileUpload) {
            $measure = $indicatorId ? $indicatorService->getIndicatorMeasureType($indicatorId) : Arr::get(
                $indicator,
                'measure'
            );
            $indicatorMeasureType = is_array($measure) ? $measure : [
                'qualitative'     => $measure === '5',
                'non_qualitative' => in_array($measure, ['1', '2', '3', '4']),
            ];
        } else {
            $params = $this->route()->parameters();
            $indicatorMeasureType = $indicatorService->getIndicatorMeasureType($params['id']);
        }

        Validator::extend('qualitative_empty', function () {
            return false;
        });

        /*
         * The variables names are not factual.
         * DO NOT get confused with the variable names.
         * Since this method is called for both actual and target fields.
         */
        foreach ($targetOrActualFields as $targetIndex => $target) {
            $targetForm = sprintf('%s.%s', $valueType, $targetIndex);
            $narrativeRules = $this->getWarningForNarrative(
                $target['comment'][0]['narrative'],
                sprintf('%s.comment.0', $targetForm)
            );
            $docLinkRules = $this->getWarningForDocumentLink(Arr::get($target, 'document_link', []), $targetForm);

            foreach ($narrativeRules as $key => $narrativeRule) {
                $rules[$key] = $narrativeRule;
            }

            foreach ($docLinkRules as $key => $docLinkRule) {
                $rules[$key] = $docLinkRule;
            }

            if ($indicatorMeasureType['non_qualitative']) {
                $rules[sprintf(
                    '%s.%s.value',
                    $valueType,
                    $targetIndex
                )]
                    = [
                    new RequiredEitherNumericTargetValueOrActualValue(
                        $valueType,
                        $fieldToValidateAgainst,
                        $formFields
                    ),
                ];
            } elseif ($indicatorMeasureType['qualitative'] && !empty($target['value'])) {
                $rules[sprintf('%s.%s.value', $valueType, $targetIndex)] = 'qualitative_empty';
            }
        }

        return $rules;
    }

    /**
     * returns rules for Target.
     *
     * @param $formFields
     * @param $valueType
     * @param $fileUpload
     * @param $indicator
     *
     * @return array
     * @throws BindingResolutionException
     */
    protected function getErrorsForTarget($formFields, $valueType, $fileUpload, $indicator): array
    {
        $rules = [];

        foreach ($formFields as $targetIndex => $target) {
            $targetForm = sprintf('%s.%s', $valueType, $targetIndex);
            $narrativeRules = $this->getErrorsForNarrative(
                $target['comment'][0]['narrative'],
                sprintf('%s.comment.0', $targetForm)
            );
            $docLinkRules = $this->getErrorsForDocumentLink(Arr::get($target, 'document_link', []), $targetForm);

            foreach ($narrativeRules as $key => $narrativeRule) {
                $rules[$key] = $narrativeRule;
            }

            foreach ($docLinkRules as $key => $docLinkRule) {
                $rules[$key] = $docLinkRule;
            }
        }

        return $rules;
    }

    /**
     * returns messages for target.
     *
     * @param $formFields
     * @param $valueType
     * @param $fileUpload
     * @param $indicator
     * @param $indicatorId
     *
     * @return array
     * @throws BindingResolutionException
     */
    protected function getMessagesForTargetOrActual(
        $formFields,
        $valueType,
        $fileUpload,
        $indicator,
        $indicatorId
    ): array {
        /** @var IndicatorService $indicatorService */
        $messages = [];
        $indicatorService = app()->make(IndicatorService::class);

        if ($fileUpload) {
            $measure = $indicatorId ? $indicatorService->getIndicatorMeasureType($indicatorId) : Arr::get(
                $indicator,
                'measure'
            );
            $indicatorMeasureType = is_array($measure) ? $measure : [
                'qualitative'     => $measure === '5',
                'non_qualitative' => in_array($measure, ['1', '2', '3', '4']),
            ];
        } else {
            $params = $this->route()->parameters();
            $indicatorMeasureType = $indicatorService->getIndicatorMeasureType($params['id']);
        }

        foreach ($formFields as $targetIndex => $target) {
            $targetForm = sprintf('%s.%s', $valueType, $targetIndex);

            $messages[sprintf('%s.%s.value.numeric', $valueType, $targetIndex)] = trans(
                'validation.activity_periods.value.numeric'
            );

            $narrativeMessages = $this->getMessagesForNarrative(
                $target['comment'][0]['narrative'],
                sprintf('%s.comment.0', $targetForm)
            );

            foreach ($narrativeMessages as $key => $narrativeMessage) {
                $messages[$key] = $narrativeMessage;
            }

            $docLinkMessages = $this->getMessagesForDocumentLink(Arr::get($target, 'document_link', []), $targetForm);

            foreach ($docLinkMessages as $key => $docLinkMessage) {
                $messages[$key] = $docLinkMessage;
            }

            if ($indicatorMeasureType['qualitative'] && !empty($target['value'])) {
                $messages[sprintf(
                    '%s.%s.value.qualitative_empty',
                    $valueType,
                    $targetIndex
                )]
                    = trans('validation.activity_periods.value.qualitative_empty');
            }
        }

        return $messages;
    }
}
