<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class StartMaintenance extends Command
{
    protected $signature = 'command:downtime {timerange : The time range (e.g. "1-5th December")}';

    protected $description = 'Sets custom timerange and puts app in maintenance mode';

    public function handle()
    {
        $timerange = $this->argument('timerange');

        // 1. Save the dynamic string to Cache
        Cache::forever('maintenance_timerange', $timerange);

        $this->info("Timerange set to: {$timerange}");

        // 2. Run standard artisan down, instructing it to render YOUR view
        // The view will pick up the Cache value immediately and bake it into the static HTML
        $this->call('down', [
            '--render' => 'errors.maintenance',
            '--secret' => 'bypass', // Optional: Allows access via your-site.com/bypass
        ]);

        $this->info('Application is now in maintenance mode.');

        return 0;
    }
}
