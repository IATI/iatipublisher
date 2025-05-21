<?php

declare(strict_types=1);

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('command:UpdateJsonFiles')->dailyAt('00:00');
        $schedule->command('command:FetchOrganisationRegistrationAgency')->dailyAt('00:05');
        $schedule->command('command:SetAppDataJsonCache')->dailyAt('00:10');
        $schedule->command('command:ClearOlderAuditLogs')->monthlyOn(1, '00:00');
        $schedule->command('command:ClearStalledImports')->everyFifteenMinutes();
        $schedule->command('command:ClearStalledDownloads')->everyFifteenMinutes();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
