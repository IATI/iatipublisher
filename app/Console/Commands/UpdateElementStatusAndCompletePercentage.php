<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\IATI\Models\Activity\Activity;
use App\IATI\Services\ElementCompleteService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/** @class UpdateElementStatusAndCompletePercentage */
class UpdateElementStatusAndCompletePercentage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:UpdateElementStatusAndCompletePercentage';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '
        Update complete_percentage if core_elements are changed.
        Only run this command if public/Data/coreElements.json is changed.
    ';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        DB::beginTransaction();
        try {
            /** @var ElementCompleteService $elementCompleteService */
            $elementCompleteService = app()->make(ElementCompleteService::class);

            $activities = Activity::all();

            foreach ($activities as $activity) {
                $elementStatus = $activity->element_status;
                $completePercentage = $elementCompleteService->calculateCompletePercentage($elementStatus);

                $activity->complete_percentage = $completePercentage;
                $activity->timestamps = false;

                $activity->updateQuietly();
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            logger()->error($e);
        }
    }
}
