<?php

declare(strict_types=1);

namespace App\IATI\Services\ImportActivity;

use App\IATI\Repositories\Import\ImportStatusRepository;
use Illuminate\Database\Eloquent\Model;

/**
 * Service class for `import_status` table.
 *
 * @class ImportStatusService
 */
class ImportStatusService
{
    public function __construct(protected ImportStatusRepository $importStatusRepository)
    {
    }

    /**
     * Retrieve import status from db.
     *
     * @param int $orgId
     *
     * @return array
     */
    public function getOrganisationImportStatus(int $orgId): array
    {
        return $this->importStatusRepository->getOrganisationImportStatus($orgId);
    }

    /**
     * Set Import status to completed in db.
     *
     * @param int    $orgId
     * @param string $type
     *
     * @return int
     */
    public function completeOrganisationImportStatus(int $orgId, string $type): int
    {
        return $this->importStatusRepository->completeOrganisationImportStatus($orgId, $type);
    }

    /**
     * Sets Import status in db.
     *
     * @param int    $orgId
     * @param int    $userId
     * @param string $type
     *
     * @return Model|null
     */
    public function setOrganisationImportStatus(int $orgId, int $userId, string $type): ?Model
    {
        return $this->importStatusRepository->setOrganisationImportStatus($orgId, $userId, $type);
    }

    /**
     * Removes import status from db.
     *
     * @param int $orgId
     *
     * @return int
     */
    public function deleteOngoingImports(int $orgId): int
    {
        return $this->importStatusRepository->deleteOngoingImports($orgId);
    }
}
