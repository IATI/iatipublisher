<?php

declare(strict_types=1);

namespace App\Http\Requests\Activity\Description;

use App\Http\Requests\Activity\ActivityBaseRequest;
use Illuminate\Support\Arr;

/**
 * Class DescriptionRequest.
 */
class DescriptionRequest extends ActivityBaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     *
     * @throws \JsonException
     */
    public function rules(): array
    {
        $data = $this->get('description');
        $totalRules = [$this->getErrorsForDescription($data), $this->getWarningForDescription($data)];

        return mergeRules($totalRules);
    }

    /**
     * Get the error message as required.
     *
     * @return array
     */
    public function messages(): array
    {
        return $this->getMessagesForDescription($this->get('description'));
    }

    /**
     * Returns rules for related activity.
     *
     * @param  array  $formFields
     *
     * @return array
     *
     * @throws \JsonException
     */
    public function getErrorsForDescription(array $formFields): array
    {
        $rules = [];

        foreach ($formFields as $descriptionIndex => $description) {
            $descriptionForm = sprintf('description.%s', $descriptionIndex);
            $rules[sprintf('%s.type', $descriptionForm)] = sprintf(
                'nullable|in:%s',
                implode(
                    ',',
                    array_keys(
                        $this->getCodeListForRequestFiles('DescriptionType', 'Activity', false)
                    )
                )
            );
            $narrativeRules = $this->getErrorsForNarrative(
                Arr::get(
                    $description,
                    'narrative',
                    []
                ),
                $descriptionForm
            );

            foreach ($narrativeRules as $key => $item) {
                $rules[$key] = $item;
            }
        }

        return $rules;
    }

    /**
     * Returns rules for related activity.
     *
     * @param  array  $formFields
     *
     * @return array
     */
    public function getWarningForDescription(array $formFields): array
    {
        $rules = [];

        foreach ($formFields as $descriptionIndex => $description) {
            $descriptionForm = sprintf('description.%s', $descriptionIndex);
            $narrativeRules = $this->getWarningForNarrative(
                Arr::get(
                    $description,
                    'narrative',
                    []
                ),
                $descriptionForm
            );

            foreach ($narrativeRules as $key => $item) {
                $rules[$key] = $item;
            }
        }

        return $rules;
    }

    /**
     * Returns messages for related activity validations.
     *
     * @param  array  $formFields
     *
     * @return array
     */
    public function getMessagesForDescription(array $formFields): array
    {
        $messages = [];

        foreach ($formFields as $descriptionIndex => $description) {
            $descriptionForm = sprintf('description.%s', $descriptionIndex);
            $messages[$descriptionForm . '.type'] = trans('validation.description_type_invalid');

            $narrativeMessages = $this->getMessagesForNarrative(
                Arr::get($description, 'narrative', []),
                $descriptionForm
            );

            foreach ($narrativeMessages as $key => $item) {
                $messages[$key] = $item;
            }
        }

        return $messages;
    }
}
