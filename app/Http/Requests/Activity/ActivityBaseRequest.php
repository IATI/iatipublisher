<?php

declare(strict_types=1);

namespace App\Http\Requests\Activity;

use App\IATI\Services\Activity\ActivityService;
use App\IATI\Services\Activity\IndicatorService;
use App\IATI\Services\Activity\PeriodService;
use App\IATI\Services\Activity\ResultService;
use App\IATI\Services\Activity\TransactionService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

/**
 * Class ActivityBaseRequest.
 */
class ActivityBaseRequest extends FormRequest
{
    /**
     * @var ActivityService
     */
    protected ActivityService $activityService;

    private string|null $requestScope = null;

    /**
     * ActivityBaseRequest constructor.
     */
    public function __construct()
    {
        parent::__construct();

        Validator::extendImplicit(
            'unique_lang',
            function ($attribute, $value) {
                return $this->uniqueLangValidator($attribute, $value);
            }
        );

        Validator::extendImplicit(
            'unique_default_lang',
            function ($attribute, $value, $parameters, $validator) {
                return $this->uniqueDefaultLangValidator($attribute, $value, $parameters, $validator);
            }
        );

        Validator::extendImplicit(
            'required_with_language',
            function ($attribute, $value, $parameters, $validator) {
                $language = preg_replace('/([^~]+).narrative/', '$1.language', $attribute);
                $request = FormRequest::all();

                return !(Arr::get($request, $language) && !Arr::get($request, $attribute));
            }
        );

        Validator::extendImplicit(
            'sum',
            function ($attribute, $value, $parameters, $validator) {
                return false;
            }
        );

        Validator::extend(
            'total',
            function ($attribute, $value, $parameters, $validator) {
                ($value != 100) ? $check = false : $check = true;

                return $check;
            }
        );

        Validator::extend(
            'exclude_operators',
            function ($attribute, $value, $parameters, $validator) {
                if ($value) {
                    return !preg_match('/[\&\|\?|]+/', $value);
                }

                return true;
            }
        );

        Validator::extend(
            'date_greater_than',
            function ($attribute, $value, $parameters, $validator) {
                $inserted = Carbon::parse($value)->year;
                $since = $parameters[0];

                return $inserted >= $since;
            }
        );

        Validator::extendImplicit(
            'unique_category',
            function ($attribute, $value) {
                return $this->uniqueCategoryValidator($attribute, $value);
            }
        );

        Validator::extendImplicit(
            'unique_language',
            function ($attribute, $value) {
                return $this->uniqueLanguageValidator($attribute, $value);
            }
        );

        Validator::extendImplicit(
            'period_start_end',
            function ($attribute, $value, $parameter, $validator) {
                return !($parameter[1] < $parameter[0]);
            }
        );

        Validator::extend(
            'must_match',
            function () {
                return false;
            }
        );

        Validator::extendImplicit('required_when_narrative_is_empty', fn () => false);
        Validator::extendImplicit('required_when_reference_is_empty', fn () => false);
    }

