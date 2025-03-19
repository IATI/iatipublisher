<?php

declare(strict_types=1);

namespace App\IATI\Services\Activity;

use App\IATI\Elements\Builder\TransactionElementFormCreator;
use App\IATI\Repositories\Activity\TransactionRepository;
use App\IATI\Traits\DataSanitizeTrait;
use App\IATI\Traits\XmlBaseElement;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Kris\LaravelFormBuilder\Form;

/**
 * Class TransactionService.
 */
class TransactionService
{
    use XmlBaseElement, DataSanitizeTrait;

    /**
     * @var TransactionRepository
     */
    protected TransactionRepository $transactionRepository;

    /**
     * @var TransactionElementFormCreator
     */
    protected TransactionElementFormCreator $transactionElementFormCreator;

    /**
     * TransactionService constructor.
     *
     * @param TransactionRepository $transactionRepository
     * @param TransactionElementFormCreator $transactionElementFormCreator
     */
    public function __construct(
        TransactionRepository $transactionRepository,
        TransactionElementFormCreator $transactionElementFormCreator
    ) {
        $this->transactionRepository = $transactionRepository;
        $this->transactionElementFormCreator = $transactionElementFormCreator;
    }

    /**
     * @param int   $activityId
     * @param int   $page
     * @param array $queryParams
     *
     * @return LengthAwarePaginator|Collection
     */
    public function getPaginatedTransactionAndStats(int $activityId, int $page, array $queryParams): LengthAwarePaginator|Collection
    {
        return $this->transactionRepository->getPaginatedTransaction($activityId, $queryParams, $page);
    }

    /**
     * Return specific transaction.
     *
     * @param $id
     *
     * @return object|null
     */
    public function getTransaction($id): ?object
    {
        return $this->transactionRepository->find($id);
    }

    /**
     * Checks if specific transactions exists for specific activity.
     *
     * @param int $activityId
     * @param int $id
     *
     * @return bool
     */
    public function activityTransactionExists(int $activityId, int $id): bool
    {
        return $this->getActivityTransaction($activityId, $id) !== null;
    }

    /**
     * Returns specific transaction of specific activity.
     *
     * @param int $activityId
     * @param int $id
     *
     * @return mixed
     */
    public function getActivityTransaction(int $activityId, int $id): mixed
    {
        return $this->transactionRepository->getActivityTransaction($activityId, $id);
    }

    /**
     * Create a new Transaction.
     *
     * @param array $transactionData
     *
     * @return Model
     */
    public function create(array $transactionData): Model
    {
        $transactionData['transaction'] = $this->sanitizeData($transactionData['transaction']);
        $transactionData['deprecation_status_map'] = refreshTransactionDeprecationStatusMap($transactionData['transaction']);

        return $this->transactionRepository->store($transactionData);
    }

    /**
     * Update Activity Transaction.
     *
     * @param       $id
     * @param       $transactionData
     *
     * @return bool
     */
    public function update($id, $transactionData): bool
    {
        $transactionData['transaction'] = $this->sanitizeData($transactionData);
        $transactionData['deprecation_status_map'] = refreshTransactionDeprecationStatusMap($transactionData);

        return $this->transactionRepository->update($id, $transactionData);
    }

    /**
     * get the references of all s.
     *
     * @param $activityId
     *
     * @return array
     */
    public function getTransactionReferences($activityId): array
    {
        return $this->transactionRepository->getTransactionReferences($activityId);
    }

    /**
     * Returns all transactions of a particular activity.
     *
     * @param $activityId
     *
     * @return object|null
     */
    public function getActivityTransactions($activityId): ?object
    {
        return $this->transactionRepository->findAllBy('activity_id', $activityId);
    }

    /**
     * Generates transaction create form.
     *
     * @param $activityId
     * @param $element
     *
     * @return Form
     */
    public function createFormGenerator($activityId, $element, $activityDefaultFieldValues): Form
    {
        $this->transactionElementFormCreator->url = route('admin.activity.transaction.store', $activityId);

        return $this->transactionElementFormCreator->editForm([], $element, 'POST', '/activity/' . $activityId, overRideDefaultFieldValue: $activityDefaultFieldValues);
    }

