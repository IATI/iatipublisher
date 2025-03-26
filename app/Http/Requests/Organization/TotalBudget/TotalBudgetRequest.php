<?php

declare(strict_types=1);

namespace App\Http\Requests\Organization\TotalBudget;

use App\Http\Requests\Organization\OrganizationBaseRequest;

/**
 * Class TotalBudgetRequest.
 */
class TotalBudgetRequest extends OrganizationBaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return $this->getWarningForTotalBudget($this->get('total_budget'));
    }

    /**
     * Get the validation message that apply to the request.
     *
     * @return array
     */
    public function messages(): array
    {
        return $this->getMessagesForTotalBudget($this->get('total_budget'));
    }

    /**
     * returns rules for total budget form.
     *
     * @param $formFields
     *
     * @return array
     */
    public function getWarningForTotalBudget($formFields): array
    {
        $rules = [];

        foreach ($formFields as $totalBudgetIndex => $totalBudget) {
            $diff = 0;
            $start = $totalBudget['period_start'][0]['date'];
            $end = $totalBudget['period_end'][0]['date'];

            if ($start && $end) {
                $diff = (strtotime($end) - strtotime($start)) / 86400;
            }

            $totalBudgetForm = sprintf('total_budget.%s', $totalBudgetIndex);
            $rules[$totalBudgetForm . '.total_budget_status'] = [
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
                $totalBudget['period_start'],
                $totalBudgetForm,
                $diff,
                365
            );

            foreach ($periodStartRules as $key => $periodStartRule) {
                $rules[$key] = $periodStartRule;
            }

            $periodEndRules = $this->getWarningForPeriodEnd(
                $totalBudget['period_end'],
                $totalBudgetForm,
                $diff,
                365
            );

            foreach ($periodEndRules as $key => $periodEndRule) {
                $rules[$key] = $periodEndRule;
            }

            $valueRules = $this->getWarningForValue($totalBudget['value'], $totalBudgetForm);

            foreach ($valueRules as $key => $valueRule) {
                $rules[$key] = $valueRule;
            }

            $budgetLineRules = $this->getWarningForBudgetLine($totalBudget['budget_line'], $totalBudgetForm);

            foreach ($budgetLineRules as $key => $budgetLineRule) {
                $rules[$key] = $budgetLineRule;
            }
        }

        return $rules;
    }

    /**
     * returns messages for total budget form rules.
     *
     * @param $formFields
     *
     * @return array
     */
    public function getMessagesForTotalBudget($formFields): array
    {
        $messages = [];

        foreach ($formFields as $totalBudgetIndex => $totalBudget) {
            $totalBudgetForm = sprintf('total_budget.%s', $totalBudgetIndex);
            $periodStartMessages = $this->getMessagesForPeriodStart($totalBudget['period_start'], $totalBudgetForm);

            foreach ($periodStartMessages as $key => $periodStartMessage) {
                $messages[$key] = $periodStartMessage;
            }

            $periodEndMessages = $this->getMessagesForPeriodEnd($totalBudget['period_end'], $totalBudgetForm);

            foreach ($periodEndMessages as $key => $periodEndMessage) {
                $messages[$key] = $periodEndMessage;
            }

            $valueMessages = $this->getMessagesForValue($totalBudget['value'], $totalBudgetForm);

            foreach ($valueMessages as $key => $valueMessage) {
                $messages[$key] = $valueMessage;
            }

            $budgetLineMessages = $this->getMessagesBudgetLine($totalBudget['budget_line'], $totalBudgetForm);

            foreach ($budgetLineMessages as $key => $budgetLineMessage) {
                $messages[$key] = $budgetLineMessage;
            }
        }

        return $messages;
    }
}