    /**
     * Validator for unique lang.
     *
     * @param      $attribute
     * @param      $value
     *
     * @return bool
     */
    public function uniqueLangValidator($attribute, $value): bool
    {
        $languages = [];

        foreach ($value as $narrative) {
            if (in_array($narrative['language'], $languages, true)) {
                return false;
            }

            $languages[] = $narrative['language'];
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
     * Returns default values related to an activity.
     *
     * @return mixed
     */
    public function getActivityDefaultValues(): mixed
    {
        $parameters = $this->route()->parameters();
        $routeParam = explode('.', $this->route()->getName());

        if ($routeParam[1] === 'indicator') {
            $indicator = app()->make(IndicatorService::class)->getIndicator($parameters['id']);
            $activity = $indicator->result->activity;
        } elseif ($routeParam[1] === 'result') {
            $result = app()->make(ResultService::class)->getResult($parameters['id']);
            $activity = $result->activity;
        } else {
            $activity = app()->make(ActivityService::class)->getActivity($parameters['id']);
        }

        return $activity->default_field_values ?? [];
    }

    /**
     * Validator for unique lang.
     *
     * @param      $attribute
     * @param      $value
     * @param      $parameters
     * @param      $validator
     *
     * @return bool
     */
    public function uniqueDefaultLangValidator($attribute, $value, $parameters, $validator): bool
    {
        $languages = [];
        $defaultValues = $this->getActivityDefaultValues();

        if (!empty($defaultValues)) {
            $defaultLanguage = Arr::get($defaultValues, 'default_language', '');

            $validator->addReplacer(
                'unique_default_lang',
                function ($message) use ($validator, $defaultLanguage) {
                    return str_replace(
                        ':language',
                        $this->getCodeListForRequestFiles('Language', 'Activity')[$defaultLanguage],
                        $message
                    );
                }
            );

            $check = true;

            if ($defaultLanguage) {
                foreach ($value as $narrative) {
                    $languages[] = $narrative['language'];
                }

                if (count($languages) === count(array_unique($languages))) {
                    if ((in_array('', $languages, true) || in_array(null, $languages, true)) && in_array(
                        $defaultLanguage,
                        $languages,
                        true
                    )) {
                        $check = false;
                    }
                }
            }

            return $check;
        }

        return true;
    }

    /**
     * returns rules for narrative if narrative is required.
     *
     * @param      $formFields
     * @param      $formBase
     *
     * @return array
     */
    public function getWarningForRequiredNarrative($formFields, $formBase): array
    {
        $rules = [];
        $rules[sprintf('%s.narrative', $formBase)][] = 'unique_lang';
        $rules[sprintf('%s.narrative', $formBase)][] = 'unique_default_lang';

        foreach ($formFields as $narrativeIndex => $narrative) {
            if ($narrative['language']) {
                $rules[sprintf('%s.narrative.%s.narrative', $formBase, $narrativeIndex)] = 'required_with:' . sprintf(
                    '%s.narrative.%s.language',
                    $formBase,
                    $narrativeIndex
                );
            } else {
                $rules[sprintf('%s.narrative.%s.narrative', $formBase, $narrativeIndex)] = 'required';
            }
        }

        return $rules;
    }

    /**
     * get message for narrative.
     *
     * @param $formFields
     * @param $formBase
     *
     * @return array
     */
    public function getMessagesForRequiredNarrative($formFields, $formBase): array
    {
        $messages = [];
        $messages[sprintf('%s.narrative.unique_lang', $formBase)] = trans('validation.narrative_language_unique');

        return $messages;
    }

    /**
     * returns rules for narrative.
     *
     * @param      $formFields
     * @param      $formBase
     *
     * @return array
     */
    public function getWarningForNarrative($formFields, $formBase): array
    {
        $rules = [];
        $rules[sprintf('%s.narrative', $formBase)][] = 'unique_lang';
        $rules[sprintf('%s.narrative', $formBase)][] = 'unique_default_lang';

        foreach ($formFields as $narrativeIndex => $narrative) {
            if (!empty(Arr::get($narrative, 'language', ''))) {
                $rules[sprintf(
                    '%s.narrative.%s.narrative',
                    $formBase,
                    $narrativeIndex
                )][]
                    = 'required_with_language:' . $narrative['narrative'];
            }
        }

        return $rules;
    }

    /**
     * returns rules for narrative.
     *
     * @param      $formFields
     * @param      $formBase
     *
     * @return array
     *
     * @throws \JsonException
     */
    public function getErrorsForNarrative($formFields, $formBase): array
    {
        $rules = [];
        $validLanguages = implode(
            ',',
            array_keys(
                $this->getCodeListForRequestFiles('Language', 'Activity', false, false)
            )
        );

        foreach ($formFields as $narrativeIndex => $narrative) {
            $rules[sprintf('%s.narrative.%s.language', $formBase, $narrativeIndex)][] = 'nullable';
            $rules[sprintf('%s.narrative.%s.language', $formBase, $narrativeIndex)][] = sprintf(
                'in:%s',
                $validLanguages
            );
        }

        return $rules;
    }

    /**
     * returns messages for narrative.
     *
     * @param $formFields
     * @param $formBase
     *
     * @return array
     */
    public function getMessagesForNarrative($formFields, $formBase): array
    {
        $messages = [];
        $messages[sprintf('%s.narrative.unique_lang', $formBase)] = trans('validation.narrative_language_unique');
        $messages[sprintf('%s.narrative.unique_default_lang', $formBase)] = trans(
            'validation.narrative_language_unique'
        );

        foreach ($formFields as $narrativeIndex => $narrative) {
            $messages[sprintf(
                '%s.narrative.%s.narrative.required_with_language',
                $formBase,
                $narrativeIndex
            )]
                = trans('validation.narrative_is_required_when_language_is_populated');
            $messages[sprintf(
                '%s.narrative.%s.language.in',
                $formBase,
                $narrativeIndex
            )]
                = trans('validation.language_is_invalid');
            $messages[sprintf(
                '%s.narrative.%s.narrative.required',
                $formBase,
                $narrativeIndex
            )]
                = trans('validation.narrative_is_required');
        }

        return $messages;
    }

    /**
     * returns rules for period start form.
     *
     * @param $formFields
     * @param $formBase
     *
     * @return array
     */
    public function getWarningForPeriodStart($formFields, $formBase): array
    {
        $rules = [];

        foreach ($formFields as $periodStartKey => $periodStartVal) {
            $rules[$formBase . '.period_start.' . $periodStartKey . '.date'] = 'nullable|date|date_greater_than:1900';
        }

        return $rules;
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
            $messages[$formBase . '.period_end.' . $periodStartKey . '.date.date'] = trans('validation.this_must_be_a_valid_date');
            $messages[$formBase . '.period_end.' . $periodStartKey . '.date.date_greater_than'] = trans(
                'validation.date_must_be_after_1900'
            );
        }

        return $messages;
    }

    /**
     * returns rules for period end form.
     *
     * @param $formFields
     * @param $formBase
     *
     * @return array
     */
    public function getWarningForPeriodEnd($formFields, $formBase): array
    {
        $rules = [];

        foreach ($formFields as $periodEndKey => $periodEndVal) {
            $rules[$formBase . '.period_end.' . $periodEndKey . '.date'] = ['nullable', 'date', 'date_greater_than:1900'];
            $rules[$formBase . '.period_end.' . $periodEndKey . '.date'][] = sprintf(
                'after:%s',
                $formBase . '.period_start.' . $periodEndKey . '.date'
            );
        }

        return $rules;
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
            $messages[$formBase . '.period_end.' . $periodEndKey . '.date.required'] = trans(
                'validation.period_end_required'
            );
            $messages[$formBase . '.period_end.' . $periodEndKey . '.date.date'] = trans(
                'validation.date_must_be_after_1900'
            );
            $messages[$formBase . '.period_end.' . $periodEndKey . '.date.after'] = trans(
                'validation.period_end_after'
            );
            $messages[$formBase . '.period_end.' . $periodEndKey . '.date.date_greater_than'] = trans(
                'validation.date_must_be_after_1900'
            );
        }

        return $messages;
    }

