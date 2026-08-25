<?php

declare(strict_types=1);

namespace App\CsvImporter\Entities\Activity\Components\Factory;

use Carbon\Carbon;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Support\Arr;
use Illuminate\Validation\Factory;
use Illuminate\Validation\Validator;

/**
 * Class Validation.
 */
class Validation extends Factory
{
    /**
     * @var array
     */
    protected array $data = [];

    /**
     * Rules for the validation.
     * @var array
     */
    protected array $rules = [];

    /**
     * Messages for failed validation rules.
     * @var array
     */
    protected array $messages = [];

    /**
     * Validation constructor.
     *
     * @param Translator $translator
     */
    public function __construct(Translator $translator)
    {
        parent::__construct($translator);
        $this->registerValidationRules();
    }

    /**
     * Set the data to be validated.
     *
     * @param $data
     *
     * @return $this
     */
    public function sign($data): static
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Append rules and messages for the Validator.
     *
     * @param array $rules
     * @param array $messages
     *
     * @return $this
     */
    public function with(array $rules = [], array $messages = []): static
    {
        $this->rules = $rules;
        $this->messages = $messages;

        return $this;
    }

    /**
     * Get the Validator instance for the data to be validated with the current rules and messages.
     *
     * @return Validator
     */
    public function getValidatorInstance(): Validator
    {
        if (!$this->data) {
            $this->data = [];
        }

        return $this->make($this->data, $this->rules, $this->messages);
    }

    /**
     * Validator for category.
     *
     * @param      $attribute
     * @param      $value
     *
     * @return bool
     */
    public function uniqueCategoryValidator($attribute, $value): bool
    {
        $categoryCodes = [];

        foreach ($value as $category) {
            $code = $category['code'];

            if (in_array($code, $categoryCodes, true)) {
                return false;
            }

            $categoryCodes[] = $code;
        }

        return true;
    }

    /**
     * Validator for unique language/code.
     *
     * @param      $attribute
     * @param      $value
     *
     * @return bool
     */
    public function uniqueLanguageValidator($attribute, $value): bool
    {
        $languageCodes = [];

        foreach ($value as $language) {
            $code = $language['code'] ?? ($language['language'] ?? '');

            if (in_array($code, $languageCodes, true)) {
                return false;
            }

            $languageCodes[] = $code;
        }

        return true;
    }

    /**
     * Register required validation rules.
     *
     * @return void
     */
    public function registerValidationRules(): void
    {
        $this->uniqueLangValidation();
        $this->percentageValidation();
        $this->operatorValidation();
        $this->activityDateValidation();
        $this->startEndDateValidation();
        $this->singleElementValidation();
        $this->sectorValidation();
        $this->recipientRegionCountryValidation();
        $this->budgetValidation();
        $this->transactionValidation();
        $this->mustMatchValidationForReportingOrg();
        $this->participatingOrgRules();
    }

    /**
     * Unique language validation.
     *
     * @return void
     */
    public function uniqueLangValidation(): void
    {
        $this->extendImplicit(
            'unique_lang',
            function ($attribute, $value) {
                $languages = [];

                if (is_array($value)) {
                    foreach ($value as $narrative) {
                        $language = Arr::get($narrative, 'language', '');

                        if (in_array($language, $languages, true)) {
                            return false;
                        }

                        $languages[] = $language;
                    }
                }

                return true;
            }
        );

        $this->extendImplicit(
            'unique_default_lang',
            function ($attribute, $value) {
                $languages = [];
                $defaultLanguage = 'en';
                $check = true;

                if ($value && is_array($value)) {
                    foreach ($value as $narrative) {
                        $languages[] = Arr::get($narrative, 'narrative.0.language', '');
                    }

                    if ((count($languages) === count(array_unique($languages))) && in_array(
                        '',
                        $languages,
                        true
                    ) && in_array($defaultLanguage, $languages, true)) {
                        $check = false;
                    }
                }

                return $check;
            }
        );

        $this->extend(
            'required_with_language',
            function ($attribute, $value, $parameters, $validator) {
                $language = preg_replace('/([^~]+).narrative/', '$1.language', $attribute);
                $request = $this->data;

                return !(Arr::get($request, $language) && !Arr::get($request, $attribute));
            }
        );
    }

