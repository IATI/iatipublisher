<?php

namespace Database\Seeders;

use App\IATI\Models\User\Role;
use Illuminate\Database\Seeder;

/**
 * Class RoleTableSeeder.
 */
class RoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        /** Create or update superadmin role */
        /** @var array $superAdminRole */
        $superAdminRole = ['role' => 'superadmin'];
        Role::updateOrCreate($superAdminRole, $superAdminRole);

        /** Create or update iati_admin role */
        /** @var array $iati_admin */
        $iatiAdminRole = ['role' => 'iati_admin'];
        Role::updateOrCreate($iatiAdminRole, $iatiAdminRole);

        /** Create or update admin role */
        /** @var array $adminRole */
        $adminRole = ['role' => 'admin'];
        Role::updateOrCreate($adminRole, $adminRole);
    }
}
