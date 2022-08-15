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
    public function rules()
    {
        return $this->getRulesForTotalBudget($this->get('total_budget'));
    }

    public function messages()
    {
        return $this->getMessagesForTotalBudget($this->get('total_budget'));
    }

    /**
     * returns rules for total budget form.
     * @param $formFields
     * @return array
     */
    public function getRulesForTotalBudget($formFields)
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
            $rules = array_merge(
                $rules,
                $this->getRulesForPeriodStart($totalBudget['period_start'], $totalBudgetForm, $diff, 365),
                $this->getRulesForPeriodEnd($totalBudget['period_end'], $totalBudgetForm, $diff, 365),
                $this->getRulesForValue($totalBudget['value'], $totalBudgetForm),
                $this->getRulesForBudgetLine($totalBudget['budget_line'], $totalBudgetForm)
            );
        }

        return $rules;
    }

    /**
     * returns messages for total budget form rules.
     * @param $formFields
     * @return array
     */
    public function getMessagesForTotalBudget($formFields)
    {
        $messages = [];
        foreach ($formFields as $totalBudgetIndex => $totalBudget) {
            $totalBudgetForm = sprintf('total_budget.%s', $totalBudgetIndex);
            $messages = array_merge(
                $messages,
                $this->getMessagesForPeriodStart($totalBudget['period_start'], $totalBudgetForm),
                $this->getMessagesForPeriodEnd($totalBudget['period_end'], $totalBudgetForm),
                $this->getMessagesForValue($totalBudget['value'], $totalBudgetForm),
                $this->getMessagesBudgetLine($totalBudget['budget_line'], $totalBudgetForm)
            );
        }

        return $messages;
    }
}
