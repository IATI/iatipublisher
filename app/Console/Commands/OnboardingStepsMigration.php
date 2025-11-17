<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class OnboardingStepsMigration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:migrate-onboarding-steps';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Removes the "Publishing Settings" step and re-indexes steps_status array.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $table = 'organization_onboardings';
        $timestamp = now()->format('Ymd_His');
        $backupPath = "backups/onboarding_migration_backup_{$timestamp}.json";

        $this->info('Starting Onboarding Steps Migration...');

        $this->line("1. Backing up data from {$table}...");
        try {
            $records = DB::table($table)->get([
                'id',
                'org_id',
                'completed_onboarding',
                'steps_status',
                'dont_show_again',
                'created_at',
                'updated_at',
            ]);

            Storage::put($backupPath, $records->toJson(JSON_PRETTY_PRINT));
            $this->info("=====Backup complete. File saved to: storage/app/{$backupPath}=====");
        } catch (\Exception $e) {
            $this->error('=====Backup failed: ' . $e->getMessage() . '=====');

            return Command::FAILURE;
        }

        $this->line("\n2. Applying modifications to {$table}...");

        $totalModified = 0;

        DB::table($table)->chunkById(100, function ($onboardings) use (&$totalModified, $table) {
            foreach ($onboardings as $record) {
                $originalSteps = json_decode($record->steps_status, true);

                if (empty($originalSteps)) {
                    continue;
                }

                $filteredSteps = array_values(array_filter($originalSteps, function ($step) {
                    return !isset($step['title']) || $step['title'] !== 'Publishing Settings';
                }));

                $reIndexedSteps = [];
                $stepIndex = 1;
                $allComplete = true;

                foreach ($filteredSteps as $step) {
                    $step['step'] = $stepIndex++;
                    $reIndexedSteps[] = $step;

                    if (!$step['complete']) {
                        $allComplete = false;
                    }
                }

                if (empty($reIndexedSteps)) {
                    $allComplete = false;
                }

                $newStepsJson = json_encode($reIndexedSteps);

                if ($newStepsJson !== $record->steps_status) {
                    DB::table($table)
                        ->where('id', $record->id)
                        ->update([
                            'steps_status' => $newStepsJson,
                            'completed_onboarding' => $allComplete,
                            'updated_at' => now(),
                        ]);
                    $totalModified++;
                }
            }
        });

        $this->info("=====Migration complete! Successfully modified {$totalModified} records.=====");

        return Command::SUCCESS;
    }
}
