<?php

declare(strict_types=1);

namespace App\IATI\Services\Activity;

use App\IATI\Repositories\Activity\ActivityPublishedRepository;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ActivityPublishedService.
 */
class ActivityPublishedService
{
    /**
     * ActivityPublishedService constructor.
     */
    public function __construct(protected ActivityPublishedRepository $activityPublishedRepository)
    {
    }

    /**
     * Returns new record or existing record in activity published table.
     *
     * @param $filename
     * @param $organizationId
     *
     * @return Model
     */
    public function findOrCreate($filename, $organizationId): Model
    {
        return $this->activityPublishedRepository->findOrCreate($filename, $organizationId);
    }

    /**
     * Updates existing record in activity published table.
     *
     * @param $activityPublished
     * @param $publishedActivities
     *
     * @return bool
     */
    public function update(int $id, $data): bool
    {
        return $this->activityPublishedRepository->update($id, $data);
    }

    /**
     * Returns activity published data.
     *
     * @param $organization_id
     *
     * @return object|null
     */
    public function getActivityPublished($organization_id): object|null
    {
        return $this->activityPublishedRepository->findByOrganizationId($organization_id);
    }

    /**
     * Updates activity published table.
     *
     * @param $activityPublished
     *
     * @return void
     */
    public function updateStatus($activityPublished): void
    {
        $this->activityPublishedRepository->updateStatus($activityPublished);
    }

    /**
     * @param $orgId
     *
     * @return int|float
     */
    public function getPublisherFileSize(int|string $orgId): int|float
    {
        return $this->activityPublishedRepository->getPublisherFileSize($orgId);
    }

    /**
     * Stores published file details for a bulk operation.
     */
    public function trackActivityPublished(int $organizationId, string $mergedFileName, array $publishedActivityFileNames, $filesize, $uuid): bool
    {
        $activityPublished = $this->findOrCreate($mergedFileName, $organizationId);
        $currentPublishedActivities = (array) $activityPublished->published_activities;

        $newPublishedActivities = array_merge($currentPublishedActivities, $publishedActivityFileNames);

        return $this->update(
            $activityPublished->id,
            [
                'published_activities'  => array_values(array_unique($newPublishedActivities)),
                'filesize'              => $filesize,
                'published_to_registry' => true,
                'dataset_uuid'          => $uuid,
            ]
        );
    }

    /**
     * @param int $activityId
     * @return bool
     */
    public function deleteActivity(int $activityId): bool
    {
        return $this->activityPublishedRepository->delete($activityId);
    }
}
