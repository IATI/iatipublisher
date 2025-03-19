<?php

namespace App\Http\Requests;

use App\IATI\Models\Activity\Activity;
use App\IATI\Models\Activity\Result;
use App\IATI\Models\Activity\Transaction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Http\FormRequest;

class BulkDeleteResultRequest extends FormRequest
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
            'result_ids'   => 'required|array|min:1',
            'result_ids.*' => 'integer|exists:activity_results,id',
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
            $resultIds = $this->input('result_ids');

            $results = Result::whereIn('id', $resultIds)->get();

            if ($this->resultsBelongToSameActivity($results)) {
                $validator->errors()->add('result_ids', 'All results must belong to the same activity.');
            }

            if ($this->resultsBelongsToActivityInRequest($results, $activityId)) {
                $validator->errors()->add('result_ids', 'Results IDs do not match the specified activity.');
            }

            $activity = Activity::find($activityId);

            if ($this->activityIsPublished($activity)) {
                $validator->errors()->add('result_ids', 'Please unpublish activity before deleting results.');
            }
        });
    }

    /**
     * @param Collection<Transaction> $transactions
     *
     * @return bool
     */
    private function resultsBelongToSameActivity(Collection $transactions): bool
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

    protected function resultsBelongsToActivityInRequest(Collection $results, int|string $activityId): bool
    {
        return $results->isNotEmpty() && $results->first()->activity_id !== (int) $activityId;
    }
}
