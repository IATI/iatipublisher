<?php

declare(strict_types=1);

namespace App\IATI\Repositories\User;

use App\IATI\Models\User\Role;
use App\IATI\Repositories\Repository;
use Illuminate\Support\Collection;

/**
 * Class RoleRepository.
 */
class RoleRepository extends Repository
{
    /**
     * Returns user model.
     *
     * @return string
     */
    public function getModel(): string
    {
        return Role::class;
    }

    /**
     * Return role and corresponding id.
     *
     * @return Collection
     */
    public function pluckRoles(): Collection
    {
        return $this->model->pluck('role', 'id');
    }

    /**
     * Returns id of superadmin from roles table.
     *
     * @return int
     */
    public function getSuperAdminId(): int
    {
        return $this->model->where('role', 'superadmin')->first()->id;
    }

    /**
     * Returns id of iati superadmin from roles table.
     *
     * @return int
     */
    public function getIatiAdminId(): int
    {
        return $this->model->where('role', 'iati_admin')->first()->id;
    }

    /**
     * Returns id of organization admin from roles table.
     *
     * @return int
     */
    public function getOrganizationAdminId(): int
    {
        return $this->model->where('role', 'admin')->first()->id;
    }
}