    /**
     * Generates transaction edit form.
     *
     * @param $transactionId
     * @param $activityId
     * @param $element
     *
     * @return Form
     *
     * @throws \JsonException
     */
    public function editFormGenerator($transactionId, $activityId, $element): Form
    {
        $activityTransaction = $this->getTransaction($transactionId);
        $deprecationStatusMap = Arr::get($activityTransaction->toArray(), 'deprecation_status_map', []);

        $this->transactionElementFormCreator->url = route(
            'admin.activity.transaction.update',
            [$activityId, $transactionId]
        );

        return $this->transactionElementFormCreator->editForm(
            $activityTransaction->transaction,
            $element,
            method:'PUT',
            parent_url: '/activity/' . $activityId,
            deprecationStatusMap: $deprecationStatusMap
        );
    }

    /**
     * Returns data in required xml array format.
     *
     * @param $transactions
     *
     * @return array
     */
    public function getXmlData($transactions): array
    {
        $transactionData = [];

        foreach ($transactions as $totalTransaction) {
            $transaction = $totalTransaction->transaction;
            $sector = [];

            foreach (Arr::get($transaction, 'sector', []) as $sectorData) {
                if ($sectorData) {
                    $vocabulary = Arr::get($sectorData, 'sector_vocabulary', null);
                    $sectorValue = $this->getSectorValue($vocabulary, $sectorData);

                    $sector[] = [
                        '@attributes' => [
                            'vocabulary'     => $vocabulary,
                            'vocabulary-uri' => Arr::get($sectorData, 'vocabulary_uri', null),
                            'code'           => $sectorValue,
                        ],
                        'narrative'   => $this->buildNarrative(Arr::get($sectorData, 'narrative', [])),
                    ];
                }
            }

            $recipientCountry = [];

            if (Arr::get($transaction, 'recipient_country', null)) {
                $recipientCountry = [
                    '@attributes' => [
                        'code' => Arr::get($transaction, 'recipient_country.0.country_code', null),
                    ],
                    'narrative'   => $this->buildNarrative(Arr::get($transaction, 'recipient_country.0.narrative', [])),
                ];
            }

            $recipientRegion = [];

            if (Arr::get($transaction, 'recipient_region', null)) {
                $recipientRegion = [
                    '@attributes' => [
                        'code'           => Arr::get(
                            $transaction,
                            'recipient_region.0.region_vocabulary',
                            null
                        ) == 1 ? Arr::get(
                            $transaction,
                            'recipient_region.0.region_code',
                            null
                        ) : Arr::get($transaction, 'recipient_region.0.custom_code', null),
                        'vocabulary'     => Arr::get($transaction, 'recipient_region.0.region_vocabulary', null),
                        'vocabulary-uri' => Arr::get($transaction, 'recipient_region.0.vocabulary_uri', null),
                    ],
                    'narrative'   => $this->buildNarrative(Arr::get($transaction, 'recipient_region.0.narrative', [])),
                ];
            }

            $aidType = [];

            if (Arr::get($transaction, 'aid_type', null)) {
                foreach (Arr::get($transaction, 'aid_type', []) as $transactionAidType) {
                    $vocabulary = Arr::get($transactionAidType, 'aid_type_vocabulary', null);
                    $code = $this->getAidTypeCode($vocabulary, $transactionAidType);

                    $aidType[] = [
                        '@attributes' => [
                            'code'       => $code,
                            'vocabulary' => $vocabulary,
                        ],
                    ];
                }
            }

            $transactionData[] = $this->getTransactionData(
                $transaction,
                $sector,
                $recipientCountry,
                $recipientRegion,
                $aidType
            );
        }

        return $transactionData;
    }