    /**
     * Percentage validations.
     *
     * @return void
     */
    public function percentageValidation(): void
    {
        $this->extend(
            'sum',
            function ($attribute, $value, $parameters, $validator) {
                return false;
            }
        );

        $this->extend(
            'total',
            function ($attribute, $value, $parameters, $validator) {
                ($value != 100) ? $check = false : $check = true;

                return $check;
            }
        );

        $this->extend(
            'sector_percentage_sum',
            function ($attribute, $value) {
                $totalPercentage = [];

                if ($value && is_array($value)) {
                    array_walk(
                        $value,
                        function ($element) use (&$totalPercentage) {
                            $sectorVocabulary = (int) $element['sector_vocabulary'];
                            $sectorPercentage = (float) $element['percentage'];

                            if (array_key_exists($sectorVocabulary, $totalPercentage)) {
                                $totalPercentage[$sectorVocabulary] = (float) $totalPercentage[$sectorVocabulary] + (float) $sectorPercentage;
                            } else {
                                $totalPercentage[$sectorVocabulary] = $sectorPercentage;
                            }
                        }
                    );

                    foreach ($totalPercentage as $percentage) {
                        if ($percentage !== '' && $percentage !== 100.0) {
                            return false;
                        }
                    }

                    return true;
                }

                return false;
            }
        );

        $this->extend(
            'percentage_sum',
            function ($attribute, $values) {
                $totalPercentage = 0;
                if ($values) {
                    foreach ($values as $value) {
                        $percentage = Arr::get($value, 'percentage');
                        if (is_numeric($percentage)) {
                            $totalPercentage += $percentage;
                        }
                    }

                    if (count($values) === 1 && $totalPercentage === 0) {
                        return true;
                    }

                    if ($totalPercentage !== 100) {
                        return false;
                    }

                    return true;
                }

                return false;
            }
        );

        $this->extend(
            'recipient_country_region_percentage_sum',
            function ($attribute, $value) {
                return number_format($value) == 100;
            }
        );

        $this->extend('sum_exceeded', function () {
            return false;
        });

        $this->extend('sum_greater_than', function () {
            return false;
        });

        $this->extend('percentage_within_vocabulary', function () {
            return false;
        });

        $this->extend('country_percentage_complete', function () {
            return false;
        });
    }

    /**
     * Operator validations.
     *
     * @return void
     */
    public function operatorValidation(): void
    {
        $this->extend(
            'exclude_operators',
            function ($attribute, $value, $parameters, $validator) {
                if ($value) {
                    return !preg_match('/[\&\|\?|]+/', $value);
                }

                return true;
            }
        );
    }

    /**
     * Date validations.
     *
     * @return void
     */
    public function activityDateValidation(): void
    {
        $this->extend(
            'actual_date',
            function ($attribute, $date) {
                $dateType = (!is_array($date)) ?: Arr::get($date, '0.type');

                if ($dateType === 2 || $dateType === 4) {
                    $actual_date = (!is_array($date)) ?: Arr::get($date, '0.date');
                    if ($actual_date > date('Y-m-d')) {
                        return false;
                    }
                }

                return true;
            }
        );

        $this->extend(
            'multiple_activity_date',
            function ($attribute, $dates) {
                if ($dates && is_array($dates)) {
                    foreach ($dates as $activityDate) {
                        if (count($activityDate) > 1) {
                            return false;
                        }
                    }

                    return true;
                }

                return false;
            }
        );

        $this->extend(
            'diff_one_year',
            function ($attribute, $values) {
                if (count($values) > 1) {
                    return true;
                }

                $periodStart = Arr::get($values[array_key_first($values)], 'period_start.0.date');
                $periodEnd = Arr::get($values[array_key_first($values)], 'period_end.0.date');
                $isPeriodStartDate = dateStrToTime($periodStart);
                $isPeriodEndDate = dateStrToTime($periodEnd);

                if ($isPeriodStartDate !== false && $isPeriodEndDate !== false) {
                    $periodStart = Carbon::parse($periodStart);
                    $periodEnd = Carbon::parse($periodEnd);

                    $diff = $periodStart->diff($periodEnd)->days;

                    return $diff <= 365;
                }

                return true;
            }
        );

        $this->extend(
            'date_greater_than',
            function ($attribute, $value, $parameters, $validator) {
                $inserted = dateFormat('Y', $value);

                if (!$inserted) {
                    return false;
                }

                $since = $parameters[0];

                return $inserted >= $since;
            }
        );
    }

