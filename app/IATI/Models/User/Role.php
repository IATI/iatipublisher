<?php

declare(strict_types=1);

namespace App\IATI\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Role.
 */
class Role extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'roles';

    /**
     * Many users have role.
     *
     * @return HasMany
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'role_id', 'id');
    }

    /**
     * Returns id of superadmin from roles table.
     *
     * @return int
     */
    public function getSuperAdminId(): int
    {
        return $this->where('role', 'superadmin')->first()->id;
    }

    /**
     * Returns id of iati superadmin from roles table.
     *
     * @return int
     */
    public function getIatiAdminId(): int
    {
        return $this->where('role', 'iati_admin')->first()->id;
    }

    /**
     * Returns id of organization admin from roles table.
     *
     * @return int
     */
    public function getOrganizationAdminId(): int
    {
        return $this->where('role', 'admin')->first()->id;
    }
}
