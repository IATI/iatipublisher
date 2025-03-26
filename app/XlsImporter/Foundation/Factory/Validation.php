<?php

declare(strict_types=1);

namespace App\XlsImporter\Foundation\Factory;

use App\XlsImporter\Validator\Traits\RegistersValidationRules;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\Factory;

/**
 * Class Validation.
 */
class Validation extends Factory
{
    use RegistersValidationRules;

    /**
     * @var
     */
    protected $validator;

    /**
     * Element URIs.
     *
     * @var array
     */
    protected array $elementLinks = [
        'Other Identifier' => 'activity.other-identifier.index',
        'Title' => 'activity.title.index',
        'Description' => 'activity.description.index',
        'Activity Status' => 'activity.activity-status.index',
        'Activity Date' => 'activity.activity-date.index',
        'Contact Info' => 'activity.contact-info.index',
        'Activity Scope' => 'activity.activity-scope.index',
        'Participating Organization' => 'activity.participating-organization.index',
        'Recipient Country' => 'activity.recipient-country.index',
        'Recipient Region' => 'activity.recipient-region.index',
        'Location' => 'activity.location.index',
        'SectorCode' => 'activity.sector.index',
        'Tag' => 'activity.tag.index',
        'Country Budget Items' => 'activity.country-budget-items.index',
        'Humanitarian Scope' => 'activity.humanitarian-scope.index',
        'Policy Marker' => 'activity.policy-marker.index',
        'Collaboration Type' => 'activity.collaboration_type.index',
        'Default Flow Type' => 'activity.default-flow-type.index',
        'Default Finance Type' => 'activity.default-finance-type.index',
        'Default Aid Type' => 'activity.default-aid-type.index',
        'Default Tied Status' => 'activity.default-tied-status.index',
        'Budget' => 'activity.budget.index',
        'Planned Disbursement' => 'activity.planned-disbursement.index',
        'Capital Spend' => 'activity.capital-spend.index',
        'Related Activity' => 'activity.related-activity.index',
        'Legacy Data' => 'activity.legacy-data.index',
        'Conditions' => 'activity.condition.index',
        'Document Links' => 'activity.document-link.edit',
        'Transaction' => 'activity.transaction.edit',
        'Results' => 'activity.result.edit',
    ];

    /**
     * @var array|string[]
     */
    protected array $activityElements = [
        'Title' => 'title',
        'Other Identifier' => 'other_identifier',
        'Description' => 'description',
        'Activity Date' => 'activity_date',
        'Recipient Country' => 'recipient_country',
        'Recipient Region' => 'recipient_region',
        'Sector' => 'sector',
        'Tag' => 'tag',
        'Policy Marker' => 'policy_marker',
        'Default Aid Type' => 'default_aid_type',
        'Country Budget Items' => 'country_budget_items',
        'Humanitarian Scope' => 'humanitarian_scope',
        'Related Activity' => 'related_activity',
        'Conditions' => 'conditions',
        'Legacy Data' => 'legacy_data',
        'Document Link' => 'document_link',
        'Contact Info' => 'contact_info',
        'Location' => 'location',
        'Planned Disbursement' => 'planned_disbursement',
        'Participating Org' => 'participating_org',
        'Budget' => 'budget',
        'Transaction' => 'transactions',
    ];

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
     * Initialize the validator object.
     *
     * @param $activity
     * @param $rules
     * @param $messages
     *
     * @return $this
     */
    public function initialize($activity, $rules, $messages): static
    {
        $this->validator = $this->make($activity, $rules, $messages);

        return $this;
    }

    /**
     * Run the validator and check if it passes.
     *
     * @return $this
     */
    public function passes(): static
    {
        $this->validator->passes();

        return $this;
    }

    /**
     * Get the unique validation errors.
     *
     * @param $isDuplicate
     * @param $duplicateTransaction
     * @param $isIdentifierValid
     *
     * @return array
     */
    public function withErrors($isActivity = false): array
    {
        $errors = [];

        foreach ($this->errors() as $index => $error) {
            $element = $this->parseErrors($index);
            $errors[$element][$index] = Arr::get($error, 0, '');
        }

        return $errors;
    }

    /**
     * Parse the errors from the validator.
     *
     * @param $index
     *
     * @return string
     */
    protected function parseErrors($index): string
    {
        return Arr::get(explode('.', $index), 0, '');
    }

    /**
     * Get the Validator error messages.
     *
     * @return mixed
     */
    protected function errors(): mixed
    {
        return $this->validator->errors()->getMessages();
    }

    /**
     * Returns rules for narrative.
     *
     * @param $elementNarrative
     * @param $elementName
     *
     * @return array
     */
    public function getWarningForNarrative($elementNarrative, $elementName): array
    {
        $rules = [];
        $rules[sprintf('%s.narrative', $elementName)][] = 'unique_lang';
        $rules[sprintf('%s.narrative', $elementName)][] = 'unique_default_lang';

        foreach ($elementNarrative as $narrativeIndex => $narrative) {
            $rules[sprintf('%s.narrative.%s.narrative', $elementName, $narrativeIndex)][] = 'required_with_language';
        }

        return $rules;
    }

