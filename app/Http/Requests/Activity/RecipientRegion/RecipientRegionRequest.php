<?php

declare(strict_types=1);

namespace App\Http\Requests\Activity\RecipientRegion;

use App\Http\Requests\Activity\ActivityBaseRequest;
use App\IATI\Services\Activity\ActivityService;
use App\IATI\Services\Activity\RecipientRegionService;
use App\IATI\Services\Activity\TransactionService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

/**
 * Class RecipientRegionRequest.
 */
class RecipientRegionRequest extends ActivityBaseRequest
{
    /**
     * @var float
     */
    protected float $allottedRegionPercent;

    /**
     * @var array
     */
    protected array $dataGroupedByVocabulary;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     * @throws BindingResolutionException
     */
    public function rules($recipient_region = []): array
    {
        $data = $this->get('recipient_region') ?? $recipient_region;
        $totalRules = [
            $this->getWarningForRecipientRegion($data),
            $this->getErrorsForRecipientRegion($data),
        ];

        return mergeRules($totalRules);
    }

    /**
     * Get the error message as required.
     *
     * @return array
     */
    public function messages($recipient_region = []): array
    {
        return $this->getMessagesForRecipientRegion($this->get('recipient_region') ?? $recipient_region);
    }

    /**
     * @param $formFields
     *
     * @return array
     */
    public function groupRegion($formFields): array
    {
        $groupedRegion = [];

        foreach ($formFields as $formField) {
            if (array_key_exists($formField['region_vocabulary'], $groupedRegion)) {
                $groupedRegion[$formField['region_vocabulary']]['count'] += 1;
                $groupedRegion[$formField['region_vocabulary']]['total'] += (float) $formField['percentage'];
            } else {
                $groupedRegion[$formField['region_vocabulary']] = [
                    'count' => 1,
                    'total' => (float) $formField['percentage'],
                ];
            }
        }

        return $groupedRegion;
    }

    /**
     * Return critical rules for recipient region.
     *
     * @param $formFields
     * @param $fileUpload
     * @param $recipientCoutnries
     *
     * @return array
     */
    public function getErrorsForRecipientRegion(
        array $formFields,
        bool $fileUpload = false,
        array $recipientCountries = []
    ): array {
        if (empty($formFields)) {
            return [];
        }

        $rules = [];
        $activityService = app()->make(ActivityService::class);
        foreach ($formFields as $recipientRegionIndex => $recipientRegion) {
            $recipientRegionForm = 'recipient_region.' . $recipientRegionIndex;
            $rules[sprintf('%s.region_vocabulary', $recipientRegionForm)] = 'nullable|in:' . implode(
                ',',
                array_keys(
                    $this->getCodeListForRequestFiles('RegionVocabulary', 'Activity', false)
                )
            );
            $rules[sprintf('%s.region_code', $recipientRegionForm)] = 'nullable|in:' . implode(
                ',',
                array_keys(
                    $this->getCodeListForRequestFiles('Region', 'Activity', false)
                )
            );
            $rules[$recipientRegionForm . '.vocabulary_uri'] = 'nullable|url';
            $rules[$recipientRegionForm . '.percentage'] = 'nullable|numeric|min:0';

            $narrativeRules = $this->getErrorsForNarrative($recipientRegion['narrative'], $recipientRegionForm);

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
     * @param  array  $recipientCountries
     *
     * @return array
     * @throws BindingResolutionException
     */
    public function getWarningForRecipientRegion(
        array $formFields,
        bool $fileUpload = false,
        array $recipientCountries = []
    ): array {
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

                return ['recipient_region' => 'already_in_transactions'];
            }

            $this->allottedRegionPercent = $activityService->getPossibleAllocationPercentForRecipientRegion(
                $params['id']
            );
        } else {
            $this->allottedRegionPercent = $activityService->getPossibleAllocationPercentForRecipientRegionFileUpload(
                $recipientCountries
            );
        }

        Validator::extend('allocated_region_total_mismatch', function () {
            return false;
        });

        Validator::extend('single_allocated_region_total_mismatch', function () {
            return false;
        });

        Validator::extend('sum_greater_than', function () {
            return false;
        });

        Validator::extend('percentage_within_vocabulary', function () {
            return false;
        });

        Validator::extend('country_percentage_complete', function () {
            return false;
        });

        $recipientRegionService = app()->make(RecipientRegionService::class);
        $this->dataGroupedByVocabulary = $recipientRegionService->groupRegion($formFields);

        foreach ($formFields as $recipientRegionIndex => $recipientRegion) {
            $recipientRegionForm = 'recipient_region.' . $recipientRegionIndex;
            $narrativeRules = $this->getWarningForNarrative($recipientRegion['narrative'], $recipientRegionForm);

            foreach ($narrativeRules as $key => $item) {
                $rules[$key] = $item;
            }

            $this->getPercentageRule($rules, $recipientRegionForm, $recipientRegion, $fileUpload, $recipientCountries);
        }

        $firstGroupTotalPercentage = Arr::first($this->dataGroupedByVocabulary, static function ($value) {
            return $value;
        });

        $this->merge(['total_region_percentage' => $firstGroupTotalPercentage['total'] ?? null]);

        return $rules;
    }

