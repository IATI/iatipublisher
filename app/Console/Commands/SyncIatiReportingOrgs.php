<?php

namespace App\Console\Commands;

use App\IATI\Models\Organization\Organization;
use App\IATI\Services\RegisterYourDataApi\IatiDataSyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SyncIatiReportingOrgs extends Command
{
    protected $signature = 'iati:sync-reporting-orgs';

    protected $description = 'Sync IATI reporting organizations from bulk-data endpoint';

    protected IatiDataSyncService $iatiDataSyncService;

    protected $syncCount = 0;
    protected $skipCount = 0;

    public function __construct(IatiDataSyncService $iatiDataSyncService)
    {
        parent::__construct();
        $this->iatiDataSyncService = $iatiDataSyncService;
    }

    public function handle(): int
    {
        $response = Http::timeout(60)->get(
            'https://bulk-data.iatistandard.org/reporting-orgs'
        );

        if (!$response->successful()) {
            $this->error('Failed to fetch reporting orgs');

            return Command::FAILURE;
        }

        $payload = $response->json();

        if (empty($payload['reporting_orgs'])) {
            $this->warn('No reporting orgs found');

            return Command::SUCCESS;
        }

        foreach ($payload['reporting_orgs'] as $org) {
            try {
                $uuid = data_get($org, 'id');

                $this->syncOrganizationDownstream($uuid, $org);
            } catch (\Throwable $e) {
                $this->error(
                    'Failed syncing ' . data_get($org, 'human_readable_name')
                );
            }
        }

        $this->info('IATI reporting org sync completed');
        $this->info('Synced Orgs:' . $this->syncCount);
        $this->info('Skipped Orgs:' . $this->skipCount);

        return Command::SUCCESS;
    }

    protected function syncOrganizationDownstream(string $uuid, array $data): Organization | null
    {
        $existingOrg = null;

        if ($uuid) {
            $existingOrg = Organization::where('uuid', $uuid)->first();
        }

        if (!$existingOrg && !empty($data['organisation_identifier'])) {
            $existingOrg = Organization::where(
                'identifier',
                $data['organisation_identifier']
            )->first();
        }

        if (!$existingOrg && !empty($data['short_name'])) {
            $existingOrg = Organization::where(
                'publisher_id',
                $data['short_name']
            )->first();
        }

        if (!$existingOrg && !empty($data['human_readable_name'])) {
            $existingOrg = Organization::where(
                'publisher_name',
                $data['human_readable_name']
            )->first();
        }

        if (!$existingOrg) {
            $this->info('Skipped ' . $data['human_readable_name']);
            $this->skipCount++;

            return null;
        }

        $publisherTypeCode = data_get($data, 'organisation_type');

        $name = [
            [
                'narrative' => data_get($data, 'human_readable_name'),
                'language'  => 'en',
            ],
        ];

        $attributes = [
            'identifier'             => $data['organisation_identifier'] ?? '-',
            'uuid'                   => $uuid,
            'publisher_id'           => data_get($data, 'short_name'),
            'publisher_name'         => data_get($data, 'human_readable_name'),
            'publisher_type'         => $publisherTypeCode,
            'name'                   => $name,
            'reporting_org'          => [
                [
                    'ref'                => data_get($data, 'organisation_identifier'),
                    'type'               => $publisherTypeCode,
                    'secondary_reporter' => $this->iatiDataSyncService->mapSecondaryReporter(
                        data_get($data, 'reporting_source_type')
                    ),
                    'narrative'          => $name,
                ],
            ],
            'country'                => $this->iatiDataSyncService->mapCountryCode(
                data_get($data, 'hq_country')
            ),
            'data_license'           => data_get($data, 'default_licence_id'),
        ];

        $existingOrg->fill($attributes);

        if ($existingOrg->isDirty()) {
            $existingOrg->status = 'draft';
            $existingOrg->is_published = $existingOrg->getOriginal('is_published');
            $existingOrg->saveQuietly();

            $this->syncCount++;

            $this->info(
                'Synced: ' . data_get($existingOrg, 'human_readable_name')
            );
        }

        return $existingOrg;
    }
}