    /**
     * Returns messages for narrative.
     *
     * @param $elementNarrative
     * @param $elementName
     *
     * @return array
     */
    public function getMessagesForNarrative($elementNarrative, $elementName): array
    {
        $messages = [];
        $messages[sprintf('%s.narrative.unique_lang', $elementName)] = trans(
            'validation.unique',
            ['attribute' => trans('elements/label.language')]
        );

        foreach ($elementNarrative as $narrativeIndex => $narrative) {
            $messages[sprintf(
                '%s.narrative.%s.narrative.required_with_language',
                $elementName,
                $narrativeIndex
            )] = 'The narrative field is required with language.';
        }

        return $messages;
    }

    /**
     * Returns rules for narrative if narrative is required.
     *
     * @param $elementNarrative
     * @param $elementName
     *
     * @return array
     */
    public function getWarningForRequiredNarrative($elementNarrative, $elementName): array
    {
        $rules = [];
        $rules[sprintf('%s.narrative', $elementName)][] = 'unique_lang';
        $rules[sprintf('%s.narrative', $elementName)][] = 'unique_default_lang';

        foreach ($elementNarrative as $narrativeIndex => $narrative) {
            if ((bool) $narrative['language']) {
                $rules[sprintf(
                    '%s.narrative.%s.narrative',
                    $elementName,
                    $narrativeIndex
                )] = 'required_with:' . sprintf(
                    '%s.narrative.%s.language',
                    $elementName,
                    $narrativeIndex
                );
            } else {
                $rules[sprintf('%s.narrative.%s.narrative', $elementName, $narrativeIndex)] = 'required';
            }
        }

        return $rules;
    }

    /**
     * Get message for narrative.
     *
     * @param $elementNarrative
     * @param $elementName
     *
     * @return array
     */
    public function getMessagesForRequiredNarrative($elementNarrative, $elementName): array
    {
        $messages = [];
        $messages[sprintf('%s.narrative.unique_lang', $elementName)] = trans(
            'validation.unique',
            ['attribute' => trans('elements/label.language')]
        );

        foreach ($elementNarrative as $narrativeIndex => $narrative) {
            if ($narrative['language']) {
                $messages[sprintf(
                    '%s.narrative.%s.narrative.required_with',
                    $elementName,
                    $narrativeIndex
                )] = trans(
                    'validation.required_with',
                    ['attribute' => trans('elements/label.narrative'), 'values' => trans('elements/label.language')]
                );
            } else {
                $messages[sprintf(
                    '%s.narrative.%s.narrative.required',
                    $elementName,
                    $narrativeIndex
                )] = trans('validation.required', ['attribute' => trans('elements/label.narrative')]);
            }
        }

        return $messages;
    }

    /**
     * Get rules for transaction's sector element.
     *
     * @param $sector
     * @param $formFields
     * @param $formBase
     *
     * @return array
     */
    public function getWarningForTransactionSectorNarrative($sector, $formFields, $formBase): array
    {
        $rules = [];
        $rules[sprintf('%s.narrative', $formBase)][] = 'unique_lang';
        $rules[sprintf('%s.narrative', $formBase)][] = 'unique_default_lang';

        foreach ($formFields as $narrativeIndex => $narrative) {
            $rules[sprintf('%s.narrative.%s.narrative', $formBase, $narrativeIndex)][] = 'required_with_language';
            if ($narrative['narrative'] !== '') {
                $rules[sprintf(
                    '%s.sector_vocabulary',
                    $formBase
                )] = 'required_with:' . sprintf('%s.narrative.%s.narrative', $formBase, $narrativeIndex);

                if ($sector['sector_vocabulary'] === '1' || $sector['sector_vocabulary'] === '2') {
                    if ($sector['sector_vocabulary'] === '1') {
                        $rules[sprintf(
                            '%s.sector_code',
                            $formBase
                        )] = 'required_with:' . sprintf(
                            '%s.narrative.%s.narrative',
                            $formBase,
                            $narrativeIndex
                        );
                    }
                    if ($sector['sector_vocabulary'] === '2') {
                        $rules[sprintf(
                            '%s.sector_category_code',
                            $formBase
                        )] = 'required_with:' . sprintf(
                            '%s.narrative.%s.narrative',
                            $formBase,
                            $narrativeIndex
                        );
                    }
                } else {
                    $rules[sprintf(
                        '%s.text',
                        $formBase
                    )] = 'required_with:' . sprintf(
                        '%s.narrative.%s.narrative',
                        $formBase,
                        $narrativeIndex
                    );
                }
            }
        }

        return $rules;
    }

