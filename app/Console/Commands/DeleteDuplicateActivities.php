<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DeleteDuplicateActivities extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:DeleteDuplicateActivities';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete duplicate activities, keeping only the latest updated activity for each org_id + activity_identifier pair.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->info('Deleting duplicate activities...');

        // Step 1: Find org_id and activity_identifier pairs with duplicates
        $duplicates = DB::table('activities')
            ->select('org_id', 'activity_identifier')
            ->groupBy('org_id', 'activity_identifier')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        // Step 2: For each duplicate group, keep the latest and delete the rest
        foreach ($duplicates as $duplicateGroup) {
            $this->info("Processing org_id: {$duplicateGroup->org_id}, activity_identifier: {$duplicateGroup->activity_identifier}");

            // Get all activities with the same org_id and activity_identifier
            $activities = DB::table('activities')
                ->where('org_id', $duplicateGroup->org_id)
                ->where('activity_identifier', $duplicateGroup->activity_identifier)
                ->orderBy('updated_at', 'desc') // Keep the latest one
                ->get();

            // Skip the first (latest) and delete the rest
            $activities->slice(1)->each(function ($duplicate) {
                DB::table('activities')->where('id', $duplicate->id)->delete();
                $this->info("Deleted activity with ID: {$duplicate->id}");
            });
        }

        $this->info('Duplicate activities deleted successfully!');
    }
}
