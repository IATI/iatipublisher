<?php

declare(strict_types=1);

namespace App\Http\Requests\Organization\TotalExpenditure;

use App\Http\Requests\Organization\OrganizationBaseRequest;

/**
 * Class TotalExpenditureRequest.
 */
class TotalExpenditureRequest extends OrganizationBaseRequest
{
    /**
     * rules for total expenditure.
     *
     * @return array
     */
    public function rules(): array
    {
        return $this->getWarningForTotalExpenditure($this->get('total_expenditure'));
    }

    /**
     * prepare messages for total expenditure.
     *
     * @return array
     */
    public function messages(): array
    {
        return $this->getMessagesForTotalExpenditure($this->get('total_expenditure'));
    }

    /**
     * rules for organization total expenditure.
     *
     * @param $formFields
     *
     * @return array
     */
    public function getWarningForTotalExpenditure($formFields): array
    {
        $rules = [];

        foreach ($formFields as $totalExpenditureIndex => $totalExpenditure) {
            $diff = 0;
            $start = $totalExpenditure['period_start'][0]['date'];
            $end = $totalExpenditure['period_end'][0]['date'];

            if ($start && $end) {
                $diff = (strtotime($end) - strtotime($start)) / 86400;
            }

            $totalExpenditureForm = sprintf('total_expenditure.%s', $totalExpenditureIndex);

            $periodStartRules = $this->getWarningForPeriodStart(
                $totalExpenditure['period_start'],
                $totalExpenditureForm,
                $diff,
                365
            );

            foreach ($periodStartRules as $key => $periodStartRule) {
                $rules[$key] = $periodStartRule;
            }

            $periodEndRules = $this->getWarningForPeriodEnd(
                $totalExpenditure['period_end'],
                $totalExpenditureForm,
                $diff,
                365
            );

            foreach ($periodEndRules as $key => $periodEndRule) {
                $rules[$key] = $periodEndRule;
            }

            $valueRules = $this->getWarningForValue($totalExpenditure['value'], $totalExpenditureForm);

            foreach ($valueRules as $key => $valueRule) {
                $rules[$key] = $valueRule;
            }

            $expenseLineRules = $this->getWarningForExpenseLine(
                $totalExpenditure['expense_line'],
                $totalExpenditureForm
            );

            foreach ($expenseLineRules as $key => $expenseLineRule) {
                $rules[$key] = $expenseLineRule;
            }
        }

        return $rules;
    }

    /**
     * messages for organization total expenditure.
     *
     * @param $formFields
     *
     * @return array
     */
    public function getMessagesForTotalExpenditure($formFields): array
    {
        $messages = [];

        foreach ($formFields as $totalExpenditureIndex => $totalExpenditure) {
            $totalExpenditureForm = sprintf('total_expenditure.%s', $totalExpenditureIndex);

            $periodStartMessages = $this->getMessagesForPeriodStart(
                $totalExpenditure['period_start'],
                $totalExpenditureForm
            );

            foreach ($periodStartMessages as $key => $periodStartMessage) {
                $messages[$key] = $periodStartMessage;
            }

            $periodEndMessages = $this->getMessagesForPeriodEnd(
                $totalExpenditure['period_end'],
                $totalExpenditureForm
            );

            foreach ($periodEndMessages as $key => $periodEndMessage) {
                $messages[$key] = $periodEndMessage;
            }

            $valueMessages = $this->getMessagesForValue($totalExpenditure['value'], $totalExpenditureForm);

            foreach ($valueMessages as $key => $valueMessage) {
                $messages[$key] = $valueMessage;
            }

            $expenseLineMessages = $this->getMessagesForExpenseLine(
                $totalExpenditure['expense_line'],
                $totalExpenditureForm
            );

            foreach ($expenseLineMessages as $key => $expenseLineMessage) {
                $messages[$key] = $expenseLineMessage;
            }
        }

        return $messages;
    }

    /**
     * rules for expense line.
     *
     * @param $formFields
     * @param $formBase
     *
     * @return array
     */
    public function getWarningForExpenseLine($formFields, $formBase): array
    {
        $rules = [];

        foreach ($formFields as $expenseLineIndex => $expenseLine) {
            $expenseLineForm = sprintf('%s.expense_line.%s', $formBase, $expenseLineIndex);

            $valueRules = $this->getWarningForBudgetOrExpenseLineValue(
                $expenseLine['value'],
                $expenseLineForm,
                $formBase
            );

            foreach ($valueRules as $key => $valueRule) {
                $rules[$key] = $valueRule;
            }

            $narrativeRules = $this->getWarningForNarrative($expenseLine['narrative'], $expenseLineForm);

            foreach ($narrativeRules as $key => $narrativeRule) {
                $rules[$key] = $narrativeRule;
            }
        }

        return $rules;
    }

    /**
     * messages for expense line.
     *
     * @param $formFields
     * @param $formBase
     *
     * @return array
     */
    public function getMessagesForExpenseLine($formFields, $formBase): array
    {
        $messages = [];

        foreach ($formFields as $expenseLineIndex => $expenseLine) {
            $expenseLineForm = sprintf('%s.expense_line.%s', $formBase, $expenseLineIndex);
            $messages[sprintf('%s.expense_line.%s.reference.required', $formBase, $expenseLineIndex)] = trans(
                'validation.required',
                ['attribute' => trans('elements/label.reference')]
            );

            $valueMessages = $this->getMessagesForBudgetOrExpenseLineValue($expenseLine['value'], $expenseLineForm);

            foreach ($valueMessages as $key => $valueMessage) {
                $messages[$key] = $valueMessage;
            }

            $narrativeMessages = $this->getMessagesForBudgetOrExpenseLineNarrative(
                $expenseLine['narrative'],
                $expenseLineForm
            );

            foreach ($narrativeMessages as $key => $narrativeMessage) {
                $messages[$key] = $narrativeMessage;
            }
        }

        return $messages;
    }
}