    /**
     * Start date and end date validations.
     *
     * @return void
     */
    public function startEndDateValidation(): void
    {
        $this->extend(
            'start_end_date',
            function ($attribute, $dates) {
                if ($dates && is_array($dates)) {
                    $actual_start_date = Arr::get($dates, 'actual_start_date.0.date');
                    $actual_end_date = Arr::get($dates, 'actual_end_date.0.date');
                    $planned_start_date = Arr::get($dates, 'planned_start_date.0.date');
                    $planned_end_date = Arr::get($dates, 'planned_end_date.0.date');

                    if (($actual_start_date > $actual_end_date) && ($actual_start_date !== '' && $actual_end_date !== '')) {
                        return false;
                    } elseif (($planned_start_date > $planned_end_date) && ($planned_start_date !== '' && $planned_end_date !== '')) {
                        return false;
                    } elseif (($actual_start_date > $planned_end_date) && ($actual_start_date !== '' && $planned_end_date !== '')
                        && ($actual_end_date === '' && $planned_start_date === '')
                    ) {
                        return false;
                    } elseif (($planned_start_date > $actual_end_date) && ($planned_start_date !== '' && $actual_end_date !== '')
                        && ($planned_end_date === '' && $actual_start_date === '')
                    ) {
                        return false;
                    }

                    return true;
                }

                return false;
            }
        );

        $this->extend(
            'start_date_required',
            function ($attribute, $dates) {
                if (is_array($dates)) {
                    if (array_key_exists('actual_start_date', $dates) || array_key_exists(
                        'planned_start_date',
                        $dates
                    )) {
                        return true;
                    }

                    return false;
                }

                return false;
            }
        );

        $this->extend(
            'start_before_end_date',
            function ($attribute, $values) {
                if (count($values) > 1) {
                    return true;
                }

                $periodStart = dateStrToTime(Arr::get($values[array_key_first($values)], 'period_start.0.date'));
                $periodEnd = dateStrToTime(Arr::get($values[array_key_first($values)], 'period_end.0.date'));

                if ($periodStart === false || $periodEnd === false) {
                    return true;
                }

                if ($periodStart <= $periodEnd) {
                    return true;
                }

                return false;
            }
        );

        $this->extend(
            'period_start_end',
            function ($attribute, $value, $parameter, $validator) {
                return !($parameter[1] < $parameter[0]);
            }
        );

        $this->extend('end_later_than_start', function () {
            return false;
        });
    }

    /**
     * Validations stating only one element.
     *
     * @return void
     */
    public function singleElementValidation(): void
    {
        $this->extendImplicit(
            'required_only_one_among',
            function ($attribute, $values, $parameters) {
                [$identifierIndex, $narrativeIndex] = $parameters;
                $isValid = false;

                if ($values) {
                    foreach ($values as $value) {
                        [$identifier, $narratives] = [
                            Arr::get($value, $identifierIndex, ''),
                            Arr::get($value, $narrativeIndex, []),
                        ];

                        foreach ($narratives as $narrative) {
                            $narrativeValue = Arr::get($narrative, 'narrative');

                            if (!$identifier && !$narrativeValue) {
                                return false;
                            }

                            $isValid = true;
                        }
                    }
                }

                return $isValid;
            }
        );

        $this->extendImplicit(
            'only_one_among',
            function ($attribute, $values) {
                foreach ($values as $value) {
                    if ((Arr::get($value, 'organization_identifier_code', '') === '')
                        && (Arr::get($value, 'type', '') === '')
                        && (Arr::get($value, 'provider_activity_id') === '')
                        && (Arr::get($value, 'narrative.0.narrative') === '')
                    ) {
                        return true;
                    }

                    if (($value['organization_identifier_code'] === '') && (Arr::get(
                        $value,
                        'narrative.0.narrative'
                    ) === '')) {
                        return false;
                    }

                    return true;
                }
            }
        );

        $this->extend('already_in_activity', function () {
            return false;
        });

        $this->extend(
            'unique_category',
            function ($attribute, $value) {
                return $this->uniqueCategoryValidator($attribute, $value);
            }
        );

        $this->extend(
            'unique_language',
            function ($attribute, $value) {
                return $this->uniqueLanguageValidator($attribute, $value);
            }
        );
    }

