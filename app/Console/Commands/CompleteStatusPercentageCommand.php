<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\IATI\Repositories\Activity\ActivityRepository;
use App\IATI\Services\ElementCompleteService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/*
 * Class CompleteStatusPercentageCommand.
 */
class CompleteStatusPercentageCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:complete_status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks complete status for all activities and updates element_status, complete_percentage columns';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        try {
            DB::beginTransaction();
            $activityRepo = app()->make(ActivityRepository::class);
            $elementCompleteService = app()->make(ElementCompleteService::class);
            $activities = $activityRepo->all();

            foreach ($activities as $activity) {
                $elementCompleteService->refreshElementStatus($activity);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error($e);
        }
    }
}
