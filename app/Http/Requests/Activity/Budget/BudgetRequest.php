<?php

declare(strict_types=1);

namespace App\Http\Requests\Activity\Budget;

use App\Http\Requests\Activity\ActivityBaseRequest;
use App\IATI\Services\Activity\ActivityService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use JsonException;

/**
 * Class BudgetRequest.
 */
class BudgetRequest extends ActivityBaseRequest
{
    /**
     * @var array
     */
    protected array $identicalIds = [];

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     *
     * @throws BindingResolutionException
     * @throws JsonException
     */
    public function rules(): array
    {
        $data = $this->get('budget');
        $totalRules = [$this->getErrorsForBudget($data), $this->getWarningForBudget($data)];

        return mergeRules($totalRules);
    }

    /**
     * Get the error message as required.
     *
     * @return array
     *
     * @throws BindingResolutionException
     */
    public function messages(): array
    {
        return $this->getMessagesForBudget($this->get('budget'));
    }

    /**
     * Returns rules for related activity.
     *
     * @param  array  $formFields
     *
     * @return array
     * @throws BindingResolutionException
     * @throws JsonException
     */
    public function getErrorsForBudget(array $formFields): array
    {
        $rules = [];
        /** @var ActivityService $activityService */
        $activityService = app()->make(ActivityService::class);
        $formFields = $activityService->setBudgets($formFields);
        $this->identicalIds = $activityService->checkSameMultipleBudgets($formFields);

        foreach ($formFields as $budgetIndex => $budget) {
            $budgetForm = sprintf('budget.%s', $budgetIndex);
            $rules[sprintf('%s.budget_type', $budgetForm)] = sprintf(
                'nullable|in:%s',
                implode(
                    ',',
                    array_keys(
                        $this->getCodeListForRequestFiles('BudgetType', 'Activity', false)
                    )
                )
            );
            $rules[sprintf('%s.budget_status', $budgetForm)][] = 'nullable';
            $rules[sprintf('%s.budget_status', $budgetForm)][] = sprintf(
                'in:%s',
                implode(
                    ',',
                    array_keys(
                        $this->getCodeListForRequestFiles('BudgetStatus', 'Activity', false)
                    )
                )
            );

            $periodStartRules = $this->getCriticalBudgetWarningForPeriodStart($budget['period_start'], $budgetForm);

            foreach ($periodStartRules as $key => $periodStartRule) {
                $rules[$key] = $periodStartRule;
            }

            $periodEndRules = $this->getCriticalBudgetWarningForPeriodEnd($budget['period_end'], $budgetForm);

            foreach ($periodEndRules as $key => $periodEndRule) {
                $rules[$key] = $periodEndRule;
            }

            $valueRules = $this->getErrorsForValue($budget['budget_value'], $budgetForm);

            foreach ($valueRules as $key => $valueRule) {
                $rules[$key] = $valueRule;
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
     *
     * @throws BindingResolutionException
     */
    public function getWarningForBudget(array $formFields): array
    {
        $rules = [];
        /** @var ActivityService $activityService */
        $activityService = app()->make(ActivityService::class);
        $formFields = $activityService->setBudgets($formFields);
        $this->identicalIds = $activityService->checkSameMultipleBudgets($formFields);

        if (count($this->identicalIds)) {
            Validator::extend('budgets_identical', function () {
                return false;
            });

            foreach ($this->identicalIds as $ids) {
                foreach ($ids as $id) {
                    $rules['budget.' . $id . '.budget_type'][] = 'budgets_identical';
                }
            }
        }

        foreach ($formFields as $budgetIndex => $budget) {
            $budgetForm = sprintf('budget.%s', $budgetIndex);
            $diff = 0;
            $start = $budget['period_start'][0]['date'];
            $end = $budget['period_end'][0]['date'];

            if ($start && $end) {
                $diff = (dateStrToTime($end) - dateStrToTime($start)) / 86400;
            }

            $periodStartRules = $this->getBudgetWarningForPeriodStart($budget['period_start'], $budgetForm, $diff);

            foreach ($periodStartRules as $key => $periodStartRule) {
                $rules[$key] = $periodStartRule;
            }

            $periodEndRules = $this->getBudgetWarningForPeriodEnd($budget['period_end'], $budgetForm, $diff);

            foreach ($periodEndRules as $key => $periodEndRule) {
                $rules[$key] = $periodEndRule;
            }

            $valueRules = $this->getWarningForValue($budget['budget_value'], $budgetForm);

            foreach ($valueRules as $key => $valueRule) {
                $rules[$key] = $valueRule;
            }

            $startDate = Arr::get($budget, 'period_start.0.date', null);
            $newDate = $startDate && isDate($startDate) ?
                date('Y-m-d', strtotime($startDate . '+1year')) : null;

            if ($newDate) {
                $rules[$budgetForm . '.period_end.0.date'][] = sprintf('before:%s', $newDate);
            }
        }

        return $rules;
    }

    /**
     * Returns rules for value.
     *
     * @param $formFields
     * @param $formBase
     *
     * @return array
     *
     * @throws JsonException
     */
    protected function getErrorsForValue($formFields, $formBase): array
    {
        $rules = [];

        foreach ($formFields as $valueIndex => $value) {
            $valueForm = sprintf('%s.budget_value.%s', $formBase, $valueIndex);
            $rules[sprintf('%s.amount', $valueForm)] = 'nullable|numeric|min:0';
            $rules[sprintf('%s.value_date', $valueForm)] = 'nullable|date';
            $rules[sprintf('%s.currency', $valueForm)] = 'nullable|in:' . implode(
                ',',
                array_keys(
                    $this->getCodeListForRequestFiles('Currency', 'Activity', false)
                )
            );
        }

        return $rules;
    }

    /**
     * returns rules for period start form.
     *
     * @param $formFields
     * @param $formBase
     * @param $diff
     *
     * @return array
     */
    public function getBudgetWarningForPeriodStart($formFields, $formBase, $diff): array
    {
        $rules = [];

        foreach ($formFields as $periodStartKey => $periodStartVal) {
            $rules[$formBase . '.period_start.' . $periodStartKey . '.date']
                = 'nullable|date_greater_than:1900|period_start_end:' . $diff . ',365';
        }

        return $rules;
    }

    /**
     * returns rules for period start form.
     *
     * @param $formFields
     * @param $formBase
     *
     * @return array
     */
    public function getCriticalBudgetWarningForPeriodStart($formFields, $formBase): array
    {
        $rules = [];

        foreach ($formFields as $periodStartKey => $periodStartVal) {
            $rules[$formBase . '.period_start.' . $periodStartKey . '.date'] = 'nullable|date';
        }

        return $rules;
    }

    /**
     * returns rules for period end form.
     *
     * @param $formFields
     * @param $formBase
     * @param $diff
     *
     * @return array
     */
    public function getBudgetWarningForPeriodEnd($formFields, $formBase, $diff): array
    {
        $rules = [];

        foreach ($formFields as $periodEndKey => $periodEndVal) {
            $rules[$formBase . '.period_end.' . $periodEndKey . '.date'][] = 'nullable';
            $rules[$formBase . '.period_end.' . $periodEndKey . '.date'][] = 'date';
            $rules[$formBase . '.period_end.' . $periodEndKey . '.date'][] = sprintf(
                'after:%s',
                $formBase . '.period_start.' . $periodEndKey . '.date'
            );
            $rules[$formBase . '.period_end.' . $periodEndKey . '.date'][] = 'date_greater_than:1900';
            $rules[$formBase . '.period_end.' . $periodEndKey . '.date'][] = 'period_start_end:' . $diff . ',365';
        }

        return $rules;
    }

    /**
     * returns rules for period end form.
     *
     * @param $formFields
     * @param $formBase
     *
     * @return array
     */
    public function getCriticalBudgetWarningForPeriodEnd($formFields, $formBase): array
    {
        $rules = [];

        foreach ($formFields as $periodEndKey => $periodEndVal) {
            $rules[$formBase . '.period_end.' . $periodEndKey . '.date'][] = 'nullable';
            $rules[$formBase . '.period_end.' . $periodEndKey . '.date'][] = 'date';
        }

        return $rules;
    }

    /**
     * Returns messages for related activity validations.
     *
     * @param  array  $formFields
     * @param  bool  $fileUpload
     *
     * @return array
     *
     * @throws BindingResolutionException
     */
    public function getMessagesForBudget(array $formFields, bool $fileUpload = false): array
    {
        $messages = [];
        /** @var ActivityService $activityService */
        $activityService = app()->make(ActivityService::class);
        $formFields = $activityService->setBudgets($formFields);
        $this->identicalIds = $activityService->checkSameMultipleBudgets($formFields);

        if (count($this->identicalIds)) {
            foreach ($this->identicalIds as $ids) {
                foreach ($ids as $id) {
                    $messages['budget.' . $id . '.budget_type.budgets_identical'] = trans(
                        'validation.activity_budget.budget.budgets_identical'
                    );
                }
            }
        }

        foreach ($formFields as $budgetIndex => $budget) {
            $budgetForm = sprintf('budget.%s', $budgetIndex);
            $periodStartMessages = $this->getMessagesForPeriodStart($budget['period_start'], $budgetForm);

            foreach ($periodStartMessages as $key => $periodStartMessage) {
                $messages[$key] = $periodStartMessage;
            }

            $periodEndMessages = $this->getMessagesForPeriodEnd($budget['period_end'], $budgetForm);

            foreach ($periodEndMessages as $key => $periodEndMessage) {
                $messages[$key] = $periodEndMessage;
            }

            $valueMessages = $this->getMessagesForValue($budget['budget_value'], $budgetForm);

            foreach ($valueMessages as $key => $valueMessage) {
                $messages[$key] = $valueMessage;
            }

            $messages[$budgetForm . '.budget_type.in'] = trans('validation.activity_budget.budget.invalid_type');
            $messages[$budgetForm . '.budget_status.in'] = trans('validation.activity_budget.budget.invalid_status');
            $messages[$budgetForm . '.period_end.0.date.before'] = trans('validation.period_end_cannot_be_more_than_one_year');
            $messages[$budgetForm . '.period_end.0.date.period_start_end'] = trans('validation.date_must_be_after_1900');
        }

        return $messages;
    }

    /**
     * returns messages for period start form.
     *
     * @param $formFields
     * @param $formBase
     *
     * @return array
     */
    public function getMessagesForPeriodStart($formFields, $formBase): array
    {
        $messages = [];

        foreach ($formFields as $periodStartKey => $periodStartVal) {
            $messages[$formBase . '.period_start.' . $periodStartKey . '.date.date'] = trans(
                'validation.date_is_invalid'
            );
            $messages[$formBase . '.period_start.' . $periodStartKey . '.date.date_greater_than'] = trans(
                'validation.date_must_be_after_1900'
            );
            $messages[$formBase . '.period_start.' . $periodStartKey . '.date.period_start_end'] = trans(
                'validation.activity_budget.date.period_start_end'
            );
        }

        return $messages;
    }

    /**
     * returns messages for period end form.
     *
     * @param $formFields
     * @param $formBase
     *
     * @return array
     */
    public function getMessagesForPeriodEnd($formFields, $formBase): array
    {
        $messages = [];

        foreach ($formFields as $periodEndKey => $periodEndVal) {
            $messages[$formBase . '.period_end.' . $periodEndKey . '.date.date'] = trans(
                'validation.date_is_invalid'
            );
            $messages[$formBase . '.period_end.' . $periodEndKey . '.date.date_greater_than'] = trans(
                'validation.date_must_be_after_1900'
            );
            $messages[$formBase . '.period_end.' . $periodEndKey . '.date.after'] = trans(
                'validation.period_end_after'
            );
        }

        return $messages;
    }

    /**
     * Returns messages for value.
     *
     * @param $formFields
     * @param $formBase
     *
     * @return array
     */
    protected function getMessagesForValue($formFields, $formBase): array
    {
        $messages = [];

        foreach ($formFields as $valueIndex => $value) {
            $valueForm = sprintf('%s.budget_value.%s', $formBase, $valueIndex);
            $messages[sprintf('%s.amount.numeric', $valueForm)] = trans(
                'validation.amount_number'
            );
            $messages[sprintf('%s.amount.min', $valueForm)] = trans(
                'validation.amount_negative'
            );
            $messages[sprintf('%s.value_date.date', $valueForm)] = trans(
                'validation.date_is_invalid'
            );
            $messages[sprintf('%s.currency.in', $valueForm)] = trans(
                'validation.invalid_currency'
            );
        }

        return $messages;
    }

    /**
     * Implodes identical ids and returns them.
     *
     * @param $ids
     *
     * @return string
     */
    public function getIdenticalIds($ids): string
    {
        foreach ($ids as $key => $id) {
            $ids[$key] = $id + 1;
        }

        return implode(', ', $ids);
    }
}
