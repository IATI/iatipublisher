<?php

declare(strict_types=1);

namespace App\IATI\Repositories\Activity;

use App\IATI\Repositories\Repository;
use App\ValidationStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

/**
 * Class ValidationStatusRepository.
 */
class ValidationStatusRepository extends Repository
{
    /**
     * Returns ValidationStatus Model.
     *
     * @return string
     */
    public function getModel(): string
    {
        return ValidationStatus::class;
    }

    /**
     * Returns Activity validation status.
     *
     * @param int $activityId
     * @param int $userId
     *
     * @return array
     */
    public function getValidationStatus(int $activityId, int $userId): array
    {
        $status = $this->model->where('activity_id', $activityId)->where('user_id', $userId)->first();

        return $status ? $status->toArray() : [];
    }

    /**
     * Delete validation progress status.
     *
     * @param int $activityId
     * @param int $userId
     *
     * @return bool
     */
    public function deleteValidationStatus(int $activityId, int $userId)
    {
        return (bool) $this->model->where('activity_id', $activityId)->where('user_id', $userId)->delete();
    }

    /**
     * Updates the validation status.
     *
     * @param int $activityId
     * @param int $userId
     * @param string $status
     * @param array $response
     *
     * @return int
     */
    public function updateValidationStatus(int $activityId, int $userId, string $status = 'processing', array $response = []): int
    {
        return $this->model->where('user_id', $userId)
            ->where('activity_id', $activityId)
            ->update([
                'user_id'       =>  $userId,
                'activity_id'   =>  $activityId,
                'status'        =>  $status,
                'response'      =>  !empty($response) ? json_encode($response) : null,
            ]);
    }

    /**
     * Stores the validation status.
     *
     * @param int $activityId
     * @param int $userId
     * @param string $status
     * @param array $response
     *
     * @return Builder|Model
     */
    public function storeValidationStatus(int $activityId, int $userId, string $status = 'processing', array $response = []): Model|Builder
    {
        return $this->model->updateOrCreate([
            'user_id'       =>  $userId,
            'activity_id'   =>  $activityId,
            'status'        =>  $status,
            'response'      =>  !empty($response) ? json_encode($response) : null,
        ]);
    }

    /**
     * Returns activity status.
     *
     * @param array $activityIds
     *
     * @return array
     */
    public function getActivitiesValidationStatus(array $activityIds): array
    {
        $allCompleted = true;

        $response = [];
        $response['status'] = 'processing';
        $response['total'] = count($activityIds);
        $response['complete_count'] = 0;
        $response['failed_count'] = 0;
        $response['activities'] = [];
        $response['activity_error_stats'] = [];

        $validatorStatuses = $this->model->with('activity')->whereIn('activity_id', $activityIds)->get();

        if ($validatorStatuses->count()) {
            $result = [];
            foreach ($validatorStatuses as $validatorStatus) {
                $act = $validatorStatus->activity->title;

                $result[$validatorStatus->activity_id] = [
                    'title'  => Arr::get($act, '0.narrative', ''),
                    'status' => $validatorStatus->status,
                ];

                if ($validatorStatus->status !== 'completed') {
                    $allCompleted = false;
                }

                if ($validatorStatus->status === 'max_merge_size_exception') {
                    $response['failed_count']++;
                    $result[$validatorStatus->activity_id]['is_valid'] = false;
                    $response['error_type'] = 'max_merge_size_exception';
                }

                if ($validatorStatus->status === 'failed') {
                    $response['failed_count']++;
                    $result[$validatorStatus->activity_id]['is_valid'] = false;
                }

                if ($validatorStatus->status === 'completed') {
                    $response['complete_count']++;
                    $activityId = $validatorStatus->activity_id;
                    $responsePropertyOfValidatorResponse = Arr::get(json_decode($validatorStatus->response, true), 'response');
                    $result[$activityId]['is_valid'] = Arr::get($responsePropertyOfValidatorResponse, 'valid');
                    $responseSummary = Arr::get($responsePropertyOfValidatorResponse, 'summary', []);
                    $result[$activityId]['top_level_error'] = $this->getHighestSeverityErrorFromResponse($responseSummary);
                }
            }

            if ($allCompleted) {
                $response['status'] = 'completed';
            } elseif ($response['failed_count'] + $response['complete_count'] === $response['total']) {
                $response['status'] = 'completed';
            }

            $response['activities'] = $result;

            return $response;
        }

        return $response;
    }

