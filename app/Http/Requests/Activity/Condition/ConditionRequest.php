<?php

declare(strict_types=1);

namespace App\Http\Requests\Activity\Condition;

use App\Http\Requests\Activity\ActivityBaseRequest;

/**
 * Class ConditionRequest.
 */
class ConditionRequest extends ActivityBaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $totalRules = [
            $this->getWarningForCondition($this->get('condition')),
            $this->getErrorsForCondition($this->get('condition')),
        ];

        return mergeRules($totalRules);
    }

    /**
     * Returns rules for related activity.
     *
     * @param  array  $formFields
     *
     * @return array
     */
    public function getWarningForCondition(array $formFields): array
    {
        $rules = [];

        foreach ($formFields as $conditionIndex => $condition) {
            $conditionForm = sprintf('condition.%s', $conditionIndex);

            foreach (
                $this->getWarningForNarrative(
                    $condition['narrative'],
                    $conditionForm
                ) as $conditionNarrativeIndex => $conditionNarrativeRules
            ) {
                $rules[$conditionNarrativeIndex] = $conditionNarrativeRules;
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
    public function getErrorsForCondition(array $formFields): array
    {
        $rules = [];

        foreach ($formFields as $conditionIndex => $condition) {
            $conditionForm = sprintf('condition.%s', $conditionIndex);
            $rules[sprintf('%s.condition_type', $conditionForm)] = 'nullable|in:' . implode(
                ',',
                array_keys(
                    $this->getCodeListForRequestFiles('ConditionType', 'Activity', false)
                )
            );

            foreach (
                $this->getErrorsForNarrative(
                    $condition['narrative'],
                    $conditionForm
                ) as $conditionNarrativeIndex => $conditionNarrativeRules
            ) {
                $rules[$conditionNarrativeIndex] = $conditionNarrativeRules;
            }
        }

        return $rules;
    }

    /**
     * Get the error message as required.
     *
     * @return array
     */
    public function messages(): array
    {
        return $this->getMessagesForCondition($this->get('condition'));
    }

    /**
     * Returns messages for related activity validations.
     *
     * @param  array  $formFields
     *
     * @return array
     */
    public function getMessagesForCondition(array $formFields): array
    {
        $messages = [];

        foreach ($formFields as $conditionIndex => $condition) {
            $conditionForm = sprintf('condition.%s', $conditionIndex);
            $messages[sprintf('%s.condition_type.in', $conditionForm)] = trans(
                'validation.activity_conditions.invalid_type'
            );

            foreach (
                $this->getMessagesForNarrative(
                    $condition['narrative'],
                    $conditionForm
                ) as $conditionNarrativeIndex => $conditionNarrativeMessages
            ) {
                $messages[$conditionNarrativeIndex] = $conditionNarrativeMessages;
            }
        }

        return $messages;
    }
}
