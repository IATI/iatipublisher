<?php

declare(strict_types=1);

namespace App\IATI\Repositories\Activity;

use App\IATI\Models\Activity\BulkPublishingStatus;
use App\IATI\Repositories\Repository;

/**
 * Class BulkPublishingStatusRepository.
 */
class BulkPublishingStatusRepository extends Repository
{
    /**
     * Returns activity model.
     *
     * @return string
     */
    public function getModel(): string
    {
        return BulkPublishingStatus::class;
    }

    /**
     * Updates status of activity in publishing status table.
     *
     * @param $activityId
     * @param $uuid
     * @param $status
     *
     * @return bool
     */
    public function updateActivityStatus($activityId, $uuid, $status): bool
    {
        $activityStatus = $this->model->where('activity_id', $activityId)->where('job_batch_uuid', $uuid)->first();

        if ($activityStatus) {
            $activityStatus->status = $status;

            return $activityStatus->save();
        }

        return false;
    }

    /**
     * Returns activities that are currently undergoing bulk publishing for an organization.
     *
     * @param $organizationId
     * @param string|null $uuid
     *
     * @return object|null
     */
    public function getActivityPublishingStatus($organizationId, ?string $uuid = ''): ?object
    {
        return $this->model->where('organization_id', '=', $organizationId)
            ->when(!empty($uuid), function ($query) use ($uuid) {
                return $query->where('job_batch_uuid', '=', $uuid);
            })->get();
    }

    /**
     * Returns activity publishing status.
     *
     * @param $activityId
     * @param bool|string $uuid
     *
     * @return object|null
     */
    public function getSpecificActivityPublishingStatus($activityId, bool|string $uuid = false): ?object
    {
        return $this->model->where('activity_id', '=', $activityId)
            ->when(!empty($uuid), function ($query) use ($uuid) {
                return $query->where('job_batch_uuid', '=', $uuid);
            })->get();
    }

    /**
     * Returns activities that are currently undergoing bulk publishing for an organization.
     *
     * @param $organizationId
     *
     * @return string|null
     */
    public function getPublishingUuid($organizationId): ?string
    {
        $publishingData = $this->model->where('organization_id', '=', $organizationId)
            ->first();

        if ($publishingData) {
            return $publishingData->job_batch_uuid;
        }

        return null;
    }

    /**
     * Returns ongoing bulk publishing processes for an organization.
     *
     * @param $organizationId
     *
     * @return object|null
     */
    public function ongoingActivityPublishingStatus($organizationId): ?object
    {
        return $this->model->where('organization_id', $organizationId)
            ->whereIn('status', ['created', 'processing'])
            ->get();
    }

    /**
     * Stop bulk publishing.
     *
     * @param $organizationId
     *
     * @return array
     */
    public function stopBulkPublishing($organizationId): array
    {
        $deleteableIds = $this->model->where('organization_id', $organizationId)
            ->where('status', '=', 'created')
            ->get()->pluck('activity_id')
            ->toArray();

        $this->model->where('organization_id', $organizationId)
            ->where('status', '=', 'created')
            ->delete();

        return $deleteableIds;
    }

    /**
     * Pre-emptively delete activities with failed or completed status.
     *
     * @param $organizationId
     *
     * @return void
     */
    public function clearExistingStatuses($organizationId): void
    {
        $this->model->where('organization_id', $organizationId)
            ->where('status', '=', 'completed')
            ->orWhere('status', '=', 'failed')
            ->delete();
    }

    /**
     * Update publishing activities that are stuck to failed.
     *
     * @param $organizationId
     *
     * @return int
     */
    public function failStuckActivities($organizationId): int
    {
        return $this->model->where('organization_id', $organizationId)
            ->where('status', '=', 'created')
            ->orWhere('status', '=', 'processing')
            ->update(['status' => 'failed']);
    }

    /**
     * Delete all the bulk publishing status belonging to $organizationId.
     *
     * @param $organizationId
     *
     * @return bool
     */
    public function deleteBulkPublishingStatus($organizationId): bool
    {
        return (bool) $this->model->where('organization_id', $organizationId)->delete();
    }

    public function updateBulkStatus(array $activityIds, string $uuid, string $status): bool|int
    {
        if (empty($activityIds)) {
            return 0;
        }

        return $this->model->whereIn('activity_id', $activityIds)
            ->where('job_batch_uuid', $uuid)
            ->update(['status' => $status, 'updated_at' => now()]);
    }
}
