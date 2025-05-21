<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\IATI\Models\Import\ImportStatus;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * @class ClearStalledImports
 *
 * Clears import status records stuck in "processing" state for more than 15 minutes.
 * Helps prevent user lockout due to stalled import processes.
 *
 * See issue: https://github.com/IATI/iatipublisher/issues/1761
 */
class ClearStalledImports extends Command
{
    protected $signature = 'command:ClearStalledImports';

    protected $description = 'Delete import status records older than specified time (15 minutes)';

    public function handle(): int
    {
        DB::beginTransaction();

        try {
            $possiblyStuckImports = ImportStatus::where('status', 'processing')->get();
            $deletableIds = [];

            $now = Carbon::now();

            foreach ($possiblyStuckImports as $importStatus) {
                $orgId = $importStatus->organization_id;

                $hourDiff = $now->diffInMinutes($importStatus->created_at);

                if ($hourDiff > 15) {
                    $deletableIds[] = $importStatus->id;
                }
            }

            $deletedCount = ImportStatus::whereIn('id', $deletableIds)->delete();

            $this->info("Successfully deleted {$deletedCount} stuck import status record(s).");

            DB::commit();

            return Command::SUCCESS;
        } catch (\Exception $e) {
            DB::rollBack();

            $this->error('Error: ' . $e->getMessage());

            return Command::FAILURE;
        }
    }
}