    /**
     * returns rules for Document Link.
     *
     * @param $formFields
     * @param $formBase
     *
     * @return array
     */
    public function getWarningForDocumentLink($formFields, $formBase = null): array
    {
        $rules = [];

        foreach ($formFields as $documentLinkIndex => $documentLink) {
            $documentLinkForm = $formBase ? sprintf('%s.document_link.%s', $formBase, $documentLinkIndex) :
                sprintf(
                    'document_link.%s',
                    $documentLinkIndex
                );

            if (Arr::get($documentLink, 'document_date', null) !== '') {
                $docDateRules = $this->getWarningForDocumentDate($documentLink['document_date'], $documentLinkForm);

                foreach ($docDateRules as $key => $item) {
                    $rules[$key] = $item;
                }
            }

            $rules[sprintf('%s.category', $documentLinkForm)][] = 'unique_category';
            $rules[sprintf('%s.language', $documentLinkForm)][] = 'unique_language';

            $narrativeTitleRules = $this->getWarningForNarrative(
                $documentLink['title'][0]['narrative'],
                sprintf('%s.title.0', $documentLinkForm)
            );

            foreach ($narrativeTitleRules as $key => $item) {
                $rules[$key] = $item;
            }

            $narrativeDesRules = $this->getWarningForNarrative(
                $documentLink['description'][0]['narrative'],
                sprintf('%s.description.0', $documentLinkForm)
            );

            foreach ($narrativeDesRules as $key => $item) {
                $rules[$key] = $item;
            }
        }

        return $rules;
    }