    /**
     * Sector validations.
     *
     * @return void
     */
    public function sectorValidation(): void
    {
        $this->extend(
            'check_sector',
            function ($attribute, $values) {
                $sectorInActivityLevel = true;
                $status = true;

                foreach ($values as $value) {
                    if ($value['activitySector'] === '') {
                        $sectorInActivityLevel = false;
                    }

                    if ($value['sector_vocabulary'] === '' && $value['code'] === ''
                        && $value['text'] === '' && $value['category_code'] === '' && Arr::get(
                            $value,
                            'sdg_goal'
                        ) === '' && Arr::get($value, 'sdg_target') === '' &&
                        $sectorInActivityLevel === false
                    ) {
                        $status = false;
                    } elseif (($value['sector_vocabulary'] !== '' || $value['code'] !== ''
                            || $value['text'] !== '' || $value['category_code'] !== '' || Arr::get(
                                $value,
                                'sdg_goal'
                            ) !== '' || Arr::get($value, 'sdg_target') !== '')
                        && $sectorInActivityLevel === true
                    ) {
                        $status = false;
                    }
                }

                return $status;
            }
        );

        $this->extend('sector_total_percent', function () {
            return false;
        });
    }

    /**
     * Recipient region and country validations.
     *
     * @return void
     */
    public function recipientRegionCountryValidation(): void
    {
        $this->extend(
            'check_recipient_region_country',
            function ($attribute, $values) {
                $transactionRecipientCountry = Arr::get($values, 'recipient_country.0.country_code');
                $transactionRecipientRegion = Arr::get($values, 'recipient_region.0.region_code');
                $activityRecipientRegion = '';
                $activityRecipientCountry = '';

                if (is_array(Arr::get($values, 'activityRecipientCountry'))) {
                    foreach (Arr::get($values, 'activityRecipientCountry', []) as $recipientCountry) {
                        $activityRecipientCountry = $recipientCountry['country_code'];
                    }
                }

                if (is_array(Arr::get($values, 'activityRecipientRegion'))) {
                    foreach (Arr::get($values, 'activityRecipientRegion', []) as $recipientRegion) {
                        $activityRecipientRegion = !empty($recipientRegion['region_code']) ? $recipientRegion['region_code'] : (!empty($recipientRegion['custom_code']) ? $recipientRegion['custom_code'] : '');
                    }
                }

                if (($activityRecipientCountry == '' && $activityRecipientRegion == '')
                    && ($transactionRecipientRegion != '' || $transactionRecipientCountry != '')
                ) {
                    return true;
                }

                if (($activityRecipientCountry != '' || $activityRecipientRegion != '')
                    && ($transactionRecipientRegion == '' && $transactionRecipientCountry == '')
                ) {
                    return true;
                }

                return false;
            }
        );

        $this->extend('allocated_country_percent_exceeded', function () {
            return false;
        });

        $this->extend('allocated_region_total_mismatch', function () {
            return false;
        });

        $this->extend('country_or_region', function () {
            return false;
        });

        $this->extend('duplicate_country_code', function () {
            return false;
        });
    }

    /**
     * Budget validations.
     *
     * @return void
     */
    public function budgetValidation(): void
    {
        $this->extend('budgets_identical', function () {
            return false;
        });
    }

    /**
     * 'Must match' validation for reporting-org.
     *
     * @return void
     */
    public function mustMatchValidationForReportingOrg(): void
    {
        $this->extend(
            'must_match',
            function () {
                return false;
            }
        );
    }

    /**
     * Transaction Validation.
     *
     * @return void
     */
    public function transactionValidation(): void
    {
        $this->extend('sector_required', function () {
            return false;
        });
    }

    /**
     * Register participating org's :
     *  - required_when_narrative_is_empty
     *  - required_when_reference_is_empty
     * rule for CSV import.
     *
     * @return void
     */
    public function participatingOrgRules(): void
    {
        $this->extendImplicit('required_when_narrative_is_empty', fn () => false);
        $this->extendImplicit('required_when_reference_is_empty', fn () => false);
    }
}
