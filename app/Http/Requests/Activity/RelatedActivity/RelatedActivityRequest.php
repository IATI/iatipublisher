<?php

declare(strict_types=1);

namespace App\Http\Requests\Activity\RelatedActivity;

use App\Http\Requests\Activity\ActivityBaseRequest;

/**
 * Class RelatedActivityRequest.
 */
class RelatedActivityRequest extends ActivityBaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $data = $this->get('related_activity');

        $totalRules = [
            $this->getWarningForRelatedActivity($data),
            $this->getErrorsForRelatedActivity($data),
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
        return $this->getMessagesForRelatedActivity($this->get('related_activity'));
    }

    /**
     * Returns rules for related activity.
     *
     * @param  array  $formFields
     *
     * @return array
     */
    public function getErrorsForRelatedActivity(array $formFields): array
    {
        $rules = [];
        $relatedActivityType = implode(
            ',',
            array_keys(
                $this->getCodeListForRequestFiles('RelatedActivityType', 'Activity', false)
            )
        );

        foreach ($formFields as $index => $formField) {
            $baseForm = sprintf('related_activity.%s', $index);
            $rules[sprintf('%s.relationship_type', $baseForm)] = sprintf(
                'nullable|in:%s',
                $relatedActivityType
            );
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
    public function getWarningForRelatedActivity(array $formFields): array
    {
        return [];
    }

    /**
     * Returns messages for related activity validations.
     *
     * @param  array  $formFields
     *
     * @return array
     */
    public function getMessagesForRelatedActivity(array $formFields): array
    {
        $messages = [];

        foreach ($formFields as $index => $formField) {
            $baseForm = sprintf('related_activity.%s', $index);
            $messages[sprintf(
                '%s.relationship_type.in',
                $baseForm
            )]
                = trans('validation.this_field_is_invalid');
        }

        return $messages;
    }
}
