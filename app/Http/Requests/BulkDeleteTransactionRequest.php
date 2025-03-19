<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\IATI\Models\Activity\Activity;
use App\IATI\Models\Activity\Transaction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Http\FormRequest;

class BulkDeleteTransactionRequest extends FormRequest
{
    /**
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return string[]
     */
    public function rules(): array
    {
        return [
            'transaction_ids'   => 'required|array|min:1',
            'transaction_ids.*' => 'integer|exists:activity_transactions,id',
        ];
    }

    /**
     * @param $validator
     *
     * @return void
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $activityId = $this->route('id');
            $transactionIds = $this->input('transaction_ids');

            $transactions = Transaction::whereIn('id', $transactionIds)->get();

            if ($this->transactionsBelongToSameActivity($transactions)) {
                $validator->errors()->add('transaction_ids', 'All transactions must belong to the same activity.');
            }

            if ($this->transactionBelongsToActivityInRequest($transactions, $activityId)) {
                $validator->errors()->add('transaction_ids', 'Transaction IDs do not match the specified activity.');
            }

            $activity = Activity::find($activityId);

            if ($this->activityIsPublished($activity)) {
                $validator->errors()->add('transaction_ids', 'Please unpublish activity before deleting transactions.');
            }
        });
    }

    /**
     * @param Collection<Transaction> $transactions
     *
     * @return bool
     */
    private function transactionsBelongToSameActivity(Collection $transactions): bool
    {
        return $transactions->pluck('activity_id')->unique()->count() > 1;
    }

    /**
     * Check if activity is published.
     *
     * @param Activity $activity
     *
     * @return bool
     */
    private function activityIsPublished(Activity $activity): bool
    {
        return $activity && $activity->linked_to_iati;
    }

    protected function transactionBelongsToActivityInRequest(Collection $transactions, int|string $activityId): bool
    {
        return $transactions->isNotEmpty() && $transactions->first()->activity_id !== (int) $activityId;
    }
}
