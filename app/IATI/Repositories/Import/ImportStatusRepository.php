<?php

declare(strict_types=1);

namespace App\IATI\Repositories\Import;

use App\IATI\Models\Import\ImportStatus;
use App\IATI\Repositories\Repository;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ImportStatusRepository.
 */
class ImportStatusRepository extends Repository
{
    /**
     * Returns activity model.
     *
     * @return string
     */
    public function getModel(): string
    {
        return ImportStatus::class;
    }

    /**
     * Returns import status.
     *
     * @param $organizationId
     *
     * @return array
     */
    public function getImportStatus($organizationId): array
    {
        $status = $this->model->where('organization_id', $organizationId)
            ->where('type', 'xls')
            ->latest()
            ->first();

        return $status ? $status->toArray() : [];
    }

    /**
     * Delete import status for organization with $organizationId and $userId.
     *
     * @param $organizationId
     * @param $userId
     *
     * @return bool
     */
    public function deleteImportStatus($organizationId, $userId): bool
    {
        return (bool) $this->model->where('organization_id', $organizationId)->where('user_id', $userId)->delete();
    }

    /**
     * Create import status.
     *
     * @param        $organizationId
     * @param        $userId
     * @param        $fileType
     * @param string $template
     * @param string $status
     *
     * @return Model|null
     */
    public function storeStatus($organizationId, $userId, $fileType, string $template = 'activity', string $status = 'processing'): ?Model
    {
        return $this->model->create([
            'organization_id' => $organizationId,
            'user_id'         => $userId,
            'status'          => $status,
            'type'            => $fileType,
            'template'        => $template,
        ]);
    }

    /**
     * @param int $orgId
     *
     * @return array
     */
    public function getOrganisationImportStatus(int $orgId): array
    {
        $status = $this->model->where('organization_id', '=', $orgId)
            ->where('status', '=', 'processing')
            ->where('template', '=', 'activity')
            ->latest()
            ->first();

        return $status ? $status->toArray() : [];
    }

    /**
     * @param int    $organization_id
     * @param string $type
     *
     * @return int
     */
    public function completeOrganisationImportStatus(int $organization_id, string $type): int
    {
        return $this->model->where('organization_id', '=', $organization_id)
            ->where('type', '=', $type)
            ->where('template', '=', 'activity')
            ->where('status', '=', 'processing')
            ->update(['status' => 'completed']);
    }

    /**
     * @param int    $organization_id
     * @param int    $userId
     * @param string $type
     *
     * @return Model|null
     */
    public function setOrganisationImportStatus(int $organization_id, int $userId, string $type): ?Model
    {
        return $this->storeStatus($organization_id, $userId, $type);
    }

    /**
     * @param int $orgId
     *
     * @return int
     */
    public function deleteOngoingImports(int $orgId): int
    {
        return $this->model->where('organization_id', '=', $orgId)
            ->where('template', '=', 'activity')
            ->where('status', '=', 'processing')
            ->delete();
    }
}
