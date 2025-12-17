<?php

namespace App\Console\Commands;

use App\IATI\Models\Activity\ActivityPublished;
use App\IATI\Models\Organization\OrganizationPublished;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SyncDatasetUuidFromCsv extends Command
{
    protected $signature = 'datasets:sync-uuids
                            {file : Path to datasets.csv}';

    protected $description = 'Update activity_published / organization_published dataset_uuid values from datasets.csv';

    public function handle(): int
    {
        $path = $this->argument('file');

        if (!is_file($path)) {
            $this->error("File not found: {$path}");

            return self::FAILURE;
        }

        if (($handle = fopen($path, 'r')) === false) {
            $this->error("Unable to open file: {$path}");

            return self::FAILURE;
        }

        $header = fgetcsv($handle);
        if ($header === false) {
            $this->error('CSV appears to be empty.');
            fclose($handle);

            return self::FAILURE;
        }

        $normalized = array_map(fn ($h) => strtolower(trim($h)), $header);

        $uuidIdx = array_search('dataset id', $normalized, true);
        $datasetNameIdx = array_search('dataset name', $normalized, true);

        if ($uuidIdx === false || $datasetNameIdx === false) {
            $this->error("CSV must contain columns 'dataset id' and 'dataset name'.");
            fclose($handle);

            return self::FAILURE;
        }

        $activityUpdates = 0;
        $organizationUpdates = 0;
        $skipped = 0;
        $rowNumber = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;

            $datasetUuid = trim($row[$uuidIdx] ?? '');
            $datasetName = trim($row[$datasetNameIdx] ?? '');

            if ($datasetUuid === '' || $datasetName === '') {
                $this->warn("Row {$rowNumber}: missing uuid or dataset name, skipping");
                $skipped++;
                continue;
            }

            if (Str::contains($datasetName, '-activities')) {
                $filename = "{$datasetName}.xml"; // incoming name already contains "-activities"
                $updated = ActivityPublished::where('filename', $filename)
                    ->update(['dataset_uuid' => $datasetUuid]);

                $updated
                    ? $this->line("Row {$rowNumber}: updated activity dataset {$filename} -> {$datasetUuid}")
                    : $this->warn("Row {$rowNumber}: no activity_published row with filename {$filename}");

                $activityUpdates += $updated;
                continue;
            }

            if (Str::contains($datasetName, '-organisation')) {
                $filename = "{$datasetName}.xml";
                $updated = OrganizationPublished::where('filename', $filename)
                    ->update(['dataset_uuid' => $datasetUuid]);

                $updated
                    ? $this->line("Row {$rowNumber}: updated organisation dataset {$filename} -> {$datasetUuid}")
                    : $this->warn("Row {$rowNumber}: no organization_published row with filename {$filename}");

                $organizationUpdates += $updated;
                continue;
            }

            $this->warn("Row {$rowNumber}: dataset name {$datasetName} does not match -activities/-organisation, skipping");
            $skipped++;
        }

        fclose($handle);

        $this->info("Activity rows updated:     {$activityUpdates}");
        $this->info("Organisation rows updated: {$organizationUpdates}");
        $this->info("Skipped / unmatched rows:  {$skipped}");

        return self::SUCCESS;
    }
}
