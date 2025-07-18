<?php

declare(strict_types=1);

namespace App\IATI\Services\Activity;

use App\IATI\Repositories\Activity\BulkPublishingStatusRepository;
use Illuminate\Support\Arr;

/**
 * Class BulkPublishingStatusService.
 */
class BulkPublishingStatusService
{
    /**
     * BulkPublishingStatusService constructor.
     */
    public function __construct(protected BulkPublishingStatusRepository $bulkPublishingStatusRepository)
    {
    }

    /**
     * Stores bulk publishing activities to publishing status table.
     *
     * @param $activities
     * @param $organizationId
     * @param $uuid
     *
     * @return void
     */
    public function storeProcessingActivities($activities, $organizationId, $uuid): void
    {
        if (count($activities)) {
            foreach ($activities as $activity) {
                $this->bulkPublishingStatusRepository->store([
                   'organization_id' => $organizationId,
                    'activity_id' => $activity->id,
                    'activity_title' => Arr::get($activity->title, '0.narrative', 'Not Available') ?: 'Not Available',
                    'status' => 'created',
                    'job_batch_uuid' => $uuid,
                ]);
            }
        }
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
        return $this->bulkPublishingStatusRepository->updateActivityStatus($activityId, $uuid, $status);
    }

    /**
     * Returns activities that are currently undergoing bulk publishing for an organization.
     *
     * @param $organizationId
     * @param string $uuid
     *
     * @return object|null
     */
    public function getActivityPublishingStatus($organizationId, string $uuid = ''): ?object
    {
        return $this->bulkPublishingStatusRepository->getActivityPublishingStatus($organizationId, $uuid);
    }

    /**
     * Returns job uuid.
     *
     * @param $organizationId
     *
     * @return string|null
     */
    public function getPublishingUuid($organizationId): ?string
    {
        return $this->bulkPublishingStatusRepository->getPublishingUuid($organizationId);
    }

    /**
     * Deletes bulk publishing statuses once all are completed.
     *
     * @param $publishStatuses
     *
     * @return void
     */
    public function deleteStatuses($publishStatuses): void
    {
        if (count($publishStatuses)) {
            foreach ($publishStatuses as $publishStatus) {
                $this->bulkPublishingStatusRepository->delete($publishStatus->id);
            }
        }
    }

    /**
     * Checks if organization has any bulk publishing processes.
     *
     * @param $organizationId
     *
     * @return bool
     */
    public function ongoingBulkPublishing($organizationId): bool
    {
        $this->bulkPublishingStatusRepository->clearExistingStatuses($organizationId);

        $statuses = $this->bulkPublishingStatusRepository->ongoingActivityPublishingStatus($organizationId);

        if (count($statuses)) {
            return true;
        }

        return false;
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
        return $this->bulkPublishingStatusRepository->stopBulkPublishing($organizationId);
    }

    /**
     * In case of job failure , set created and processing to failed.
     *
     * @param $organizationId
     *
     * @return int
     */
    public function failStuckActivities($organizationId): int
    {
        return $this->bulkPublishingStatusRepository->failStuckActivities($organizationId);
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
        return $this->bulkPublishingStatusRepository->deleteBulkPublishingStatus($organizationId);
    }

    public function updateBulkActivityStatus(array $activityIds, string $uuid, string $status): int
    {
        return $this->bulkPublishingStatusRepository->updateBulkStatus($activityIds, $uuid, $status);
    }
}