    /**
     * returns rules for Document Link.
     *
     *
     * @param $formFields
     * @param $formBase
     *
     * @return array
     */
    public function getErrorsForDocumentLink($formFields, $formBase = null): array
    {
        $rules = [];

        if (!empty($formFields)) {
            foreach ($formFields as $documentLinkIndex => $documentLink) {
                $documentLinkForm = $formBase ? sprintf('%s.document_link.%s', $formBase, $documentLinkIndex) :
                    sprintf(
                        'document_link.%s',
                        $documentLinkIndex
                    );
                $rules[sprintf('%s.format', $documentLinkForm)] = 'nullable|in:' . implode(
                    ',',
                    array_keys(
                        $this->getCodeListForRequestFiles('FileFormat', 'Activity', false)
                    )
                );

                if (Arr::get($documentLink, 'url', null) !== '') {
                    $rules[sprintf('%s.url', $documentLinkForm)] = 'nullable|url';
                }

                if (Arr::get($documentLink, 'document_date', null) !== '') {
                    $docDateRules = $this->getErrorsForDocumentDate($documentLink['document_date'], $documentLinkForm);

                    foreach ($docDateRules as $key => $item) {
                        $rules[$key] = $item;
                    }
                }

                foreach (array_keys($documentLink['category']) as $index) {
                    $rules[sprintf('%s.category.%s.code', $documentLinkForm, $index)] = 'nullable|in:' . implode(
                        ',',
                        array_keys(
                            $this->getCodeListForRequestFiles(
                                'DocumentCategory',
                                'Activity',
                                false
                            )
                        )
                    );
                }

                foreach (array_keys($documentLink['language']) as $index) {
                    $rules[sprintf('%s.language.%s.language', $documentLinkForm, $index)] = 'nullable|in:' .
                        implode(
                            ',',
                            array_keys(
                                $this->getCodeListForRequestFiles(
                                    'Language',
                                    'Activity',
                                    false,
                                    false
                                )
                            )
                        );
                    $rules[sprintf('%s.language.%s.code', $documentLinkForm, $index)] = 'nullable|in:' . implode(
                        ',',
                        array_keys(
                            $this->getCodeListForRequestFiles(
                                'Language',
                                'Activity',
                                false,
                                false
                            )
                        )
                    );
                }

                $narrativeTitleRules = $this->getErrorsForNarrative(
                    $documentLink['title'][0]['narrative'],
                    sprintf('%s.title.0', $documentLinkForm)
                );

                foreach ($narrativeTitleRules as $key => $item) {
                    $rules[$key] = $item;
                }

                $narrativeDesRules = $this->getErrorsForNarrative(
                    $documentLink['description'][0]['narrative'],
                    sprintf('%s.description.0', $documentLinkForm)
                );

                foreach ($narrativeDesRules as $key => $item) {
                    $rules[$key] = $item;
                }
            }
        }

        return $rules;
    }

    /**
     * returns rules for document date.
     *
     * @param $formFields
     * @param $formIndex
     *
     * @return array
     */
    protected function getWarningForDocumentDate($formFields, $formIndex): array
    {
        $rules = [];

        foreach ($formFields as $documentCategoryIndex => $documentCategory) {
            $rules[sprintf('%s.document_date.%s.date', $formIndex, $documentCategoryIndex)] = [
                'nullable',
                'date_greater_than:1900',
            ];
        }

        return $rules;
    }

    /**
     * returns rules for document date.
     *
     * @param $formFields
     * @param $formIndex
     *
     * @return array
     */
    protected function getErrorsForDocumentDate($formFields, $formIndex): array
    {
        $rules = [];

        foreach ($formFields as $documentCategoryIndex => $documentCategory) {
            $rules[sprintf('%s.document_date.%s.date', $formIndex, $documentCategoryIndex)] = 'nullable|date';
        }

        return $rules;
    }

    /**
     * returns messages for Document Link.
     *
     * @param $formFields
     * @param $formBase
     *
     * @return array
     */
    public function getMessagesForDocumentLink($formFields, $formBase = null): array
    {
        $messages = [];

        foreach ($formFields as $documentLinkIndex => $documentLink) {
            if ($formBase) {
                $documentLinkForm = sprintf('%s.document_link.%s', $formBase, $documentLinkIndex);
            } else {
                $documentLinkForm = sprintf('document_link.%s', $documentLinkIndex);
            }

            $messages[sprintf('%s.format', $documentLinkForm)] = trans(
                'validation.document_link_format_invalid'
            );

            if (Arr::get($documentLink, 'url', null) !== '') {
                $messages[sprintf('%s.url.url', $documentLinkForm)] = trans('validation.url_valid');
            }

            if (Arr::get($documentLink, 'document_date', null) !== '') {
                $docDateMessages = $this->getMessagesForDocumentDate($documentLink['document_date'], $documentLinkForm);

                foreach ($docDateMessages as $key => $item) {
                    $messages[$key] = $item;
                }
            }

            $messages[sprintf(
                '%s.category.unique_category',
                $documentLinkForm
            )]
                = trans('validation.document_link_category_unique');
            $messages[sprintf(
                '%s.category.0.code.in',
                $documentLinkForm
            )]
                = trans('validation.document_link_category_invalid');
            $messages[sprintf(
                '%s.language.unique_language',
                $documentLinkForm
            )]
                = trans('validation.document_link_language_unique');
            $messages[sprintf(
                '%s.language.0.code.in',
                $documentLinkForm
            )]
                = trans('validation.language_is_invalid');
            $narrativeTitleMessages = $this->getMessagesForNarrative(
                $documentLink['title'][0]['narrative'],
                sprintf('%s.title.0', $documentLinkForm)
            );

            foreach ($narrativeTitleMessages as $key => $item) {
                $messages[$key] = $item;
            }

            $narrativeDesMessages = $this->getMessagesForNarrative(
                $documentLink['description'][0]['narrative'],
                sprintf('%s.description.0', $documentLinkForm)
            );

            foreach ($narrativeDesMessages as $key => $item) {
                $messages[$key] = $item;
            }
        }

        return $messages;
    }

