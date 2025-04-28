<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Helpers\ImportCacheHelper;
use App\IATI\Models\Import\ImportStatus;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * @class ClearImportStatusIfStuck
 */
class ClearImportStatusIfStuck extends Command
{
    protected $signature = 'command:ClearImportStatusIfStuck';

    protected $description = 'Delete import status records older than specified hours';

    public function handle(): int
    {
        try {
            $possiblyStuckImports = ImportStatus::where('status', 'processing')->get();
            $deletableIds = [];

            $now = Carbon::now();

            foreach ($possiblyStuckImports as $importStatus) {
                $orgId = $importStatus->organization_id;

                ImportCacheHelper::clearImportCache($orgId);

                $hourDiff = $now->diffInMinutes($importStatus->created_at) / 60;

                if ($hourDiff > 0.5) {
                    $deletableIds[] = $importStatus->id;
                }
            }

            $deletedCount = ImportStatus::whereIn('id', $deletableIds)->delete();

            $this->info("Successfully deleted {$deletedCount} stuck import status record(s).");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());

            return Command::FAILURE;
        }
    }
}
