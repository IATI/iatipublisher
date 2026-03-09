<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\IATI\Services\RegisterYourDataApi\IatiDataSyncService;
use App\IATI\Services\RegisterYourDataApi\ReportingOrgApiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncOrganizationsFromRegistry extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:sync-organizations-registry {--token=} {--pageSize=500}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetches all organizations from the Registry and syncs them to the Publisher.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        protected ReportingOrgApiService $reportingOrgApiService,
        protected IatiDataSyncService $iatiDataSyncService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $accessToken = $this->option('token');

        if (!$accessToken) {
            $this->error('The --token option is required.');

            return Command::FAILURE;
        }

        $this->info('Starting organization synchronization from Registry...');

        $page = 1;
        $pageSize = (int) $this->option('pageSize');
        $totalSynced = 0;
        $totalSkipped = 0;

        do {
            $this->info("Fetching page {$page}...");

            try {
                $response = $this->reportingOrgApiService->getDiscoverableReportingOrgsPaginated([
                    'page' => $page,
                    'page_size' => $pageSize,
                ], $accessToken);

                if (!isset($response['data']) || empty($response['data'])) {
                    $this->warn("No data found on page {$page}. Completing sync.");
                    break;
                }

                $organizations = $response['data'];
                $pagination = $response['pagination'] ?? null;

                foreach ($organizations as $org) {
                    $uuid = $org['id'] ?? null;
                    $metadata = $org['metadata'] ?? [];

                    if (!$uuid) {
                        $this->error('Found organization without UUID, skipping.');
                        continue;
                    }

                    try {
                        $synced = $this->iatiDataSyncService->syncOrganizationFromDiscovery($uuid, $metadata);
                        if ($synced) {
                            $totalSynced++;
                        } else {
                            $totalSkipped++;
                        }
                    } catch (\Exception $e) {
                        $this->error("Failed to sync organization {$uuid}: " . $e->getMessage());
                        Log::error("Organization sync failed for UUID {$uuid}", [
                            'exception' => $e->getMessage(),
                            'metadata' => $metadata,
                        ]);
                    }
                }

                $totalPages = $pagination['total_pages'] ?? 1;
                $this->info("Page {$page} processed. Synced: {$totalSynced}, Skipped: {$totalSkipped}");

                $page++;
            } catch (\Exception $e) {
                $this->error('An error occurred during API fetch: ' . $e->getMessage());
                Log::error('Registry organization sync command failed', [
                    'exception' => $e->getMessage(),
                    'page' => $page,
                ]);

                return Command::FAILURE;
            }
        } while ($page <= $totalPages);

        $this->info('Synchronization complete.');
        $this->info("Total organizations synced/updated: {$totalSynced}");
        $this->info("Total organizations skipped (not found in Publisher): {$totalSkipped}");

        return Command::SUCCESS;
    }
}
