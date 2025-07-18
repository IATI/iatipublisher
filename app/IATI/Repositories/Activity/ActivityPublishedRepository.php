<?php

declare(strict_types=1);

namespace App\IATI\Repositories\Activity;

use App\IATI\Models\Activity\ActivityPublished;
use App\IATI\Repositories\Repository;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ActivityPublishedRepository.
 */
class ActivityPublishedRepository extends Repository
{
    /**
     * Returns activity published model.
     *
     * @return string
     */
    public function getModel(): string
    {
        return ActivityPublished::class;
    }

    /**
     * Creates new record or updates existing record in activity published table.
     *
     * @param $filename
     * @param $organizationId
     *
     * @return Model
     */
    public function findOrCreate($filename, $organizationId): Model
    {
        $published = $this->model->firstOrNew([
            'filename' => $filename,
            'organization_id' => $organizationId,
        ]);

        $published->touch();

        return $published;
    }

    /**
     * Updates activity published table.
     *
     * @param $activityPublished
     * @return void
     */
    public function updateStatus($activityPublished): void
    {
        $activityPublished->published_to_registry = 1;
        $activityPublished->save();
    }

    /**
     * @param $activityPublished
     * @param $filesize
     *
     * @return void
     */
    public function updateFilesize($activityPublished, $filesize): void
    {
        $activityPublished->filesize = $filesize;
        $activityPublished->save();
    }

    /**
     * @param int|string $orgId
     *
     * @return int|float
     */
    public function getPublisherFileSize(int|string $orgId): float|int
    {
        return $this->model->where('organization_id', $orgId)?->first()->filesize ?? 0;
    }

    /**
     * @param int $organizationId
     *
     * @return ActivityPublished|null
     */
    public function findByOrganizationId(int $organizationId): ?ActivityPublished
    {
        return $this->model->where('organization_id', $organizationId)->first();
    }
}
