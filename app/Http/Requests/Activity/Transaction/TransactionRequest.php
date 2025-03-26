<?php

declare(strict_types=1);

namespace App\Http\Requests\Activity\Transaction;

use App\Http\Requests\Activity\ActivityBaseRequest;
use App\IATI\Services\Activity\ActivityService;
use App\IATI\Services\Activity\TransactionService;
use App\Rules\AlreadyInActivity;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

/**
 * Class TransactionRequest.
 */
class TransactionRequest extends ActivityBaseRequest
{
    /**
     * @var array
     */
    protected array $transactionFormField;

    /**
     * @var array
     */
    protected array $activityFormField;

    /**
     * @var bool
     */
    protected bool $hasCountryOrRegionDefinedInActivity = false;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     * @throws BindingResolutionException
     * @throws \JsonException
     */
    public function rules(): array
    {
        $data = request()->except('_token');

        $totalRules = [
            $this->getWarningForTransaction($data),
            $this->getErrorsForTransaction($data),
            $this->getCriticalErrorsForTransaction($data),
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
        return $this->getMessagesForTransaction(request()->except('_token'));
    }

    /**
     * @throws BindingResolutionException
     */
    public function getCriticalErrorsForTransaction(
        array $formFields,
        bool $fileUpload = false,
        array $activityData = [],
        array $multipleTransactions = []
    ): array {
        $rules = [];

        $tempRules = [
            $this->getCriticalErrorForSector(
                Arr::get($formFields, 'sector', []),
                $fileUpload,
                Arr::get($activityData, 'sector', []),
                $multipleTransactions
            ),
            $this->getCriticalErrorForRecipientRegion(
                Arr::get($formFields, 'recipient_region', []),
                $fileUpload,
                Arr::get($activityData, 'recipient_region', [])
            ),
            $this->getCriticalErrorForRecipientCountry(
                Arr::get($formFields, 'recipient_country', []),
                $fileUpload,
                Arr::get($activityData, 'recipient_country', [])
            ),
        ];

        foreach ($tempRules as $tempRule) {
            foreach ($tempRule as $idx => $rule) {
                $rules[$idx] = $rule;
            }
        }

        return $rules;
    }

    /**
     * Returns rules for transaction.
     *
     * @param  array  $formFields
     * @param  bool  $fileUpload
     * @param  array  $activityData
     *
     * @return array
     * @throws BindingResolutionException|\JsonException
     */
    public function getWarningForTransaction(
        array $formFields,
        bool $fileUpload = false,
        array $activityData = [],
        array $multipleTransactions = []
    ): array {
        $rules = [];
        $this->transactionFormField = $formFields;
        $this->activityFormField = $activityData;

        Validator::extend('country_or_region', static function () {
            return false;
        });

        $tempRules = [
            $this->getTransactionDateRules(Arr::get($formFields, 'transaction_date', [])),
            $this->getValueRules(Arr::get($formFields, 'value', [])),
            $this->getDescriptionRules(Arr::get($formFields, 'description', [])),
            $this->getSectorsRules(
                Arr::get($formFields, 'sector', []),
                $fileUpload,
                Arr::get($activityData, 'sector', []),
                $multipleTransactions
            ),
            $this->getWarningForProviderOrg(Arr::get($formFields, 'provider_organization', [])),
            $this->getWarningForReceiverOrg(Arr::get($formFields, 'receiver_organization', [])),
            $this->getWarningForRecipientRegion(
                Arr::get($formFields, 'recipient_region', []),
                $fileUpload,
                Arr::get($activityData, 'recipient_region', [])
            ),
            $this->getWarningForRecipientCountry(
                Arr::get($formFields, 'recipient_country', []),
                $fileUpload,
                Arr::get($activityData, 'recipient_country', [])
            ),
        ];

        foreach ($tempRules as $tempRule) {
            foreach ($tempRule as $idx => $rule) {
                $rules[$idx] = $rule;
            }
        }

        return $rules;
    }

    /**
     * Returns rules for transaction.
     *
     * @param  array  $formFields
     * @param  bool  $fileUpload
     * @param  array  $activityData
     *
     * @return array
     * @throws BindingResolutionException
     */
    public function getErrorsForTransaction(
        array $formFields,
        bool $fileUpload = false,
        array $activityData = []
    ): array {
        $rules = [];

        $rules['transaction_type.0.transaction_type_code'] = 'nullable|in:' . implode(
            ',',
            array_keys(
                $this->getCodeListForRequestFiles('TransactionType', 'Activity', false)
            )
        );
        $rules['flow_type.0.flow_type'] = 'nullable|in:' . implode(
            ',',
            array_keys(
                $this->getCodeListForRequestFiles('FlowType', 'Activity', false)
            )
        );
        $rules['finance_type.0.finance_type'] = 'nullable|in:' . implode(
            ',',
            array_keys(
                $this->getCodeListForRequestFiles('FinanceType', 'Activity', false)
            )
        );
        $rules['aid_type.0.aid_type_vocabulary'] = 'nullable|in:' . implode(
            ',',
            array_keys(
                $this->getCodeListForRequestFiles('AidTypeVocabulary', 'Activity', false)
            )
        );
        $rules['aid_type.0.aid_type_code'] = 'nullable|in:' . implode(
            ',',
            array_keys(
                $this->getCodeListForRequestFiles('AidType', 'Activity', false)
            )
        );
        $rules['aid_type.0.earmarking_category'] = 'nullable|in:' . implode(
            ',',
            array_keys(
                $this->getCodeListForRequestFiles('EarmarkingCategory', 'Activity', false)
            )
        );
        $rules['aid_type.0.earmarking_modality'] = 'nullable|in:' . implode(
            ',',
            array_keys(
                $this->getCodeListForRequestFiles('EarmarkingModality', 'Activity', false)
            )
        );
        $rules['aid_type.0.cash_and_voucher_modalities'] = 'nullable|in:' . implode(
            ',',
            array_keys(
                $this->getCodeListForRequestFiles(
                    'CashandVoucherModalities',
                    'Activity',
                    false
                )
            )
        );
        $rules['tied_status.0.tied_status_code'] = 'nullable|in:' . implode(
            ',',
            array_keys(
                $this->getCodeListForRequestFiles('TiedStatus', 'Activity', false)
            )
        );
        $rules['disbursement_channel.0.disbursement_channel_code'] = 'nullable|in:' . implode(
            ',',
            array_keys(
                $this->getCodeListForRequestFiles('DisbursementChannel', 'Activity', false)
            )
        );

        $tempRules = [
            $this->getCriticalTransactionDateRules(Arr::get($formFields, 'transaction_date', [])),
            $this->getCriticalValueRules(Arr::get($formFields, 'value', [])),
            $this->getCriticalDescriptionRules(Arr::get($formFields, 'description', [])),
            $this->getCriticalSectorsRules(
                Arr::get($formFields, 'sector', []),
                $fileUpload,
                Arr::get($activityData, 'sector', [])
            ),
            $this->getErrorsForProviderOrg(Arr::get($formFields, 'provider_organization', [])),
            $this->getErrorsForReceiverOrg(Arr::get($formFields, 'receiver_organization', [])),
            $this->getErrorsForRecipientRegion(
                Arr::get($formFields, 'recipient_region', []),
                $fileUpload,
                Arr::get($activityData, 'recipient_region', [])
            ),
            $this->getErrorsForRecipientCountry(
                Arr::get($formFields, 'recipient_country', []),
                $fileUpload,
                Arr::get($activityData, 'recipient_country', [])
            ),
        ];

        foreach ($tempRules as $tempRule) {
            foreach ($tempRule as $idx => $rule) {
                $rules[$idx] = $rule;
            }
        }

        return $rules;
    }

    /**
     * Returns messages for transaction validations.
     *
     * @param  array  $formFields
     *
     * @return array
     */
    public function getMessagesForTransaction(array $formFields): array
    {
        $messages = [];
        $messages['transaction_type.0.transaction_type_code.in'] = trans(
            'validation.type_is_invalid'
        );
        $messages['flow_type.0.flow_type.in'] = trans(
            'validation.this_field_is_invalid'
        );
        $messages['finance_type.0.finance_type.in'] = trans(
            'validation.this_field_is_invalid'
        );
        $messages['aid_type.0.aid_type_vocabulary.in'] = trans(
            'validation.vocabulary_is_invalid'
        );
        $messages['aid_type.0.aid_type_code.in'] = trans(
            'validation.this_field_is_invalid'
        );
        $messages['aid_type.0.earmarking_category.in'] = trans(
            'validation.this_field_is_invalid'
        );
        $messages['aid_type.0.earmarking_modality.in'] = trans(
            'validation.this_field_is_invalid'
        );
        $messages['aid_type.0.cash_and_voucher_modalities.in'] = trans(
            'validation.this_field_is_invalid'
        );
        $messages['tied_status.0.tied_status_code.in'] = trans(
            'validation.activity_transactions.aid_type.invalid_status_code'
        );
        $tempMessages = [
            $this->getTransactionDateMessages(Arr::get($formFields, 'transaction_date', [])),
            $this->getValueMessages(Arr::get($formFields, 'value', [])),
            $this->getDescriptionMessages(Arr::get($formFields, 'description', [])),
            $this->getSectorsMessages(Arr::get($formFields, 'sector', [])),
            $this->getMessagesForProviderOrg(Arr::get($formFields, 'provider_organization', [])),
            $this->getMessagesForReceiverOrg(Arr::get($formFields, 'receiver_organization', [])),
            $this->getMessagesForRecipientRegion(Arr::get($formFields, 'recipient_region', [])),
            $this->getMessagesForRecipientCountry(Arr::get($formFields, 'recipient_country', [])),
        ];

        foreach ($tempMessages as $tempMessage) {
            foreach ($tempMessage as $idx => $message) {
                $messages[$idx] = $message;
            }
        }

        return $messages;
    }

    /**
     * get transaction date rules.
     *
     * @param  array  $formFields
     *
     * @return array
     */
    public function getTransactionDateRules(array $formFields): array
    {
        $rules = [];

        foreach ($formFields as $dateIndex => $date) {
            $dateForm = sprintf('transaction_date.%s', $dateIndex);
            $rules[sprintf('%s.date', $dateForm)] = 'nullable|before:tomorrow';
        }

        return $rules;
    }

    /**
     * get critical rules for transaction date.
     *
     * @param  array  $formFields
     *
     * @return array
     */
    public function getCriticalTransactionDateRules(array $formFields): array
    {
        $rules = [];

        foreach ($formFields as $dateIndex => $date) {
            $dateForm = sprintf('transaction_date.%s', $dateIndex);
            $rules[sprintf('%s.date', $dateForm)] = 'nullable|date';
        }

        return $rules;
    }

    /**
     * get transaction date error message.
     *
     * @param  array  $formFields
     *
     * @return array
     */
    public function getTransactionDateMessages(array $formFields): array
    {
        $messages = [];

        foreach ($formFields as $dateIndex => $date) {
            $dateForm = sprintf('transaction_date.%s', $dateIndex);
            $messages[sprintf('%s.date.before', $dateForm)] = trans(
                'validation.future_date'
            );
            $messages[sprintf('%s.date.date', $dateForm)] = trans(
                'validation.date_is_invalid'
            );
        }

        return $messages;
    }

    /**
     * get values rules.
     *
     * @param  array  $formFields
     *
     * @return array
     */
    public function getValueRules(array $formFields): array
    {
        $rules = [];

        foreach ($formFields as $valueIndex => $value) {
            $valueForm = sprintf('value.%s', $valueIndex);
            $rules[sprintf('%s.amount', $valueForm)] = 'nullable|numeric';
            $rules[sprintf('%s.date', $valueForm)] = 'nullable|before:tomorrow';
        }

        return $rules;
    }

    /**
     * get values critical rules.
     *
     * @param  array  $formFields
     *
     * @return array
     */
    public function getCriticalValueRules(array $formFields): array
    {
        $rules = [];

        foreach ($formFields as $valueIndex => $value) {
            $valueForm = sprintf('value.%s', $valueIndex);
            $rules[sprintf('%s.amount', $valueForm)] = 'nullable|numeric';
            $rules[sprintf('%s.date', $valueForm)] = 'nullable|date';
            $rules[sprintf('%s.currency', $valueForm)] = sprintf(
                'nullable|in:%s',
                implode(
                    ',',
                    array_keys(
                        $this->getCodeListForRequestFiles('Currency', 'Activity')
                    )
                )
            );
        }

        return $rules;
    }

    /**
     * get value error message.
     *
     * @param  array  $formFields
     *
     * @return array$transactionForm
     */
    public function getValueMessages(array $formFields): array
    {
        $messages = [];

        foreach ($formFields as $valueIndex => $value) {
            $valueForm = sprintf('value.%s', $valueIndex);
            $messages[sprintf('%s.amount.numeric', $valueForm)] = trans(
                'validation.amount_number'
            );
            $messages[sprintf('%s.date.before', $valueForm)] = trans(
                'validation.future_date'
            );
            $messages[sprintf('%s.date.date', $valueForm)] = trans(
                'validation.date_is_invalid'
            );
            $messages[sprintf('%s.currency.in', $valueForm)] = trans(
                'validation.invalid_currency'
            );
        }

        return $messages;
    }

    /**
     * get description rules.
     *
     * @param  array  $formFields
     *
     * @return array
     */
    public function getDescriptionRules(array $formFields): array
    {
        $rules = [];

        foreach ($formFields as $descriptionIndex => $description) {
            $narrativeForm = sprintf('description.%s', $descriptionIndex);
            $narrativeRules = $this->getWarningForNarrative($description['narrative'], $narrativeForm);

            foreach ($narrativeRules as $key => $item) {
                $rules[$key] = $item;
            }
        }

        return $rules;
    }

    /**
     * get description critical rules.
     *
     * @param  array  $formFields
     *
     * @return array
     */
    public function getCriticalDescriptionRules(array $formFields): array
    {
        $rules = [];

        foreach ($formFields as $descriptionIndex => $description) {
            $narrativeForm = sprintf('description.%s', $descriptionIndex);
            $narrativeRules = $this->getErrorsForNarrative($description['narrative'], $narrativeForm);

            foreach ($narrativeRules as $key => $item) {
                $rules[$key] = $item;
            }
        }

        return $rules;
    }

    /**
     * get description error message.
     *
     * @param  array  $formFields
     *
     * @return array
     */
    public function getDescriptionMessages(array $formFields): array
    {
        $messages = [];

        foreach ($formFields as $descriptionIndex => $description) {
            $narrativeForm = sprintf('description.%s', $descriptionIndex);
            $narrativeMessages = $this->getMessagesForNarrative($description['narrative'], $narrativeForm);

            foreach ($narrativeMessages as $key => $item) {
                $messages[$key] = $item;
            }
        }

        return $messages;
    }

    /**
     * returns rules for sector.
     *
     * @param  array  $formFields
     * @param  bool  $fileUpload
     * @param  array  $activitySectors
     * @param  array  $transactions
     *
     * @return array
     * @throws BindingResolutionException|\JsonException
     */
    public function getSectorsRules(
        array $formFields,
        bool $fileUpload,
        array $activitySectors,
        array $transactions = []
    ): array {
        if (empty($formFields)) {
            return [];
        }

        $rules = [];
        $transactionService = app()->make(TransactionService::class);

        Validator::extend('sector_required', function () {
            return false;
        });

        if (!$fileUpload) {
            $params = $this->route()->parameters();

            $transactionId = isset($params['transactionId']) ? (int) $params['transactionId'] : null;

            if (is_variable_null($formFields) && $transactionService->hasSectorDefinedInTransaction(
                $params['id'],
                $transactionId
            )) {
                $rules['sector'] = 'sector_required';
            }
        } else {
            $hasSector = false;

            if (!empty($transactions) && count($transactions) > 1) {
                $sectors = [];

                foreach ($transactions as $transaction) {
                    $sectors[] = Arr::get($transaction, 'sector', []);
                }

                $notNullCount = 0;
                $nullCount = 0;

                foreach ($sectors as $sector) {
                    if (!is_array_value_empty($sector)) {
                        $notNullCount++;
                    } else {
                        $nullCount++;
                    }
                }

                if ($notNullCount >= 1 && $nullCount >= 1) {
                    $hasSector = true;
                }
            }

            if ($hasSector) {
                $rules['sector'] = 'sector_required';
            }
        }

        foreach ($formFields as $sectorIndex => $sector) {
            $sectorForm = sprintf('sector.%s', $sectorIndex);
            $narrativeRules = $this->getWarningForNarrative($sector['narrative'], $sectorForm);

            foreach ($narrativeRules as $key => $item) {
                $rules[$key] = $item;
            }
        }

        return $rules;
    }

    /**
     * returns critical rules for sector.
     *
     * @param  array  $formFields
     * @param  bool  $fileUpload
     * @param  array  $activitySectors
     *
     * @return array
     * @throws BindingResolutionException
     */
    public function getCriticalSectorsRules(array $formFields, bool $fileUpload, array $activitySectors): array
    {
        if (empty($formFields)) {
            return [];
        }

        foreach ($formFields as $sectorIndex => $sector) {
            $sectorForm = sprintf('sector.%s', $sectorIndex);
            $rules[sprintf('%s.sector_vocabulary', $sectorForm)] = 'nullable|in:' . implode(
                ',',
                array_keys(
                    $this->getCodeListForRequestFiles('SectorVocabulary', 'Activity', false)
                )
            );
            $rules[sprintf('%s.code', $sectorForm)] = 'nullable|in:' . implode(
                ',',
                array_keys(
                    $this->getCodeListForRequestFiles('SectorCode', 'Activity', false)
                )
            );
            $rules[sprintf('%s.category_code', $sectorForm)] = 'nullable|in:' . implode(
                ',',
                array_keys(
                    $this->getCodeListForRequestFiles('SectorCategory', 'Activity', false)
                )
            );
            $rules[sprintf('%s.sdg_goal', $sectorForm)] = 'nullable|in:' . implode(
                ',',
                array_keys(
                    $this->getCodeListForRequestFiles('UNSDG-Goals', 'Activity', false)
                )
            );
            $rules[sprintf('%s.sdg_target', $sectorForm)] = 'nullable|in:' . implode(
                ',',
                array_keys(
                    $this->getCodeListForRequestFiles('UNSDG-Targets', 'Activity', false)
                )
            );

            if (isset($sector['sector_vocabulary']) && $sector['sector_vocabulary'] === '99') {
                $rules[sprintf('%s.vocabulary_uri', $sectorForm)] = 'nullable|url';
            }

            $narrativeRules = $this->getErrorsForNarrative($sector['narrative'], $sectorForm);

            foreach ($narrativeRules as $key => $item) {
                $rules[$key] = $item;
            }
        }

        return $rules;
    }

    /**
     * returns messages for sector.
     *
     * @param  array  $formFields
     *
     * @return array
     */
    public function getSectorsMessages(array $formFields): array
    {
        $messages = [
            'sector.sector_required' => trans('validation.activity_transactions.sector.required'),
        ];

        if (empty($formFields)) {
            return $messages;
        }

        foreach ($formFields as $sectorIndex => $sector) {
            $sectorForm = sprintf('sector.%s', $sectorIndex);
            $messages[sprintf(
                '%s.sector_vocabulary.in',
                $sectorForm
            )]
                = trans('validation.vocabulary_is_invalid');
            $messages[sprintf('%s.code.in', $sectorForm)] = trans(
                'validation.sector_code_is_invalid'
            );
            $messages[sprintf('%s.category_code.in', $sectorForm)] = trans(
                'validation.sector_code_is_invalid'
            );
            $messages[sprintf('%s.sdg_goal.in', $sectorForm)] = trans(
                'validation.sector_code_is_invalid'
            );
            $messages[sprintf('%s.sdg_target.in', $sectorForm)] = trans(
                'validation.sector_code_is_invalid'
            );

            if (isset($sector['sector_vocabulary']) && $sector['sector_vocabulary'] === '99') {
                $messages[sprintf('%s.vocabulary_uri.url', $sectorForm)]
                    = trans('validation.url_valid');
            }

            $narrativeMessages = $this->getMessagesForNarrative($sector['narrative'], $sectorForm);

            foreach ($narrativeMessages as $key => $item) {
                $messages[$key] = $item;
            }
        }

        return $messages;
    }

    /**
     * get rules for transaction provider organization.
     *
     * @param  array  $formFields
     *
     * @return array
     */
    public function getWarningForProviderOrg(array $formFields): array
    {
        $rules = [];

        foreach ($formFields as $providerOrgIndex => $providerOrg) {
            $providerOrgForm = sprintf('provider_organization.%s', $providerOrgIndex);
            $rules[sprintf('%s.%s', $providerOrgForm, 'provider_activity_id')] = 'exclude_operators';
            $narrativeRules = $this->getWarningForNarrative($providerOrg['narrative'], $providerOrgForm);

            foreach ($narrativeRules as $key => $item) {
                $rules[$key] = $item;
            }
        }

        return $rules;
    }

    /**
     * get critical rules for transaction provider organization.
     *
     * @param  array  $formFields
     *
     * @return array
     */
    public function getErrorsForProviderOrg(array $formFields): array
    {
        $rules = [];

        foreach ($formFields as $providerOrgIndex => $providerOrg) {
            $providerOrgForm = sprintf('provider_organization.%s', $providerOrgIndex);
            $rules[sprintf('%s.%s', $providerOrgForm, 'type')] = 'nullable|in:' . implode(
                ',',
                array_keys(
                    $this->getCodeListForRequestFiles(
                        'OrganizationType',
                        'Organization',
                        false
                    )
                )
            );
            $narrativeRules = $this->getErrorsForNarrative($providerOrg['narrative'], $providerOrgForm);

            foreach ($narrativeRules as $key => $item) {
                $rules[$key] = $item;
            }
        }

        return $rules;
    }

    /**
     * get error messages for transaction provider organization.
     *
     * @param  array  $formFields
     *
     * @return array
     */
    public function getMessagesForProviderOrg(array $formFields): array
    {
        $messages = [];

        foreach ($formFields as $providerOrgIndex => $providerOrg) {
            $providerOrgForm = sprintf('provider_organization.%s', $providerOrgIndex);
            $message[sprintf(
                '%s.%s.exclude_operators',
                $providerOrgForm,
                'provider_activity_id'
            )]
                = 'The transaction provider-activity-id field is not valid.';
            $messages[sprintf('%s.%s.in', $providerOrgForm, 'type')]
                = trans('validation.organisation_type_is_invalid');
            $narrativeMessages = $this->getMessagesForNarrative($providerOrg['narrative'], $providerOrgForm);

            foreach ($narrativeMessages as $key => $item) {
                $messages[$key] = $item;
            }
        }

        return $messages;
    }

    /**
     * get rules for transaction receiver organization.
     *
     * @param  array  $formFields
     *
     * @return array
     */
    public function getWarningForReceiverOrg(array $formFields): array
    {
        $rules = [];

        foreach ($formFields as $receiverOrgIndex => $receiverOrg) {
            $receiverOrgForm = sprintf('receiver_organization.%s', $receiverOrgIndex);
            $rules[sprintf('%s.%s', $receiverOrgForm, 'receiver_activity_id')] = 'exclude_operators';
            $narrativeRules = $this->getWarningForNarrative($receiverOrg['narrative'], $receiverOrgForm);

            foreach ($narrativeRules as $key => $item) {
                $rules[$key] = $item;
            }
        }

        return $rules;
    }

    /**
     * get critical rules for transaction receiver organization.
     *
     * @param  array  $formFields
     *
     * @return array
     */
    public function getErrorsForReceiverOrg(array $formFields): array
    {
        $rules = [];

        foreach ($formFields as $receiverOrgIndex => $receiverOrg) {
            $receiverOrgForm = sprintf('receiver_organization.%s', $receiverOrgIndex);
            $rules[sprintf('%s.%s', $receiverOrgForm, 'type')] = 'nullable|in:' . implode(
                ',',
                array_keys(
                    $this->getCodeListForRequestFiles(
                        'OrganizationType',
                        'Organization',
                        false
                    )
                )
            );
            $narrativeRules = $this->getErrorsForNarrative($receiverOrg['narrative'], $receiverOrgForm);

            foreach ($narrativeRules as $key => $item) {
                $rules[$key] = $item;
            }
        }

        return $rules;
    }

    /**
     * get error messages for transaction receiver organization.
     *
     * @param  array  $formFields
     *
     * @return array
     */
    public function getMessagesForReceiverOrg(array $formFields): array
    {
        $messages = [];

        foreach ($formFields as $receiverOrgIndex => $receiverOrg) {
            $receiverOrgForm = sprintf('receiver_organization.%s', $receiverOrgIndex);
            $message[sprintf(
                '%s.%s.exclude_operators',
                $receiverOrgForm,
                'receiver_activity_id'
            )]
                = trans('validation.activity_transactions.receiver_org.exclude_operators');
            $messages[sprintf('%s.%s.in', $receiverOrgForm, 'type')]
                = trans('validation.organisation_type_is_invalid');
            $narrativeMessages = $this->getMessagesForNarrative($receiverOrg['narrative'], $receiverOrgForm);

            foreach ($narrativeMessages as $key => $item) {
                $messages[$key] = $item;
            }
        }

        return $messages;
    }

    /**
     * returns rules for recipient region.
     *
     * @param  array  $formFields
     * @param  bool  $fileUpload
     * @param  array  $activityRecipientRegions
     *
     * @return array
     * @throws BindingResolutionException
     */
    public function getWarningForRecipientRegion(
        array $formFields,
        bool $fileUpload,
        array $activityRecipientRegions
    ): array {
        if (empty($formFields)) {
            return [];
        }

        $rules = [];
        $activityService = app()->make(ActivityService::class);

        if ($fileUpload && $activityService->isElementEmpty($activityRecipientRegions, 'recipientRegionFields')) {
            $this->getRecipientRegionOrCountryRuleFromFileUpload($rules, 'recipient_country');
        }

        foreach ($formFields as $recipientRegionIndex => $recipientRegion) {
            $recipientRegionForm = sprintf('recipient_region.%s', $recipientRegionIndex);
            $narrativeRules = $this->getWarningForNarrative($recipientRegion['narrative'], $recipientRegionForm);

            foreach ($narrativeRules as $key => $item) {
                $rules[$key] = $item;
            }
        }

        if (!$fileUpload) {
            $this->getRecipientRegionOrCountryRule($rules, 'recipient_region');
        }

        return $rules;
    }

    /**
     * returns critical rules for recipient region.
     *
     * @param  array  $formFields
     * @param  bool  $fileUpload
     * @param  array  $activityRecipientRegions
     *
     * @return array
     * @throws BindingResolutionException
     */
    public function getErrorsForRecipientRegion(
        array $formFields,
        bool $fileUpload,
        array $activityRecipientRegions
    ): array {
        if (empty($formFields)) {
            return [];
        }

        $rules = [];

        foreach ($formFields as $recipientRegionIndex => $recipientRegion) {
            $recipientRegionForm = sprintf('recipient_region.%s', $recipientRegionIndex);
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
            $rules[sprintf('%s.vocabulary_uri', $recipientRegionForm)] = 'nullable|url';

            if (Arr::get($recipientRegion, 'region_vocabulary', 1) === '99') {
                $rules[sprintf('%s.vocabulary_uri', $recipientRegionForm)] = 'nullable|url';
            }
            $narrativeRules = $this->getErrorsForNarrative($recipientRegion['narrative'], $recipientRegionForm);

            foreach ($narrativeRules as $key => $item) {
                $rules[$key] = $item;
            }
        }

        return $rules;
    }

    /**
     * returns messaged for recipient region.
     *
     * @param  array  $formFields
     *
     * @return array
     */
    public function getMessagesForRecipientRegion(array $formFields): array
    {
        $messages = [
            'recipient_region.country_or_region' => trans(
                'validation.activity_transactions.country_or_region'
            ),
        ];

        if (!$formFields) {
            return $messages;
        }

        foreach ($formFields as $recipientRegionIndex => $recipientRegion) {
            $recipientRegionForm = sprintf('recipient_region.%s', $recipientRegionIndex);
            $messages[sprintf(
                '%s.region_vocabulary.in',
                $recipientRegionForm
            )]
                = trans('validation.vocabulary_is_invalid');
            $messages[sprintf(
                '%s.region_code.in',
                $recipientRegionForm
            )]
                = trans('validation.region_code_is_invalid');
            $messages[sprintf(
                '%s.vocabulary_uri.url',
                $recipientRegionForm
            )]
                = trans('validation.url_valid');

            if (Arr::get($recipientRegion, 'region_vocabulary', 1) === '99') {
                $messages[sprintf('%s.vocabulary_uri.url', $recipientRegionForm)]
                    = trans('validation.url_valid');
            }

            $narrativeMessages = $this->getMessagesForNarrative($recipientRegion['narrative'], $recipientRegionForm);

            foreach ($narrativeMessages as $key => $item) {
                $messages[$key] = $item;
            }
        }

        return $messages;
    }

    /**
     * returns rules for recipient country.
     *
     * @param  array  $formFields
     * @param  bool  $fileUpload
     * @param  array  $activityRecipientCountries
     *
     * @return array
     * @throws BindingResolutionException
     */
    public function getWarningForRecipientCountry(
        array $formFields,
        bool $fileUpload,
        array $activityRecipientCountries
    ): array {
        if (empty($formFields)) {
            return [];
        }

        $rules = [];
        $activityService = app()->make(ActivityService::class);

        if ($fileUpload && $activityService->isElementEmpty($activityRecipientCountries, 'recipientCountryFields')) {
            $this->getRecipientRegionOrCountryRuleFromFileUpload($rules, 'recipient_country');
        }

        foreach ($formFields as $recipientCountryIndex => $recipientCountry) {
            $recipientCountryForm = sprintf('recipient_country.%s', $recipientCountryIndex);
            $narrativeRules = $this->getWarningForNarrative(
                Arr::get($recipientCountry, 'narrative', []),
                $recipientCountryForm
            );

            foreach ($narrativeRules as $key => $item) {
                $rules[$key] = $item;
            }
        }

        if (!$fileUpload) {
            $this->getRecipientRegionOrCountryRule($rules, 'recipient_country');
        }

        return $rules;
    }

    /**
     * returns critical rules for recipient country.
     *
     * @param  array  $formFields
     * @param  bool  $fileUpload
     * @param  array  $activityRecipientCountries
     *
     * @return array
     * @throws BindingResolutionException
     */
    public function getErrorsForRecipientCountry(
        array $formFields,
        bool $fileUpload,
        array $activityRecipientCountries
    ): array {
        if (empty($formFields)) {
            return [];
        }

        $rules = [];

        foreach ($formFields as $recipientCountryIndex => $recipientCountry) {
            $recipientCountryForm = sprintf('recipient_country.%s', $recipientCountryIndex);
            $rules[sprintf('%s.country_code', $recipientCountryForm)] = 'nullable|in:' . implode(
                ',',
                array_keys(
                    $this->getCodeListForRequestFiles('Country', 'Activity', false)
                )
            );
            $narrativeRules = $this->getErrorsForNarrative(
                Arr::get($recipientCountry, 'narrative', []),
                $recipientCountryForm
            );

            foreach ($narrativeRules as $key => $item) {
                $rules[$key] = $item;
            }
        }

        return $rules;
    }

    /**
     * returns messages for recipient country.
     *
     * @param  array  $formFields
     *
     * @return array
     */
    public function getMessagesForRecipientCountry(array $formFields): array
    {
        $messages = [
            'recipient_country.country_or_region' => trans('validation.activity_transactions.country_or_region'),
        ];

        if (!$formFields) {
            return $messages;
        }

        foreach ($formFields as $recipientCountryIndex => $recipientCountry) {
            $recipientCountryForm = sprintf('recipient_country.%s', $recipientCountryIndex);
            $messages[sprintf(
                '%s.country_code.in',
                $recipientCountryForm
            )]
                = trans('validation.country_code');
            $narrativeMessages = $this->getMessagesForNarrative(
                Arr::get($recipientCountry, 'narrative', []),
                $recipientCountryForm
            );

            foreach ($narrativeMessages as $key => $item) {
                $messages[$key] = $item;
            }
        }

        return $messages;
    }

    /**
     * Checks if recipient region or country is required or not.
     *
     * @param $rules
     * @param $attribute
     * @return array
     * @throws BindingResolutionException
     */
    public function getRecipientRegionOrCountryRule(&$rules, $attribute): array
    {
        $transactionService = app()->make(TransactionService::class);
        $params = $this->route()->parameters();
        $transactionId = isset($params['transactionId']) ? (int) $params['transactionId'] : null;
        $hasDefinedInTransaction = $transactionService->hasRecipientRegionOrCountryDefinedInTransaction(
            $params['id'],
            $transactionId
        );

        if ($hasDefinedInTransaction && (is_variable_null($this->all()['recipient_region']) && is_variable_null(
            $this->all()['recipient_country']
        ))) {
            $rules[$attribute] = 'country_or_region';
        } elseif (!is_variable_null($this->all()['recipient_region']) && !is_variable_null(
            $this->all()['recipient_country']
        )) {
            $rules[$attribute] = 'country_or_region';
        } elseif ($hasDefinedInTransaction && !$this->hasCountryOrRegionDefinedInActivity && is_variable_null(
            $this->all()['recipient_region']
        ) && is_variable_null($this->all()['recipient_country'])) {
            $rules[$attribute] = 'country_or_region';
        }

        return $rules;
    }

    /**
     * Checks if country or region rule required or not from file upload.
     *
     * @param $rules
     * @param $attribute
     * @return array
     */
    public function getRecipientRegionOrCountryRuleFromFileUpload(&$rules, $attribute): array
    {
        $hasRegionOrCountryDefinedInTransaction = Session::get('has_region_or_country_defined_in_transaction');
        $recipientRegion = $this->transactionFormField['recipient_region'];
        $recipientCountry = $this->transactionFormField['recipient_country'];

        if (!is_array_value_empty($recipientRegion) && !is_array_value_empty($recipientCountry)) {
            $rules[$attribute] = 'country_or_region';
        } elseif (!is_array_value_empty($recipientRegion) || !is_array_value_empty($recipientCountry)) {
            Session::put('has_region_or_country_defined_in_transaction', true);
        } elseif ($hasRegionOrCountryDefinedInTransaction && (is_array_value_empty(
            $recipientRegion
        ) && is_array_value_empty($recipientCountry))) {
            $rules[$attribute] = 'country_or_region';
        }

        return $rules;
    }

    /**
     * @param  array  $formFields
     * @param  bool  $fileUpload
     * @param  array  $activityRecipientRegions
     * @return array|array[]
     * @throws BindingResolutionException
     */
    public function getCriticalErrorForRecipientRegion(
        array $formFields,
        bool $fileUpload,
        array $activityRecipientRegions
    ): array {
        if (empty($formFields)) {
            return [];
        }

        $rules = [];
        $activityService = app()->make(ActivityService::class);

        if (!$fileUpload) {
            $params = $this->route()->parameters();

            if ($activityService->hasRecipientRegionDefinedInActivity(
                $params['id']
            ) || $activityService->hasRecipientCountryDefinedInActivity($params['id'])) {
                $this->hasCountryOrRegionDefinedInActivity = true;
            }

            if (!$activityService->isElementEmpty($formFields, 'recipientRegionFields')
                && ($activityService->hasRecipientRegionDefinedInActivity(
                    $params['id']
                ) || $activityService->hasRecipientCountryDefinedInActivity($params['id']))) {
                Validator::extend('already_in_activity', function () {
                    return false;
                });

                return ['recipient_region' => [new AlreadyInActivity()]];
            }
        } else {
            if (!$activityService->isElementEmpty(
                $formFields,
                'recipientRegionFields'
            ) && !$activityService->isElementEmpty($activityRecipientRegions, 'recipientRegionFields')) {
                return ['recipient_region' => [new AlreadyInActivity()]];
            }
        }

        return $rules;
    }

    /**
     * @param  array  $formFields
     * @param  bool  $fileUpload
     * @param  array  $activityRecipientCountries
     * @return array|array[]
     * @throws BindingResolutionException
     */
    public function getCriticalErrorForRecipientCountry(
        array $formFields,
        bool $fileUpload,
        array $activityRecipientCountries
    ): array {
        if (empty($formFields)) {
            return [];
        }

        $rules = [];
        $activityService = app()->make(ActivityService::class);

        if (!$fileUpload) {
            $params = $this->route()->parameters();

            if (!$activityService->isElementEmpty($formFields, 'recipientCountryFields')
                && ($activityService->hasRecipientCountryDefinedInActivity(
                    $params['id']
                ) || $activityService->hasRecipientRegionDefinedInActivity($params['id']))) {
                Validator::extend('already_in_activity', function () {
                    return false;
                });

                return ['recipient_country' => [new AlreadyInActivity()]];
            }
        } else {
            if (!$activityService->isElementEmpty(
                $formFields,
                'recipientCountryFields'
            ) && !$activityService->isElementEmpty($activityRecipientCountries, 'recipientCountryFields')) {
                return ['recipient_country' => [new AlreadyInActivity()]];
            }
        }

        return $rules;
    }

    /**
     * @param  array  $formFields
     * @param  bool  $fileUpload
     * @param  array  $activitySectors
     * @param  array  $transactions
     * @return array|array[]
     * @throws BindingResolutionException
     */
    public function getCriticalErrorForSector(
        array $formFields,
        bool $fileUpload,
        array $activitySectors,
        array $transactions = []
    ): array {
        if (empty($formFields)) {
            return [];
        }

        $rules = [];
        $activityService = app()->make(ActivityService::class);

        Validator::extend('already_in_activity', function () {
            return false;
        });

        if (!$fileUpload) {
            $params = $this->route()->parameters();

            if (!$activityService->isElementEmpty(
                $formFields,
                'sectorFields'
            ) && $activityService->hasSectorDefinedInActivity($params['id'])) {
                return ['sector' => [new AlreadyInActivity()]];
            }
        } else {
            if (!empty($activitySectors) && !$activityService->isElementEmpty($formFields, 'sectorFields')) {
                return ['sector' => [new AlreadyInActivity()]];
            }
        }

        return $rules;
    }
}