    /**
     * Returns messages for related activity validations.
     *
     * @param  array  $formFields
     *
     * @return array
     */
    public function getMessagesForRecipientRegion(array $formFields): array
    {
        $messages = [
            'recipient_region.already_in_transactions' => trans(
                'validation.activity_recipient_region.already_in_transaction'
            ),
        ];

        foreach ($formFields as $recipientRegionIndex => $recipientRegion) {
            $recipientRegionForm = 'recipient_region.' . $recipientRegionIndex;
            $messages[sprintf(
                '%s.region_vocabulary.in',
                $recipientRegionForm
            )]
                = trans('validation.vocabulary_is_invalid');
            $messages[sprintf('%s.region_code.in', $recipientRegionForm)] = trans(
                'validation.region_code_is_invalid'
            );
            $messages[$recipientRegionForm . '.percentage.numeric'] = trans(
                'validation.percentage_must_be_a_number'
            );
            $messages[sprintf(
                '%s.vocabulary_uri.url',
                $recipientRegionForm
            )]
                = trans('validation.url_valid');
            $messages[sprintf(
                '%s.percentage.country_percentage_complete',
                $recipientRegionForm
            )]
                = trans('validation.activity_recipient_region.percentage.country_percentage_complete');
            $narrativeMessages = $this->getMessagesForNarrative($recipientRegion['narrative'], $recipientRegionForm);

            foreach ($narrativeMessages as $key => $item) {
                $messages[$key] = $item;
            }

            $messages[$recipientRegionForm . '.percentage.in'] = trans(
                'validation.recipient_country_region_percentage_sum'
            );
            $messages[$recipientRegionForm . '.percentage.allocated_region_total_mismatch'] = trans(
                'validation.recipient_country_region_percentage_sum'
            );
            $messages[$recipientRegionForm . '.percentage.sum_greater_than'] = trans(
                'validation.activity_recipient_region.percentage.sum_greater_than'
            );
            $messages[$recipientRegionForm . '.percentage.percentage_within_vocabulary'] = trans(
                'validation.recipient_country_region_percentage_sum'
            );
            $messages[$recipientRegionForm . '.percentage.min'] = trans(
                'validation.percentage_must_be_at_least_0'
            );
            $messages[$recipientRegionForm . '.percentage.single_allocated_region_total_mismatch'] = trans(
                'validation.recipient_country_region_percentage_sum'
            );
        }

        return $messages;
    }

    /**
     * Gets Percentage rule for recipient region.
     * Note: The rules are applied iteratively for each different vocabulary used.
     *
     * @param $rules
     * @param $fileUpload
     * @param $recipientRegionForm
     * @param $recipientRegion
     * @param $recipientCountries
     *
     * @return array
     * @throws BindingResolutionException
     */
    public function getPercentageRule(
        &$rules,
        $recipientRegionForm,
        $recipientRegion,
        $fileUpload,
        $recipientCountries
    ): array {
        $sumOfAllGroupedPercentages = $this->allottedRegionPercent;
        $percentagesGroupedByVocabulary = $this->dataGroupedByVocabulary;
        $currentVocabularyData = $percentagesGroupedByVocabulary[$recipientRegion['region_vocabulary']];
        $activityService = app()->make(ActivityService::class);

        if ($sumOfAllGroupedPercentages !== 100.0) {
            if (Arr::get($currentVocabularyData, 'count') > 1) {
                if ($currentVocabularyData['total'] !== $sumOfAllGroupedPercentages) {
                    $rules[$recipientRegionForm . '.percentage'][] = 'nullable';
                    $rules[$recipientRegionForm . '.percentage'][] = 'allocated_region_total_mismatch';
                }
            } elseif ($sumOfAllGroupedPercentages === 0.0 && $currentVocabularyData['total'] > $sumOfAllGroupedPercentages) {
                $rules[$recipientRegionForm . '.percentage'][] = 'country_percentage_complete';
            } elseif ($sumOfAllGroupedPercentages !== 0.0) {
                $rules[$recipientRegionForm . '.percentage'][] = 'in:' . $sumOfAllGroupedPercentages;
            } else {
                $rules[$recipientRegionForm . '.percentage'][] = 'nullable';
            }
        } elseif ($currentVocabularyData['total'] > 100.0 && ($currentVocabularyData['total'] - 100.0) > 0.00001) {
            $rules[$recipientRegionForm . '.percentage'][] = 'sum_greater_than';
        } elseif ($currentVocabularyData['total'] !== $percentagesGroupedByVocabulary[array_key_first(
            $percentagesGroupedByVocabulary
        )]['total']) {
            $rules[$recipientRegionForm . '.percentage'][] = 'percentage_within_vocabulary';
        }

        if (!$fileUpload) {
            $params = $this->route()->parameters();

            if ($currentVocabularyData['total'] === 0.0 && !$activityService->hasRecipientCountryDefinedInActivity(
                $params['id']
            )) {
                $rules[$recipientRegionForm . '.percentage'][] = 'nullable';
            } elseif ($currentVocabularyData['total'] < $sumOfAllGroupedPercentages && $activityService->hasRecipientCountryDefinedInActivity(
                $params['id']
            )) {
                if ($currentVocabularyData['total'] !== $percentagesGroupedByVocabulary[array_key_first(
                    $percentagesGroupedByVocabulary
                )]['total']) {
                    $rules[$recipientRegionForm . '.percentage'] = 'percentage_within_vocabulary';
                } else {
                    $rules[$recipientRegionForm . '.percentage'] = 'single_allocated_region_total_mismatch';
                }
            }
        } elseif ($currentVocabularyData['total'] === 0.0 && is_array_values_null($recipientCountries)) {
            $rules[$recipientRegionForm . '.percentage'][] = 'nullable';
        } elseif ($currentVocabularyData['total'] !== $percentagesGroupedByVocabulary[array_key_first(
            $percentagesGroupedByVocabulary
        )]['total']) {
            $rules[$recipientRegionForm . '.percentage'][] = 'percentage_within_vocabulary';
        } elseif ($currentVocabularyData['total'] < $sumOfAllGroupedPercentages && !is_array_values_null(
            $recipientCountries
        )) {
            $rules[$recipientRegionForm . '.percentage'] = 'allocated_region_total_mismatch';
        }

        return $rules;
    }
}
