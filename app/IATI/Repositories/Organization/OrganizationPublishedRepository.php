<?php

declare(strict_types=1);

namespace App\IATI\Repositories\Organization;

use App\IATI\Models\Organization\OrganizationPublished;
use App\IATI\Repositories\Repository;
use Illuminate\Database\Eloquent\Model;

/**
 * Class OrganizationPublishedRepository.
 */
class OrganizationPublishedRepository extends Repository
{
    /**
     * Return Organization model.
     *
     * @return string
     */
    public function getModel(): string
    {
        return OrganizationPublished::class;
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
            'published_to_registry' => true,
        ]);

        $published->touch();

        return $published;
    }

    /**
     * Returns activity published data.
     *
     * @param $organization_id
     *
     * @return Model
     */
    public function getOrganizationPublished($organization_id): ?Model
    {
        return $this->model->where('organization_id', $organization_id)->first();
    }
}
