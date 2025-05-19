<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * @class ClearOlderAuditLogs
 *
 * This command is scheduled to run on the first day of every month.
 *
 * As part of our efforts to improve GDPR compliance and reduce personal data retention,
 * this command deletes audit log entries that are older than one full calendar month.
 *
 * While we do not track mouse clicks or fine-grained user behavior, we do log actions like
 * login, save, update, delete, download, and export. These logs are not used for profiling
 * and do not track individual user journeys.
 *
 * IP addresses and browser user-agent strings may be considered personally identifiable
 * under some interpretations of GDPR. To support data minimization, we are limiting
 * retention of such logs to 1 month.
 *
 * See issue: https://github.com/iati/iatipublisher/issues/1456
 */
class ClearOlderAuditLogs extends Command
{
    protected $signature = 'command:ClearOlderAuditLogs';

    protected $description = 'Clear audit logs older than one full calendar month for GDPR data minimization.';

    public function handle(): void
    {
        DB::beginTransaction();

        try {
            $cutoffDate = Carbon::now()->startOfMonth()->subMonth();

            $deleted = DB::table('audits')
                ->where('created_at', '<', $cutoffDate)
                ->delete();

            logger()->info("GDPR: Cleared {$deleted} audit logs older than {$cutoffDate->toDateString()}.");

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            logger()->error('Error clearing audit logs for GDPR compliance: ' . $e->getMessage());
        }
    }
}
