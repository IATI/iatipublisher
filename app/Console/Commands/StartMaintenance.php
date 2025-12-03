<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class StartMaintenance extends Command
{
    protected $signature = 'command:downtime
                            {--type= : Type of maintenance}
                            {--time= : The time range (e.g. "1-5th December")}';

    protected $description = 'Sets custom timerange and puts app in maintenance mode';

    public function handle()
    {
        $type = $this->option('type');
        $timerange = $this->option('time');

        if (!in_array($type, ['maintenance', 'downtime'])) {
            $this->error('Invalid type. Must be "maintenance" or "downtime".');

            return 1;
        }

        // 1. Save the dynamic string to Cache
        Cache::forever('maintenance_timerange', $timerange);
        Cache::forever('maintenance_type', $type);

        $this->info("Timerange set to: {$timerange}");
        $this->info("Type set to: {$timerange}");

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
