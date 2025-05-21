<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\IATI\Models\Download\DownloadStatus;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * @class ClearStalledDownloads
 *
 * Clears download records stuck in "processing" state for more than 15 minutes.
 * Prevents user lockout due to stalled downloads.
 *
 * Similar to: https://github.com/IATI/iatipublisher/issues/1761.
 *
 * See also: https://github.com/IATI/iatipublisher/issues/1813
 */
class ClearStalledDownloads extends Command
{
    protected $signature = 'command:ClearStalledDownloads';

    protected $description = 'Delete download status records older than specified time (15 minutes)';

    public function handle(): int
    {
        DB::beginTransaction();

        try {
            $possiblyStuckDownloads = DownloadStatus::where('status', 'processing')->get();
            $deletableIds = [];

            $now = Carbon::now();

            foreach ($possiblyStuckDownloads as $downloadStatus) {
                $minuteDiff = $now->diffInMinutes($downloadStatus->created_at);

                if ($minuteDiff > 15) {
                    $deletableIds[] = $downloadStatus->id;
                }
            }

            $deletedCount = DownloadStatus::whereIn('id', $deletableIds)->delete();

            $this->info("Successfully deleted {$deletedCount} stuck download status record(s).");

            DB::commit();

            return 1;
        } catch (\Exception $e) {
            DB::rollback();

            $this->error('Error: ' . $e->getMessage());

            return 0;
        }
    }
}
