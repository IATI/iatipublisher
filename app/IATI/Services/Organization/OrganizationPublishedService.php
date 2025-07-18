<?php

declare(strict_types=1);

namespace App\IATI\Services\Organization;

use App\IATI\Repositories\Organization\OrganizationPublishedRepository;
use Illuminate\Database\Eloquent\Model;

/**
 * Class OrganizationPublishedService.
 */
class OrganizationPublishedService
{
    /**
     * OrganizationPublishedService constructor.
     */
    public function __construct(protected OrganizationPublishedRepository $organizationPublishedRepository)
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
        return $this->organizationPublishedRepository->findOrCreate($filename, $organizationId);
    }

    /**
     * Returns activity published data.
     *
     * @param $organizationId
     *
     * @return Model
     */
    public function getOrganizationPublished($organizationId): ?Model
    {
        return $this->organizationPublishedRepository->getOrganizationPublished($organizationId);
    }

    /**
     * Updates organization published table.
     *
     * @param $organization_id
     * @param $status
     *
     * @return void
     */
    public function updateStatus($organization_id, $status): void
    {
        $this->organizationPublishedRepository->update($organization_id, [
            'published_to_registry' => $status ? 1 : 0,
        ]);
    }

    public function upsert(array $data, $uniqueBy): int
    {
        return $this->organizationPublishedRepository->upsert($data, $uniqueBy);
    }

    public function delete(int $id): bool
    {
        return $this->organizationPublishedRepository->delete($id);
    }
}
