<?php

declare(strict_types=1);

namespace App\Http\Requests\Activity\RecipientCountry;

use App\Http\Requests\Activity\ActivityBaseRequest;
use App\IATI\Services\Activity\ActivityService;
use App\IATI\Services\Activity\TransactionService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Validator;

/**
 * Class RecipientCountryRequest.
 */
class RecipientCountryRequest extends ActivityBaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     * @throws BindingResolutionException
     */
    public function rules(): array
    {
        $totalRules = [
            $this->getWarningForRecipientCountry($this->get('recipient_country')),
            $this->getErrorsForRecipientCountry($this->get('recipient_country')),
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
        return $this->getMessagesForRecipientCountry($this->get('recipient_country'));
    }

    /**
     * @param $formFields
     *
     * @return float
     */
    public function getTotalPercent($formFields): float
    {
        $total = 0;

        foreach ($formFields as $formField) {
            //if clause added to bypass server error. Numeric validation will invoke and data wont be saved
            if (is_numeric($formField['percentage'])) {
                $total += $formField['percentage'];
            }
        }

        return $total;
    }

    public function getErrorsForRecipientCountry(array $formFields, bool $fileUpload = false): array
    {
        if (empty($formFields)) {
            return [];
        }

        $rules = [];

        foreach ($formFields as $recipientCountryIndex => $recipientCountry) {
            $recipientCountryForm = 'recipient_country.' . $recipientCountryIndex;
            $rules[sprintf('%s.country_code', $recipientCountryForm)] = 'nullable|in:' . implode(
                ',',
                array_keys(
                    $this->getCodeListForRequestFiles('Country', 'Activity', false)
                )
            );
            $rules[$recipientCountryForm . '.percentage'] = 'nullable|numeric|min:0';

            $narrativeRules = $this->getErrorsForNarrative($recipientCountry['narrative'], $recipientCountryForm);

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
     * @param  bool  $fileUpload
     *
     * @return array
     * @throws BindingResolutionException
     */
    public function getWarningForRecipientCountry(array $formFields, bool $fileUpload = false): array
    {
        if (empty($formFields)) {
            return [];
        }

        $rules = [];
        $activityService = app()->make(ActivityService::class);

        if (!$fileUpload) {
            $params = $this->route()->parameters();
            $transactionService = app()->make(TransactionService::class);

            if ($transactionService->hasRecipientRegionOrCountryDefinedInTransaction($params['id'])) {
                Validator::extend('already_in_transactions', function () {
                    return false;
                });

                return ['recipient_country' => 'already_in_transactions'];
            }

            Validator::extend('allocated_country_percent', function () {
                return false;
            });

            $allottedCountryPercent = $activityService->getPossibleAllocationPercentForRecipientCountry($params['id']);
        }

        Validator::extend('sum_exceeded', function () {
            return false;
        });

        Validator::extend('region_percentage_complete', function () {
            return false;
        });

        Validator::extend('duplicate_country_code', function () {
            return false;
        });

        $totalCountryPercent = $this->getTotalPercent($formFields);
        $groupedCountryCode = $this->getGroupedCountryCode($formFields);

        $this->merge(['total_country_percentage' => $totalCountryPercent]);

        foreach ($formFields as $recipientCountryIndex => $recipientCountry) {
            $recipientCountryForm = 'recipient_country.' . $recipientCountryIndex;

            if (in_array($recipientCountry['country_code'], $groupedCountryCode, true)) {
                $rules[sprintf('%s.country_code', $recipientCountryForm)][] = 'duplicate_country_code';
            }

            $narrativeRules = $this->getWarningForNarrative($recipientCountry['narrative'], $recipientCountryForm);

            foreach ($narrativeRules as $key => $item) {
                $rules[$key] = $item;
            }

            if ($totalCountryPercent > 100.0 && ($totalCountryPercent - 100.0) > 0.000001) {
                $rules[$recipientCountryForm . '.percentage'][] = 'sum_exceeded';
            }

            if (!$fileUpload) {
                if ($allottedCountryPercent === 100.0) {
                    $rules[$recipientCountryForm . '.percentage'][] = 'nullable';
                    $rules[$recipientCountryForm . '.percentage'][] = 'max:100';
                }

                if ($allottedCountryPercent === 100.0 &&
                    $totalCountryPercent < $allottedCountryPercent &&
                    $activityService->hasRecipientRegionDefinedInActivity(
                        $params['id']
                    )) {
                    $rules[$recipientCountryForm . '.percentage'][] = 'allocated_country_percent';
                }

                if ($allottedCountryPercent === 0.0) {
                    $rules[$recipientCountryForm . '.percentage'][] = $totalCountryPercent > 0.0
                        ? 'region_percentage_complete'
                        : 'nullable';
                } elseif ($totalCountryPercent !== $allottedCountryPercent && $allottedCountryPercent !== 100.0) {
                    $rules[$recipientCountryForm . '.percentage'][] = 'allocated_country_percent';
                }
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
    public function getMessagesForRecipientCountry(array $formFields): array
    {
        $messages = [
            'recipient_country.already_in_transactions' => trans(
                'validation.activity_recipient_country.already_in_transaction'
            ),
        ];

        foreach ($formFields as $recipientCountryIndex => $recipientCountry) {
            $recipientCountryForm = 'recipient_country.' . $recipientCountryIndex;
            $messages[sprintf('%s.country_code.in', $recipientCountryForm)] = trans(
                'validation.country_code'
            );
            $messages[sprintf(
                '%s.country_code.duplicate_country_code',
                $recipientCountryForm
            )]
                = trans('validation.activity_recipient_country.duplicate_country_code');
            $messages[$recipientCountryForm . '.percentage.numeric'] = trans(
                'validation.percentage_must_be_a_number'
            );
            $messages[$recipientCountryForm . '.percentage.max'] = trans(
                'validation.percentage_cannot_be_greater_than_100'
            );
            $messages[$recipientCountryForm . '.percentage.sum_exceeded'] = trans(
                'validation.activity_recipient_country.percentage.sum_exceeded'
            );
            $messages[$recipientCountryForm . '.percentage.min'] = trans(
                'validation.percentage_must_be_at_least_0'
            );
            $messages[$recipientCountryForm . '.percentage.region_percentage_complete'] = trans(
                'validation.activity_recipient_country.percentage.region_percentage_complete'
            );
            $narrativeMessages = $this->getMessagesForNarrative($recipientCountry['narrative'], $recipientCountryForm);

            foreach ($narrativeMessages as $key => $item) {
                $messages[$key] = $item;
            }
            $messages[$recipientCountryForm . '.percentage.in'] = trans(
                'validation.recipient_country_region_percentage_sum'
            );
            $messages[$recipientCountryForm . '.percentage.allocated_country_percent'] = trans(
                'validation.recipient_country_region_percentage_sum'
            );
        }

        return $messages;
    }

    /**
     * Groups Country code.
     *
     * @param $formFields
     * @return array
     */
    public function getGroupedCountryCode($formFields): array
    {
        $array = $formFields;
        $column = array_column($array, 'country_code');

        if ($column[0] === null) {
            return [];
        }

        $column = array_map(function ($item) {
            return $item ?? '';
        }, $column);

        $counted = !empty($column) ? array_count_values($column) : [];
        $duplicates = array_filter($counted, static function ($value) {
            return $value > 1;
        });

        return array_keys($duplicates);
    }
}
