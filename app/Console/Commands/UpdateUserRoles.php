<?php

namespace App\Console\Commands;

use App\IATI\Models\User\User;
use Illuminate\Console\Command;

/**
 * class UpdateUserRoles.
 *
 * this command is just for one time use for
 * https://github.com/IATI/iatipublisher/issues/1894
 */
class UpdateUserRoles extends Command
{
    protected $signature = 'users:update-roles';
    protected $description = 'Update users role_id from 4 to 3';

    public function handle()
    {
        $count = User::where('role_id', 4)->update(['role_id' => 3]);

        $this->info("Updated {$count} users from role_id 4 to 3.");

        return 0;
    }
}