    /**
     * Get messages for transaction's sector element.
     *
     * @param $sector
     * @param $formFields
     * @param $formBase
     *
     * @return array
     */
    public function getMessagesForTransactionSectorNarrative($sector, $formFields, $formBase): array
    {
        $messages = [];
        $messages[sprintf('%s.narrative.unique_lang', $formBase)] = trans(
            'validation.unique',
            ['attribute' => trans('elements/label.language')]
        );

        foreach ($formFields as $narrativeIndex => $narrative) {
            $messages[sprintf(
                '%s.narrative.%s.narrative.required_with_language',
                $formBase,
                $narrativeIndex
            )] = trans(
                'validation.required_with',
                ['attribute' => trans('elements/label.narrative'), 'values' => trans('elements/label.language')]
            );

            if ($narrative['narrative'] !== '') {
                $messages[sprintf('%s.sector_vocabulary.required_with', $formBase)] = trans(
                    'validation.required_with',
                    ['attribute' => trans('elements/label.sector_vocabulary'), 'values' => trans('elements/label.narrative')]
                );

                if ($sector['sector_vocabulary'] === '1' || $sector['sector_vocabulary'] === '2') {
                    if ($sector['sector_vocabulary'] === '1') {
                        $messages[sprintf('%s.sector_code.required_with', $formBase)] = trans(
                            'validation.required_with',
                            [
                                'attribute' => trans('elements/label.sector_code'),
                                'values' => trans('elements/label.narrative'),
                            ]
                        );
                    }
                    if ($sector['sector_vocabulary'] === '2') {
                        $messages[sprintf('%s.sector_category_code.required_with', $formBase)] = trans(
                            'validation.required_with',
                            [
                                'attribute' => trans('elements/label.sector_code'),
                                'values' => trans('elements/label.narrative'),
                            ]
                        );
                    }
                } else {
                    $messages[sprintf('%s.text.required_with', $formBase)] = trans(
                        'elementForm.required_with',
                        ['attribute' => trans('elements/label.sector_code'), 'values' => trans('elements/label.narrative')]
                    );
                }
            }
        }

        return $messages;
    }

    /**
     * Returns rules for narrative.
     *
     * @param $formFields
     * @param $formBase
     *
     * @return array
     */
    public function getWarningForResultNarrative($formFields, $formBase): array
    {
        $rules = [];
        $rules[sprintf('%s.narrative', $formBase)][] = 'unique_lang';
        $rules[sprintf('%s.narrative', $formBase)][] = 'unique_default_lang';

        foreach ($formFields as $narrativeIndex => $narrative) {
            $rules[sprintf('%s.narrative.%s.narrative', $formBase, $narrativeIndex)][] = 'required';
        }

        return $rules;
    }

    /**
     * Returns rules for period start form.
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
            $rules[$formBase . '.period_start.' . $periodStartKey . '.date'] = 'required|date';
        }

        return $rules;
    }

    /**
     * Returns messages for period start form.
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
            $messages[$formBase . '.period_start.' . $periodStartKey . '.date.required'] = trans(
                'validation.required',
                ['attribute' => trans('elements/label.period_start')]
            );
            $messages[$formBase . '.period_end.' . $periodStartKey . '.date.date'] = trans(
                'validation.date',
                ['attribute' => trans('elements/label.period_start')]
            );
        }

        return $messages;
    }

    /**
     * Returns rules for period end form.
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
            $rules[$formBase . '.period_end.' . $periodEndKey . '.date'][] = 'nullable';
            $rules[$formBase . '.period_end.' . $periodEndKey . '.date'][] = 'date';
            $rules[$formBase . '.period_end.' . $periodEndKey . '.date'][] = sprintf(
                'after:%s',
                $formBase . '.period_start.' . $periodEndKey . '.date'
            );
        }

        return $rules;
    }

    /**
     * Returns messages for period end form.
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
                'validation.required',
                ['attribute' => trans('elements/label.period_end')]
            );
            $messages[$formBase . '.period_end.' . $periodEndKey . '.date.date'] = trans(
                'validation.date',
                ['attribute' => trans('elements/label.period_end')]
            );
            $messages[$formBase . '.period_end.' . $periodEndKey . '.date.after'] = trans(
                'validation.after',
                ['attribute' => trans('elements/label.period_end'), 'date' => trans('elements/label.period_start')]
            );
        }

        return $messages;
    }

    /**
     * Get the errors in the uploaded Xml File.
     *
     * @param $element
     * @param $elementErrors
     * @param $activityId
     *
     * @return array
     */
    protected function getErrors($element, $elementErrors, $activityId): array
    {
        $errors = [];
        $index = 0;

        foreach ($elementErrors as $elementIndex => $error) {
            $elementName = Str::snake(Str::camel(strtolower($this->parseErrors($elementIndex))));
            $errorIndex = (int) Arr::get(explode('.', $elementIndex), '1');
            $id = Arr::get($this->validator->getData(), $elementName . '.' . $errorIndex . '.id');
            $errors[$index]['link'] = isset($this->elementLinks[$element]) ? route(
                $this->elementLinks[$element],
                [$activityId, $id]
            ) : '';
            $errors[$index]['message'] = $error;
            $index++;
        }

        return $errors;
    }
}
