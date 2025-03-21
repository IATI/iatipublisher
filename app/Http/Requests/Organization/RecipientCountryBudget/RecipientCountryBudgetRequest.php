<?php

declare(strict_types=1);

namespace App\Http\Requests\Organization\RecipientCountryBudget;

use App\Http\Requests\Organization\OrganizationBaseRequest;

/**
 * Class RecipientCountryBudgetRequest.
 */
class RecipientCountryBudgetRequest extends OrganizationBaseRequest
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
                'recipient_country_budget'
            ) as $recipientCountryBudgetIndex => $recipientCountryBudget
        ) {
            $diff = 0;
            $start = $recipientCountryBudget['period_start'][0]['date'];
            $end = $recipientCountryBudget['period_end'][0]['date'];

            if ($start && $end) {
                $diff = (dateStrToTime($end) - dateStrToTime($start)) / 86400;
            }

            $recipientCountryBudgetForm = sprintf('recipient_country_budget.%s', $recipientCountryBudgetIndex);
            $rules[$recipientCountryBudgetForm . '.status'] = [
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
            $budgetWarning = $this->getRecipientCountryBudgetWarning(
                $recipientCountryBudget['recipient_country'],
                $recipientCountryBudgetForm
            );

            foreach ($budgetWarning as $key => $budgetRule) {
                $rules[$key] = $budgetRule;
            }

            $periodStartRules = $this->getWarningForPeriodStart(
                $recipientCountryBudget['period_start'],
                $recipientCountryBudgetForm,
                $diff,
                365
            );

            foreach ($periodStartRules as $key => $periodStartRule) {
                $rules[$key] = $periodStartRule;
            }

            $periodEndRules = $this->getWarningForPeriodEnd(
                $recipientCountryBudget['period_end'],
                $recipientCountryBudgetForm,
                $diff,
                365
            );

            foreach ($periodEndRules as $key => $periodEndRule) {
                $rules[$key] = $periodEndRule;
            }

            $valueRules = $this->getWarningForValue($recipientCountryBudget['value'], $recipientCountryBudgetForm);

            foreach ($valueRules as $key => $valueRule) {
                $rules[$key] = $valueRule;
            }

            $budgetLineRules = $this->getWarningForBudgetLine(
                $recipientCountryBudget['budget_line'],
                $recipientCountryBudgetForm
            );

            foreach ($budgetLineRules as $key => $budgetLineRule) {
                $rules[$key] = $budgetLineRule;
            }
        }

        return $rules;
    }

    /**
     * returns rules for value form.
     *
     * @param $formFields
     * @param $formBase
     *
     * @return array
     */
    public function getWarningForValue($formFields, $formBase): array
    {
        $rules = [];
        $periodStartFormBase = sprintf('%s.period_start.0.date', $formBase);
        $periodEndFormBase = sprintf('%s.period_end.0.date', $formBase);
        $valueDateRule = sprintf(
            'nullable|date|after_or_equal:%s|before_or_equal:%s',
            $periodStartFormBase,
            $periodEndFormBase
        );

        foreach ($formFields as $valueKey => $valueVal) {
            $valueForm = $formBase . '.value.' . $valueKey;
            $rules[$valueForm . '.amount'] = 'nullable|numeric|min:0';
            $rules[$valueForm . '.currency'] = sprintf(
                'nullable|in:%s',
                implode(
                    ',',
                    array_keys(
                        $this->getCodeListForRequestFiles('Currency', 'Activity')
                    )
                )
            );
            $rules[$valueForm . '.value_date'] = $valueDateRule;
        }

        return $rules;
    }

    /** returns rules for budget line value or expense line value.
     *
     * @param $formField
     * @param $formBase
     * @param $parentFormBase
     *
     * @return array
     */
    public function getWarningForBudgetOrExpenseLineValue($formField, $formBase, $parentFormBase): array
    {
        $rules = [];
        $periodStartFormBase = sprintf('%s.period_start.0.date', $parentFormBase);
        $periodEndFormBase = sprintf('%s.period_end.0.date', $parentFormBase);
        $valueDateRule = sprintf(
            'nullable|date|after_or_equal:%s|before_or_equal:%s',
            $periodStartFormBase,
            $periodEndFormBase
        );

        foreach ($formField as $budgetLineIndex => $budgetLine) {
            $rules[$formBase . '.value.' . $budgetLineIndex . '.amount'] = 'nullable|numeric|min:0';
            $rules[$formBase . '.value.' . $budgetLineIndex . '.currency'] = sprintf(
                'nullable|in:%s',
                implode(
                    ',',
                    array_keys(
                        $this->getCodeListForRequestFiles('Currency', 'Activity')
                    )
                )
            );
            $rules[$formBase . '.value.' . $budgetLineIndex . '.value_date'] = $valueDateRule;
        }

        return $rules;
    }

    /**
     * returns messages for value form.
     *
     * @param $formFields
     * @param $formBase
     *
     * @return array
     */
    public function getMessagesForValue($formFields, $formBase): array
    {
        $messages = [];

        foreach ($formFields as $valueKey => $valueVal) {
            $valueForm = $formBase . '.value.' . $valueKey;
            $messages[$valueForm . '.amount.required'] = trans(
                'validation.amount_required'
            );
            $messages[$valueForm . '.amount.numeric'] = trans(
                'validation.amount_number'
            );
            $messages[$valueForm . '.amount.min'] = trans(
                'validation.amount_negative'
            );
            $messages[$valueForm . '.value_date.required'] = trans(
                'validation.value_date_required'
            );
            $messages[$valueForm . '.value_date.date'] = trans(
                'validation.date_is_invalid'
            );
            $messages[sprintf(
                '%s.value_date.after_or_equal',
                $valueForm
            )]
                = trans('validation.value_date_after_or_equal');
            $messages[sprintf(
                '%s.value_date.before_or_equal',
                $valueForm
            )]
                = trans('validation.value_date_after_or_equal');
            $messages[sprintf('%s.currency.in', $valueForm)] = trans('validation.invalid_currency');
        }

        return $messages;
    }

    /**
     * return validation messages to the rules.
     *
     * @return array
     */
    public function messages(): array
    {
        $messages = [];

        foreach ($this->get('recipient_country_budget') as $recipientCountryBudgetIndex => $recipientCountryBudget) {
            $recipientCountryBudgetForm = sprintf('recipient_country_budget.%s', $recipientCountryBudgetIndex);

            $budgetMessages = $this->getRecipientCountryBudgetMessages(
                $recipientCountryBudget['recipient_country'],
                $recipientCountryBudgetForm
            );

            foreach ($budgetMessages as $key => $budgetMessage) {
                $messages[$key] = $budgetMessage;
            }

            $periodStartMessages = $this->getMessagesForPeriodStart(
                $recipientCountryBudget['period_start'],
                $recipientCountryBudgetForm
            );

            foreach ($periodStartMessages as $key => $periodStartMessage) {
                $messages[$key] = $periodStartMessage;
            }

            $periodEndMessages = $this->getMessagesForPeriodEnd(
                $recipientCountryBudget['period_end'],
                $recipientCountryBudgetForm
            );

            foreach ($periodEndMessages as $key => $periodEndMessage) {
                $messages[$key] = $periodEndMessage;
            }

            $valueMessages = $this->getMessagesForValue($recipientCountryBudget['value'], $recipientCountryBudgetForm);

            foreach ($valueMessages as $key => $valueMessage) {
                $messages[$key] = $valueMessage;
            }

            $budgetLineMessages = $this->getMessagesBudgetLine(
                $recipientCountryBudget['budget_line'],
                $recipientCountryBudgetForm
            );

            foreach ($budgetLineMessages as $key => $budgetLineMessage) {
                $messages[$key] = $budgetLineMessage;
            }
        }

        return $messages;
    }

    /**
     * Rules for recipient country budget.
     *
     * @param  array  $formFields
     * @param       $formBase
     *
     * @return array
     */
    public function getRecipientCountryBudgetWarning(array $formFields, $formBase): array
    {
        $rules = [];

        foreach ($formFields as $recipientCountryIndex => $recipientCountry) {
            $recipientCountryForm = sprintf('%s.recipient_country.%s', $formBase, $recipientCountryIndex);
            $rules[sprintf('%s.code', $recipientCountryForm)] = sprintf(
                'nullable|in:%s',
                implode(
                    ',',
                    array_keys(
                        $this->getCodeListForRequestFiles('Country', 'Activity')
                    )
                )
            );
            $narrativeRules = $this->getWarningForNarrative($recipientCountry['narrative'], $recipientCountryForm);

            foreach ($narrativeRules as $key => $narrativeRule) {
                $rules[$key] = $narrativeRule;
            }
        }

        return $rules;
    }

    /**
     * Custom messages for recipient country budget form.
     *
     * @param  array  $formFields
     * @param       $formBase
     *
     * @return array
     */
    public function getRecipientCountryBudgetMessages(array $formFields, $formBase): array
    {
        $messages = [];

        foreach ($formFields as $recipientCountryIndex => $recipientCountry) {
            $recipientCountryForm = sprintf('%s.recipient_country.%s', $formBase, $recipientCountryIndex);
            $messages[sprintf('%s.code.required', $recipientCountryForm)] = trans(
                'validation.required',
                ['attribute' => trans(' elements/label.code')]
            );
            $narrativeMessages = $this->getMessagesForNarrative($recipientCountry['narrative'], $recipientCountryForm);

            foreach ($narrativeMessages as $key => $narrativeMessage) {
                $messages[$key] = $narrativeMessage;
            }
        }

        return $messages;
    }
}
