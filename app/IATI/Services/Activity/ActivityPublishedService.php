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
     * @param           $activityPublished
     * @param float|int $mergedFilesize
     *
     * @return void
     */
    public function updateFilesize($activityPublished, float|int $mergedFilesize): void
    {
        $this->activityPublishedRepository->updateFilesize($activityPublished, $mergedFilesize);
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
}