    /**
     * Returns sector value based on vocabulary.
     *
     * @param $vocabulary
     * @param $sectorData
     *
     * @return string|null
     */
    public function getSectorValue($vocabulary, $sectorData): ?string
    {
        switch ($vocabulary) {
            case '1':
                return Arr::get($sectorData, 'code', null);
            case '2':
                return Arr::get($sectorData, 'category_code', null);
            case '7':
                return Arr::get($sectorData, 'sdg_goal', null);
            case '8':
                return Arr::get($sectorData, 'sdg_target', null);
            default:
                return Arr::get($sectorData, 'text', null);
        }
    }

    /**
     * Returns aid type code according to vocabulary.
     *
     * @param $vocabulary
     * @param $aidType
     *
     * @return string|null
     */
    public function getAidTypeCode($vocabulary, $aidType): ?string
    {
        switch ($vocabulary) {
            case '2':
                return Arr::get($aidType, 'earmarking_category', null);
            case '3':
                return Arr::get($aidType, 'earmarking_modality', null);
            case '4':
                return Arr::get($aidType, 'cash_and_voucher_modalities', null);
            default:
                return Arr::get($aidType, 'aid_type_code', null);
        }
    }

    /**
     * Returns array required for creating xml.
     *
     * @param $transaction
     * @param $sector
     * @param $recipientCountry
     * @param $recipientRegion
     * @param $aidType
     *
     * @return array
     */
    public function getTransactionData($transaction, $sector, $recipientCountry, $recipientRegion, $aidType): array
    {
        return [
            '@attributes'          => [
                'ref'          => Arr::get($transaction, 'reference', null),
                'humanitarian' => Arr::get($transaction, 'humanitarian', null),
            ],
            'transaction-type'     => [
                '@attributes' => [
                    'code' => Arr::get($transaction, 'transaction_type.0.transaction_type_code', null),
                ],
            ],
            'transaction-date'     => [
                '@attributes' => [
                    'iso-date' => Arr::get($transaction, 'transaction_date.0.date', null),
                ],
            ],
            'value'                => [
                '@attributes' => [
                    'currency'   => Arr::get($transaction, 'value.0.currency', null),
                    'value-date' => Arr::get($transaction, 'value.0.date', null),
                ],
                '@value'      => Arr::get($transaction, 'value.0.amount', null),
            ],
            'description'          => [
                'narrative' => $this->buildNarrative(Arr::get($transaction, 'description.0.narrative', [])),
            ],
            'provider-org'         => [
                '@attributes' => [
                    'ref'                  => Arr::get(
                        $transaction,
                        'provider_organization.0.organization_identifier_code',
                        null
                    ),
                    'provider-activity-id' => Arr::get(
                        $transaction,
                        'provider_organization.0.provider_activity_id',
                        null
                    ),
                    'type'                 => Arr::get($transaction, 'provider_organization.0.type', null),
                ],
                'narrative'   => $this->buildNarrative(
                    Arr::get($transaction, 'provider_organization.0.narrative', [])
                ),
            ],
            'receiver-org'         => [
                '@attributes' => [
                    'ref'                  => Arr::get(
                        $transaction,
                        'receiver_organization.0.organization_identifier_code',
                        null
                    ),
                    'receiver-activity-id' => Arr::get(
                        $transaction,
                        'receiver_organization.0.receiver_activity_id',
                        null
                    ),
                    'type'                 => Arr::get($transaction, 'receiver_organization.0.type', null),
                ],
                'narrative'   => $this->buildNarrative(
                    Arr::get($transaction, 'receiver_organization.0.narrative', [])
                ),
            ],
            'disbursement-channel' => [
                '@attributes' => [
                    'code' => Arr::get($transaction, 'disbursement_channel.0.disbursement_channel_code', null),
                ],
            ],
            'sector'               => $sector,
            'recipient-country'    => $recipientCountry,
            'recipient-region'     => $recipientRegion,
            'flow-type'            => [
                '@attributes' => [
                    'code' => Arr::get($transaction, 'flow_type.0.flow_type', null),
                ],
            ],
            'finance-type'         => [
                '@attributes' => [
                    'code' => Arr::get($transaction, 'finance_type.0.finance_type', null),
                ],
            ],
            'aid-type'             => $aidType,
            'tied-status'          => [
                '@attributes' => [
                    'code' => Arr::get($transaction, 'tied_status.0.tied_status_code', null),
                ],
            ],
        ];
    }