    /**
     * Get Activity Validation Responses.
     *
     * @param array $activityIds
     *
     * @return array
     */
    public function getActivitiesValidationResponse(array $activityIds): array
    {
        return $this->model->whereIn('activity_id', $activityIds)
            ->get()
            ->pluck('response')
            ->toArray();
    }

    /**
     * Count ongoing process.
     *
     * @return array
     */
    public function checkOngoingValidationStatus(): array
    {
        return $this->model->with('activity')->where('user_id', auth()->user()->id)
                            ->whereIn('status', ['processing', 'completed'])
                            ->get()
                            ->pluck('activity.default_title_narrative', 'activity_id')
                            ->toArray();
    }

    /**
     * Count ongoing process.
     *
     * @return int
     */
    public function checkPreviousValidationStatusPending(): int
    {
        return $this->model->where('user_id', auth()->user()->id)
                           ->count();
    }

    /**
     * Deletes validation responses.
     *
     * @return int
     */
    public function deleteValidationResponses(): int
    {
        return $this->model->where('user_id', auth()->user()->id)
                           ->delete();
    }

    /**
     * Change source: https://github.com/iati/iatipublisher/issues/1423.
     * Bug: Activity validation status response missing some activities.
     *
     * Basically what was happening is:
     * - Previously RegistryValidatorJob::dispatch($activity, $user); would be dispatched for each activity.
     * - This would mean that jobs would be lined one after another.
     * - Since RegistryValidatorJob, creates the row in validation_status table,
     *      N rows would not be created unless all jobs finished going through the queue.
     *
     * Let ,
     *  T be total number of activities we need to validate
     *  Q be total number of jobs finished the queue.
     *  T and Q will never be equal until all jobs have completed.
     * Meaning method: getActivitiesValidationStatus() would be processing wrong number of validation_status via:
     * $validatorStatuses = $this->model->with('activity')->whereIn('activity_id', $activityIds)->get();
     *
     * This method will make it so that T and Q are equal by padding the rows in 'validation_status' table.
     *
     * @param array $activityIds
     * @param int $userId
     * @return void
     */
    public function insertInitialValidatorResponseDataForProperResponse(array $activityIds, int $userId): void
    {
        $now = now();
        $initialStatus = 'processing';
        $insertableValidationStatus = array_map(function ($activityId) use ($userId, $now, $initialStatus) {
            return [
                'user_id'     => $userId,
                'activity_id' => $activityId,
                'created_at'  => $now,
                'response'    => null,
                'status'      => $initialStatus,
            ];
        }, $activityIds);

        $this->model->insert($insertableValidationStatus);
    }

    /**
     * @param array $responseSummary
     *
     * @return string|null
     */
    private function getHighestSeverityErrorFromResponse(array $responseSummary): ?string
    {
        $severityLevels = collect(['critical', 'error', 'warning', 'advisory']);

        return $severityLevels->first(function ($level) use ($responseSummary) {
            return Arr::get($responseSummary, $level, 0) > 0;
        });
    }

    /**
     * Returns array of activity_id of the activities that do not contain critical issues or errors.
     * Activities with either of these validation levels are blocked from being published.
     *
     * @param array $activityIds
     *
     * @return array
     */
    public function getActivitiesWithNoBlockingErrors(array $activityIds): array
    {
        return $this->model->whereIn('activity_id', $activityIds)
            ->where('status', 'completed')
            ->whereRaw("(response->'response'->'summary'->>'critical')::integer = 0")
            ->whereRaw("(response->'response'->'summary'->>'error')::integer = 0")
            ->pluck('activity_id')->toArray();
    }
}
