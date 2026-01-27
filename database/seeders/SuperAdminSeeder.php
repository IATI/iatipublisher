<?php

namespace Database\Seeders;

use App\IATI\Models\User\Role;
use App\IATI\Models\User\User;
use Illuminate\Database\Seeder;

/**
 * Class UserTableSeeder.
 */
class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        User::firstOrCreate([
            'full_name' => 'superadmin',
            'email'     => 'superadmin@gmail.com',
            'address'   => 'kathmandu',
            'is_active' => true,
            'role_id'   => app(Role::class)->getSuperAdminId(),
        ]);
    }
}