    /**
     * returns messages for document date.
     *
     * @param $formFields
     * @param $formIndex
     *
     * @return array
     */
    protected function getMessagesForDocumentDate($formFields, $formIndex): array
    {
        $messages = [];

        foreach ($formFields as $documentCategoryIndex => $documentCategory) {
            $messages[sprintf('%s.document_date.%s.date.date', $formIndex, $documentCategoryIndex)]
                = trans('validation.this_must_be_a_valid_date');
            $messages[sprintf('%s.document_date.%s.date.date_greater_than', $formIndex, $documentCategoryIndex)]
                = trans('validation.date_must_be_after_1900');
        }

        return $messages;
    }

    /**
     * Returns rules for value.
     *
     * @param $formFields
     * @param $formBase
     *
     * @return array
     */
    protected function getWarningForValue($formFields, $formBase): array
    {
        $rules = [];

        foreach ($formFields as $valueIndex => $value) {
            $valueForm = sprintf('%s.value.%s', $formBase, $valueIndex);
            $rules[sprintf('%s.amount', $valueForm)] = 'min:0';
            $rules[sprintf('%s.value_date', $valueForm)] = 'nullable';
        }

        return $rules;
    }

    /**
     * Returns critical rules for value.
     *
     * @param $formFields
     * @param $formBase
     *
     * @return array
     */
    protected function getErrorsForValue($formFields, $formBase): array
    {
        $rules = [];

        foreach ($formFields as $valueIndex => $value) {
            $valueForm = sprintf('%s.value.%s', $formBase, $valueIndex);
            $rules[sprintf('%s.amount', $valueForm)] = 'nullable|numeric|min:0';
            $rules[sprintf('%s.currency', $valueForm)] = 'nullable|in:' . implode(
                ',',
                array_keys(
                    $this->getCodeListForRequestFiles('Currency', 'Activity', false)
                )
            );
            $rules[sprintf('%s.value_date', $valueForm)] = 'nullable|date';
        }

        return $rules;
    }

    protected function getDeprecatedStatusMap()
    {
        if (is_null($this->route())) {
            /* Some tests do not make actual request, so route is null */
            return [];
        }

        $parameters = $this->route()->parameters();
        $routeParam = explode('.', $this->route()->getName());

        /** @var PeriodService|IndicatorService|ResultService|TransactionService|ActivityService $service */
        $element = '';
        if (in_array('period', $routeParam)) {
            $elementId = $this->id ?? '';
            $service = app()->make(PeriodService::class);
        } elseif (in_array('indicator', $routeParam)) {
            $elementId = $this->indicatorId ?? '';
            $service = app()->make(IndicatorService::class);
        } elseif (in_array('result', $routeParam)) {
            $elementId = $this->resultId ?? '';
            $service = app()->make(ResultService::class);
        } elseif (in_array('transaction', $routeParam)) {
            $elementId = $this->transactionId ?? '';
            $service = app()->make(TransactionService::class);
        } else {
            $elementId = $this->id ?? '';
            $service = app()->make(ActivityService::class);
            $element = str_replace('-', '_', Arr::get($routeParam, 2, ''));
        }

        return $service->getDeprecationStatusMap($elementId, $element);
    }

    /**
     * @throws \JsonException
     */
    protected function getCodeListForRequestFiles($listName, $listType, bool $code = true): array
    {
        return getCodeList(
            $listName,
            $listType,
            $code,
            filterDeprecated: true,
            deprecationStatusMap: $this->getDeprecatedStatusMap()
        );
    }
}