    /**
     * Deletes specific transaction.
     *
     * @param $id
     *
     * @return bool
     */
    public function deleteTransaction($id): bool
    {
        return $this->transactionRepository->delete($id);
    }

    /**
     * Checks if sector defined in one of the activity transaction.
     *
     * @param $activityId
     * @param $transactionId
     * @return bool
     */
    public function hasSectorDefinedInTransaction($activityId, $transactionId = null): bool
    {
        $transactionData = $this->getActivityTransactions($activityId);

        if (!empty($transactionData)) {
            foreach ($transactionData as $transactionDatum) {
                if ($transactionDatum->id === $transactionId) {
                    continue;
                }

                if (
                    isset($transactionDatum->transaction['sector'])
                    && !is_array_value_empty($transactionDatum->transaction['sector'])
                ) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     *  Checks if recipient region or country defined in transaction.
     *
     * @param [type] $activityId
     * @param null $transactionId
     * @return bool
     */
    public function hasRecipientRegionOrCountryDefinedInTransaction($activityId, $transactionId = null): bool
    {
        $hasDefined = false;
        $transactions = $this->getActivityTransactions($activityId);

        foreach ($transactions as $transaction) {
            if ($transaction->id === $transactionId) {
                continue;
            }

            $recipientRegion = $transaction->transaction['recipient_region'];
            $recipientCountry = $transaction->transaction['recipient_country'];

            if (!is_array_value_empty($recipientCountry) || !is_array_value_empty($recipientRegion)) {
                $hasDefined = true;
                break;
            }
        }

        return $hasDefined;
    }

    /**
     * Checks element is defined in transaction.
     *
     * @param $activity
     * @param $elementName
     * @return bool
     */
    public function checkIfTransactionHasElementDefined($activity, $elementName): bool
    {
        $hasDefined = false;
        $transactionData = $activity->transactions()->get()->toArray();

        if (!empty($transactionData)) {
            foreach ($transactionData as $transactionDatum) {
                if (
                    isset($transactionDatum['transaction'][$elementName])
                    && !is_array_value_empty($transactionDatum['transaction'][$elementName])
                ) {
                    $hasDefined = true;
                }
            }
        }

        return $hasDefined;
    }

    /**
     * Inserts multiple transactions.
     *
     * @param $transactions
     *
     * @return bool
     */
    public function insert($transactions): bool
    {
        return $this->transactionRepository->insert($transactions);
    }

    /**
     * append freeze and info_text in sector, recipient region or country if present in activity level.
     *
     * @param $activity
     * @param $transactionId
     *
     * @return array
     *
     * @throws \JsonException
     */
    public function getManipulatedTransactionElementSchema($activity, $transactionId = null): array
    {
        $element = getElementSchema('transactions');

        if (!empty($activity->sector)) {
            $element['sub_elements']['sector']['freeze'] = true;
            $element['sub_elements']['sector']['info_text'] = trans('common/common.transaction_sector_form_info_text');
        }

        if (!empty($activity->recipient_country) || !empty($activity->recipient_region)) {
            $element['sub_elements']['recipient_region']['freeze'] = true;
            $element['sub_elements']['recipient_region']['info_text'] = trans('common/common.transaction_recipient_region_form_info_text');
            $element['sub_elements']['recipient_country']['freeze'] = true;
            $element['sub_elements']['recipient_country']['info_text'] = trans('common/common.transaction_recipient_region_form_info_text');
        }

        if ($transactionId) {
            $this->appendInfoTextForRecipientRegionAndCountryInTransaction($activity, $element, $transactionId);
            $this->appendInfoTextForSectorInTransaction($activity, $element, $transactionId);
        }

        return $element;
    }

    /**
     * appends warning info text in recipient region or country in transaction level.
     *
     * @param $activity
     * @param $element
     * @param $transactionId
     *
     * @return void
     */
    public function appendInfoTextForRecipientRegionAndCountryInTransaction($activity, &$element, $transactionId): void
    {
        $hasDefinedInTransaction = $this->hasRecipientRegionOrCountryDefinedInTransaction($activity->id);

        $emptyRecipientRegionOrCountryTransaction = $activity->transactions->filter(function ($item) {
            $recipientRegion = $item->transaction['recipient_region'];
            $recipientCountry = $item->transaction['recipient_country'];

            return is_array_value_empty($recipientRegion) && is_array_value_empty($recipientCountry);
        });

        $emptyRecipientRegionOrCountryTransactionCount = count($emptyRecipientRegionOrCountryTransaction);

        if ($emptyRecipientRegionOrCountryTransactionCount && $hasDefinedInTransaction) {
            $recipientRegionOrCountryMessage = 'Recipient Region or Recipient Country is declared at transaction level.';

            if (in_array((int) $transactionId, $emptyRecipientRegionOrCountryTransaction->pluck('id')->toArray(), true)) {
                $message = $recipientRegionOrCountryMessage;
            } else {
                $messagePart = $emptyRecipientRegionOrCountryTransactionCount > 1 ? "are other $emptyRecipientRegionOrCountryTransactionCount transactions"
                    : "is $emptyRecipientRegionOrCountryTransactionCount transaction";
                $message = $recipientRegionOrCountryMessage . " There $messagePart without Recipient Region or Recipient Country in this activity.";
            }
            $element['sub_elements']['recipient_region']['warning_info_text'] = $message;
            $element['sub_elements']['recipient_country']['warning_info_text'] = $message;
        }
    }

    /**
     * Adds warning info text in sector in transaction level.
     *
     * @param $activity
     * @param $element
     * @param $transactionId
     *
     * @return void
     */
    public function appendInfoTextForSectorInTransaction($activity, &$element, $transactionId): void
    {
        $hasSectorDefinedInTransaction = $this->hasSectorDefinedInTransaction($activity->id);
        $emptySectorTransaction = $activity->transactions->filter(function ($item) {
            $sector = $item->transaction['sector'];

            return is_array_value_empty($sector);
        });
        $emptySectorTransactionCount = count($emptySectorTransaction);

        if ($emptySectorTransactionCount && $hasSectorDefinedInTransaction) {
            $sectorMessage = 'Sector is declared at transaction level. You must add sector in all transactions.';

            if (in_array((int) $transactionId, $emptySectorTransaction->pluck('id')->toArray(), true)) {
                $message = $sectorMessage;
            } else {
                $messagePart = $emptySectorTransactionCount > 1 ? "are other $emptySectorTransactionCount transactions"
                    : "is $emptySectorTransactionCount transaction";
                $message = $sectorMessage . " There $messagePart without Sector in this activity.";
            }
            $element['sub_elements']['sector']['warning_info_text'] = $message;
        }
    }

    public function getDeprecationStatusMap($id = '', $key = '')
    {
        if ($id) {
            try {
                $transaction = $this->transactionRepository->find($id);
            } catch (Exception) {
                return [];
            }

            if ($key === '') {
                return $transaction->deprecation_status_map;
            }

            return Arr::get($transaction->deprecation_status_map, $key, []);
        }

        return [];
    }

    /**
     * @param int $activityId
     *
     * @return array
     */
    public function getTransactionCountStats(int $activityId): array
    {
        return $this->transactionRepository->getTransactionCountStats($activityId);
    }

    /** @param array $transactionIds
     *
     * @return bool
     */
    public function bulkDeleteTransactions(array $transactionIds): bool
    {
        return $this->transactionRepository->bulkDeleteTransactions($transactionIds);
    }
}
