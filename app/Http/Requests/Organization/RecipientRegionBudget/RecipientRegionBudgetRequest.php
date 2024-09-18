<?php

declare(strict_types=1);

namespace App\Http\Requests\Organization\RecipientRegionBudget;

use App\Http\Requests\Organization\OrganizationBaseRequest;

/**
 * Class RecipientRegionBudgetRequest.
 */
class RecipientRegionBudgetRequest extends OrganizationBaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $rules = [];

        foreach ($this->get('recipient_region_budget') as $recipientRegionBudgetIndex => $recipientRegionBudget) {
            $diff = 0;
            $start = $recipientRegionBudget['period_start'][0]['date'];
            $end = $recipientRegionBudget['period_end'][0]['date'];

            if ($start && $end) {
                $diff = (dateStrToTime($end) - dateStrToTime($start)) / 86400;
            }

            $recipientRegionBudgetForm = sprintf('recipient_region_budget.%s', $recipientRegionBudgetIndex);
            $rules[$recipientRegionBudgetForm . '.status'] = [
                'nullable',
                sprintf(
                    'in:%s',
                    implode(
                        ',',
                        array_keys(
                            $this->getCodeListForRequestFiles('BudgetStatus', 'Activity')
                        )
                    )
                ),
            ];

            $budgetWarning = $this->getRecipientRegionBudgetWarning(
                $recipientRegionBudget['recipient_region'],
                $recipientRegionBudgetForm
            );

            foreach ($budgetWarning as $key => $item) {
                $rules[$key] = $item;
            }

            $periodStartRules = $this->getWarningForPeriodStart(
                $recipientRegionBudget['period_start'],
                $recipientRegionBudgetForm,
                $diff,
                365
            );

            foreach ($periodStartRules as $key => $item) {
                $rules[$key] = $item;
            }

            $periodEndRules = $this->getWarningForPeriodEnd(
                $recipientRegionBudget['period_end'],
                $recipientRegionBudgetForm,
                $diff,
                365
            );

            foreach ($periodEndRules as $key => $item) {
                $rules[$key] = $item;
            }

            $valueRules = $this->getWarningForValue($recipientRegionBudget['value'], $recipientRegionBudgetForm);

            foreach ($valueRules as $key => $item) {
                $rules[$key] = $item;
            }

            $budgetLineRules = $this->getWarningForBudgetLine(
                $recipientRegionBudget['budget_line'],
                $recipientRegionBudgetForm
            );

            foreach ($budgetLineRules as $key => $item) {
                $rules[$key] = $item;
            }
        }

        return $rules;
    }

    /**
     * Get the validation messages for the rules.
     *
     * @return array
     */
    public function messages(): array
    {
        $messages = [];

        foreach ($this->get('recipient_region_budget') as $recipientRegionBudgetIndex => $recipientRegionBudget) {
            $recipientRegionBudgetForm = sprintf('recipient_region_budget.%s', $recipientRegionBudgetIndex);
            $budgetMessages = $this->getRecipientRegionBudgetMessages(
                $recipientRegionBudget['recipient_region'],
                $recipientRegionBudgetForm
            );

            foreach ($budgetMessages as $key => $item) {
                $messages[$key] = $item;
            }

            $periodStartMessages = $this->getMessagesForPeriodStart(
                $recipientRegionBudget['period_start'],
                $recipientRegionBudgetForm
            );

            foreach ($periodStartMessages as $key => $item) {
                $messages[$key] = $item;
            }

            $periodEndMessages = $this->getMessagesForPeriodEnd(
                $recipientRegionBudget['period_end'],
                $recipientRegionBudgetForm
            );

            foreach ($periodEndMessages as $key => $item) {
                $messages[$key] = $item;
            }

            $valueMessages = $this->getMessagesForValue($recipientRegionBudget['value'], $recipientRegionBudgetForm);

            foreach ($valueMessages as $key => $item) {
                $messages[$key] = $item;
            }

            $budgetLineMessages = $this->getMessagesBudgetLine(
                $recipientRegionBudget['budget_line'],
                $recipientRegionBudgetForm
            );

            foreach ($budgetLineMessages as $key => $item) {
                $messages[$key] = $item;
            }
        }

        return $messages;
    }

    /**
     * Rules for recipient region budget form.
     *
     * @param  array  $formFields
     * @param       $formBase
     *
     * @return array
     */
    public function getRecipientRegionBudgetWarning(array $formFields, $formBase): array
    {
        $rules = [];

        foreach ($formFields as $recipientRegionIndex => $recipientRegion) {
            $recipientRegionForm = sprintf('%s.recipient_region.%s', $formBase, $recipientRegionIndex);
            $rules[sprintf('%s.vocabulary_uri', $recipientRegionForm)] = 'nullable|url';
            $rules[sprintf('%s.region_vocabulary', $recipientRegionForm)] = sprintf(
                'nullable|in:%s',
                implode(
                    ',',
                    array_keys(
                        $this->getCodeListForRequestFiles('RegionVocabulary', 'Activity', false)
                    )
                )
            );
            $rules[sprintf('%s.code', $recipientRegionForm)] = 'nullable';

            $narrativeRules = $this->getWarningForNarrative($recipientRegion['narrative'], $recipientRegionForm);

            foreach ($narrativeRules as $key => $item) {
                $rules[$key] = $item;
            }
        }

        return $rules;
    }

    /**
     * Custom rules for recipient region budget.
     *
     * @param  array  $formFields
     * @param       $formBase
     *
     * @return array
     */
    public function getRecipientRegionBudgetMessages(array $formFields, $formBase): array
    {
        $messages = [];

        foreach ($formFields as $recipientRegionIndex => $recipientRegion) {
            $recipientRegionForm = sprintf('%s.recipient_region.%s', $formBase, $recipientRegionIndex);
            $messages[sprintf('%s.recipient_region.%s.vocabulary_uri.url', $formBase, $recipientRegionIndex)]
                = trans(
                    'validation.url'
                );
            $messages[sprintf('%s.recipient_region.%s.code.required', $formBase, $recipientRegionIndex)]
                = trans(
                    'validation.required',
                    ['attribute' => trans('elements/label.code')]
                );
            $narrativeMessages = $this->getMessagesForNarrative($recipientRegion['narrative'], $recipientRegionForm);

            foreach ($narrativeMessages as $key => $item) {
                $messages[$key] = $item;
            }
        }

        return $messages;
    }
}
