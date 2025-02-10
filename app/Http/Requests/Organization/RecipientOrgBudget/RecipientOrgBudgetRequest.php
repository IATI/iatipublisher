<?php

declare(strict_types=1);

namespace App\Http\Requests\Organization\RecipientOrgBudget;

use App\Http\Requests\Organization\OrganizationBaseRequest;

/**
 * Class RecipientOrgBudgetRequest.
 */
class RecipientOrgBudgetRequest extends OrganizationBaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $rules = [];

        foreach (
            $this->get(
                'recipient_org_budget'
            ) as $recipientOrganizationBudgetIndex => $recipientOrganizationBudget
        ) {
            $diff = 0;
            $start = $recipientOrganizationBudget['period_start'][0]['date'];
            $end = $recipientOrganizationBudget['period_end'][0]['date'];

            if ($start && $end) {
                $diff = (dateStrToTime($end) - dateStrToTime($start)) / 86400;
            }
            $recipientOrganizationBudgetForm = sprintf(
                'recipient_org_budget.%s',
                $recipientOrganizationBudgetIndex
            );
            $rules[$recipientOrganizationBudgetForm . '.status'] = [
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
            $periodStartRules = $this->getWarningForPeriodStart(
                $recipientOrganizationBudget['period_start'],
                $recipientOrganizationBudgetForm,
                $diff,
                365
            );

            foreach ($periodStartRules as $key => $item) {
                $rules[$key] = $item;
            }

            $periodEndRules = $this->getWarningForPeriodEnd(
                $recipientOrganizationBudget['period_end'],
                $recipientOrganizationBudgetForm,
                $diff,
                365
            );

            foreach ($periodEndRules as $key => $item) {
                $rules[$key] = $item;
            }

            $valueRules = $this->getWarningForValue(
                $recipientOrganizationBudget['value'],
                $recipientOrganizationBudgetForm
            );

            foreach ($valueRules as $key => $item) {
                $rules[$key] = $item;
            }

            $budgetLineRules = $this->getWarningForBudgetLine(
                $recipientOrganizationBudget['budget_line'],
                $recipientOrganizationBudgetForm
            );

            foreach ($budgetLineRules as $key => $item) {
                $rules[$key] = $item;
            }

            $narrativeRules = $this->getWarningForNarrative(
                $recipientOrganizationBudget['recipient_org'][0]['narrative'],
                $recipientOrganizationBudgetForm . '.recipient_org.0'
            );

            foreach ($narrativeRules as $key => $item) {
                $rules[$key] = $item;
            }
        }

        return $rules;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function messages(): array
    {
        $messages = [];

        foreach (
            $this->get(
                'recipient_org_budget'
            ) as $recipientOrganizationBudgetIndex => $recipientOrganizationBudget
        ) {
            $recipientOrganizationBudgetForm
                = sprintf('recipient_org_budget.%s', $recipientOrganizationBudgetIndex);
            $narrativeField = sprintf(
                '%s.recipient_org.0.narrative.0.narrative.required_without',
                $recipientOrganizationBudgetForm
            );
            $messages[$narrativeField] = trans(
                'validation.required_without',
                ['attribute' => trans('elements/label.narrative'), 'values' => trans('elements/label.ref')]
            );

            $periodStartMessages = $this->getMessagesForPeriodStart(
                $recipientOrganizationBudget['period_start'],
                $recipientOrganizationBudgetForm
            );

            foreach ($periodStartMessages as $key => $item) {
                $messages[$key] = $item;
            }

            $periodEndMessages = $this->getMessagesForPeriodEnd(
                $recipientOrganizationBudget['period_end'],
                $recipientOrganizationBudgetForm
            );

            foreach ($periodEndMessages as $key => $item) {
                $messages[$key] = $item;
            }

            $valueMessages = $this->getMessagesForValue(
                $recipientOrganizationBudget['value'],
                $recipientOrganizationBudgetForm
            );

            foreach ($valueMessages as $key => $item) {
                $messages[$key] = $item;
            }

            $budgetLineMessages = $this->getMessagesBudgetLine(
                $recipientOrganizationBudget['budget_line'],
                $recipientOrganizationBudgetForm
            );

            foreach ($budgetLineMessages as $key => $item) {
                $messages[$key] = $item;
            }

            $narrativeMessages = $this->getMessagesForNarrative(
                $recipientOrganizationBudget['recipient_org'][0]['narrative'],
                $recipientOrganizationBudgetForm . '.recipient_org.0'
            );

            foreach ($narrativeMessages as $key => $item) {
                $messages[$key] = $item;
            }
        }

        return $messages;
    }
}
