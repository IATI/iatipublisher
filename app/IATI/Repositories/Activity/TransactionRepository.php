<?php

declare(strict_types=1);

namespace App\IATI\Repositories\Activity;

use App\IATI\Models\Activity\Transaction;
use App\IATI\Repositories\Repository;
use App\IATI\Traits\FillDefaultValuesTrait;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

/**
 * Class TransactionRepository.
 */
class TransactionRepository extends Repository
{
    use FillDefaultValuesTrait;

    /**
     * @return string
     */
    public function getModel(): string
    {
        return Transaction::class;
    }

    /**
     * Returns paginated transactions.
     *
     * @param int   $activityId
     * @param array $queryParams
     * @param int   $page
     *
     * @return LengthAwarePaginator
     */
    public function getPaginatedTransaction(int $activityId, array $queryParams, int $page = 1): LengthAwarePaginator
    {
        $query = $this->model->where('activity_id', $activityId);

        $filterApplied = Arr::get($queryParams, 'filterBy', 'all');
        $orderBy = Arr::get($queryParams, 'orderBy', 'created_at');
        $direction = strtoupper(Arr::get($queryParams, 'direction', 'ASC'));
        $limit = Arr::get($queryParams, 'limit', 10);

        $query = $query->when($filterApplied != 'all', function ($query) use ($filterApplied) {
            if ($filterApplied === 'others') {
                return $query->whereRaw("(transaction->'transaction_type'->0->>'transaction_type_code')::INTEGER NOT IN (1, 2, 3, 4)");
            }

            return $query->whereRaw("(transaction->'transaction_type'->0->>'transaction_type_code') = '$filterApplied'");
        });

        $query = $query->when(true, function ($query) use ($orderBy, $direction) {
            return match ($orderBy) {
                'type'  => $query->orderByRaw("(transaction->'transaction_type'->0->>'transaction_type_code')::INTEGER $direction, created_at DESC"),
                'value' => $query->orderByRaw("(transaction->'value'->0->>'amount')::NUMERIC $direction, created_at DESC"),
                'date'  => $query->orderByRaw("(transaction->'transaction_date'->0->>'date')::DATE $direction, created_at DESC"),
                default => $query->orderBy('created_at', 'desc'),
            };
        });

        return $query->orderBy('id', 'desc')->paginate($limit, ['*'], 'transaction', $page);
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
        return $this->model->where(['activity_id'=>$activityId, 'id'=>$id])->first();
    }

    /**
     * get the references of all transactions.
     *
     * @param $activityId
     *
     * @return array
     */
    public function getTransactionReferences($activityId): array
    {
        $transactions = $this->model->where('activity_id', $activityId)->get();
        $references = [];

        foreach ($transactions as $transactionRow) {
            $references[$transactionRow->transaction['reference']] = $transactionRow->id;
        }

        return $references;
    }

    /**
     * Delete transactions with activity id.
     *
     * @param $activityId
     *
     * @return bool|int
     */
    public function deleteTransaction($activityId): bool|int
    {
        return $this->model->where('activity_id', $activityId)->forceDelete();
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
        return $this->model->insert($transactions);
    }

    /**
     * @param int $activityId
     *
     * @return array
     */
    public function getTransactionCountStats(int $activityId): array
    {
        return $this->model->where('activity_id', $activityId)
            ->selectRaw("
                COUNT(*) as all,
                COUNT(CASE WHEN (transaction->'transaction_type'->0->>'transaction_type_code')::INTEGER = 1 THEN 1 END) as incoming_funds,
                COUNT(CASE WHEN (transaction->'transaction_type'->0->>'transaction_type_code')::INTEGER = 2 THEN 2 END) as outgoing_commitment,
                COUNT(CASE WHEN (transaction->'transaction_type'->0->>'transaction_type_code')::INTEGER = 3 THEN 3 END) as disbursement,
                COUNT(CASE WHEN (transaction->'transaction_type'->0->>'transaction_type_code')::INTEGER = 4 THEN 4 END) as expenditure,
                COUNT(CASE WHEN (transaction->'transaction_type'->0->>'transaction_type_code')::INTEGER NOT IN (1,2,3,4) THEN 1 END) as others
            ")
            ->first()
            ->toArray();
    }

    /**
     * @param array $transactionIds
     *
     * @return bool
     */
    public function bulkDeleteTransactions(array $transactionIds): bool
    {
        return (bool) $this->model->whereIn('id', $transactionIds)->delete();
    }

    public function bulkDeleteTransactionsByActivityIds(array $activityIds): bool
    {
        if (empty($activityIds)) {
            return false;
        }

        return (bool) $this->model->whereIn('activity_id', $activityIds)->delete();
    }

    /**
     * @throws \JsonException
     */
    public function createTransactions(array $allActivityData, array $allActivityIdsMappedToActivityIdentifiers, array $defaultFieldValuesMappedToActivityIdentifier): bool
    {
        $preparedData = $this->prepareAllTransactionDataToUpsert($allActivityData, $allActivityIdsMappedToActivityIdentifiers, $defaultFieldValuesMappedToActivityIdentifier);

        return DB::table('activity_transactions')->insert($preparedData);
    }

    private function prepareAllTransactionDataToUpsert(array $allActivityData, array $allActivityIdsMappedToActivityIdentifiers, array $defaultFieldValuesMappedToActivityIdentifier): array
    {
        $preparedData = [];

        foreach ($allActivityData as $activityIdentifier => $activityData) {
            if (!empty($activityData['transactions'])) {
                $defaultFieldValues = $defaultFieldValuesMappedToActivityIdentifier[$activityIdentifier];

                foreach ($activityData['transactions'] as $index => $transaction) {
                    $activityId = $allActivityIdsMappedToActivityIdentifiers[$activityIdentifier];

                    $transactionData = [
                        'transaction'            => $transaction,
//                        'deprecation_status_map' => refreshTransactionDeprecationStatusMap($transaction),
                    ];

                    $transactionData = $this->populateDefaultFields($transactionData, $defaultFieldValues);

                    $transactionData = [
                        'activity_id'            => $activityId,
                        'transaction'            => json_encode($transactionData['transaction'], JSON_THROW_ON_ERROR),
                        'deprecation_status_map' => json_encode([], JSON_THROW_ON_ERROR),
                    ];

                    $preparedData[] = $transactionData;
                }
            }
        }

        return $preparedData;
    }
}
