<?php

namespace App\Console\Commands;

use App\IATI\Models\Activity\Activity;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class CopyActivityIdentifier extends Command
{
    protected $signature = 'command:CopyActivityIdentifier';
    protected $description = 'Copy iati_identifier->activity_identifier to the activity_identifier column';

    public function handle(): void
    {
        DB::beginTransaction();
        try {
            Activity::whereRaw("(iati_identifier->>'activity_identifier') IS NOT NULL")
                ->whereNull('activity_identifier')
                ->chunkById(100, function ($activities) {
                    foreach ($activities as $activity) {
                        $identifier = Arr::get($activity->iati_identifier, 'activity_identifier');
                        $activity->timestamps = false;
                        $activity->updateQuietly(['activity_identifier' => $identifier]);
                    }
                });

            DB::commit();
            $this->info('Successfully updated activity_identifier column.');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error($e->getMessage());
            exit(1);
        }
    }
}
