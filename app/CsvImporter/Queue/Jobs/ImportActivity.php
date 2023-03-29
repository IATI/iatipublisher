<?php

declare(strict_types=1);

namespace App\CsvImporter\Queue\Jobs;

use App\CsvImporter\Queue\CsvProcessor;
use App\Jobs\Job;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Session;

/**
 * Class ImportActivity.
 */
class ImportActivity extends Job implements ShouldQueue
{
    /**
     * @var CsvProcessor
     */
    protected CsvProcessor $csvProcessor;

    /**
     * Current Organization's Id.
     *
     * @var int|mixed
     */
    protected mixed $organizationId;

    /**
     * Current User's id.
     *
     * @var int|mixed
     */
    protected int $userId;

    /**
     * Directory where the uploaded Csv file is stored temporarily before import.
     */
    public string $csv_file_storage_path;

    /**
     * Directory where the uploaded Csv data file is stored temporarily before import.
     */
    public string $csv_data_storage_path;

    /**
     * @var string
     */
    protected string $filename;

    /**
     * @var array
     */
    private array $activityIdentifiers;

    /**
     * Current Organizations reporting_org.
     *
     * @var array
     */
    protected array $organizationReportingOrg;

    /**
     * Create a new job instance.
     *
     * @param CsvProcessor $csvProcessor
     * @param              $filename
     * @param              $activityIdentifiers
     */
    public function __construct(CsvProcessor $csvProcessor, $filename, $activityIdentifiers, $organizationReportingOrg)
    {
        $this->csvProcessor = $csvProcessor;
        $this->organizationId = Session::get('org_id');
        $this->userId = Session::get('user_id');
        $this->filename = $filename;
        $this->activityIdentifiers = $activityIdentifiers;
        $this->csv_file_storage_path = env('CSV_FILE_STORAGE_PATH', 'CsvImporter/file');
        $this->csv_data_storage_path = env('CSV_DATA_STORAGE_PATH', 'CsvImporter/tmp');
        $this->organizationReportingOrg = $organizationReportingOrg;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \JsonException
     */
    public function handle(): void
    {
        try {
            awsUploadFile(sprintf('%s/%s/%s/%s', $this->csv_data_storage_path, $this->organizationId, $this->userId, 'status.json'), json_encode(['success' => true, 'message' => 'Processing'], JSON_THROW_ON_ERROR));
            $this->csvProcessor->handle($this->organizationId, $this->userId, $this->activityIdentifiers);
            awsUploadFile(sprintf('%s/%s/%s/%s', $this->csv_data_storage_path, $this->organizationId, $this->userId, 'status.json'), json_encode(['success' => true, 'message' => 'Complete'], JSON_THROW_ON_ERROR));

            $this->delete();
        } catch (\Exception $e) {
            awsUploadFile(sprintf('%s/%s/%s/%s', $this->csv_data_storage_path, $this->organizationId, $this->userId, 'status.json'), json_encode(['success' => false, 'message' => $e->getMessage()], JSON_THROW_ON_ERROR));

            $this->delete();
        }
    }
}
