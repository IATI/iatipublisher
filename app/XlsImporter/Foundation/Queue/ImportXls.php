<?php

declare(strict_types=1);

namespace App\XlsImporter\Foundation\Queue;

use App\IATI\Repositories\Import\ImportStatusRepository;
use App\Jobs\Job;
use App\XlsImporter\Foundation\XlsQueueProcessor;
use Illuminate\Contracts\Queue\ShouldQueue;
use Throwable;

/**
 * Class ImportXls.
 */
class ImportXls extends Job implements ShouldQueue
{
    /**
     * Organization id.
     *
     * @var int
     */
    protected $organizationId;

    /**
     * Organization reporting org.
     *
     * @var array
     */
    protected $reportingOrg;

    /**
     * Uploaded xls filename.
     *
     * @var string
     */
    protected $filename;

    /**
     * user id.
     * @var int
     */
    protected $userId;

    /**
     * Array containing all the identifiers existing in the system.
     *
     * @var array
     */
    protected $iatiIdentifiers;

    /**
     * Type of xls file that has been uploaded (template).
     *
     * @var string
     */
    protected $xlsType;

    /**
     * path where xls file is stored.
     *
     * @var string
     */
    private string $xls_file_storage_path;

    /**
     * path where processed xls data is stored.
     *
     * @var string
     */
    private string $xls_data_storage_path;

    /**
     * ImportXls constructor.
     *
     * @param $organizationId
     * @param $reportingOrg
     * @param $userId
     * @param $filename
     * @param $iatiIdentifiers
     * @param $xlsType
     */
    public function __construct($organizationId, $reportingOrg, $userId, $filename, $iatiIdentifiers, $xlsType)
    {
        $this->organizationId = $organizationId;
        $this->reportingOrg = $reportingOrg;
        $this->filename = $filename;
        $this->userId = $userId;
        $this->iatiIdentifiers = $iatiIdentifiers;
        $this->xlsType = $xlsType;
        $this->xls_file_storage_path = config('import.xls_file_storage_path');
        $this->xls_data_storage_path = config('import.xls_data_storage_path');
    }

    /**
     * Handle method of job.
     *
     * @return void
     * @throws Throwable
     */
    public function handle(): void
    {
        try {
            /** @var XlsQueueProcessor $xlsImportQueue */
            $xlsImportQueue = app()->make(XlsQueueProcessor::class);
            $xlsImportQueue->import($this->filename, $this->organizationId, $this->reportingOrg, $this->userId, $this->iatiIdentifiers, $this->xlsType);

            $this->delete();
        } catch (\Exception $e) {
            logger()->error($e);
            awsUploadFile('error.log', $e->getMessage());
            awsUploadFile(sprintf('%s/%s/%s/%s', $this->xls_data_storage_path, $this->organizationId, $this->userId, 'status.json'), json_encode(['success' => false, 'message' => trans('common/common.error_has_occurred_while_importing_the_file')], JSON_THROW_ON_ERROR));
            $this->delete();
        }
    }

    /**
     * Handles a job failure.
     *
     * @param Throwable $exception
     *
     * @return void
     * @throws \JsonException
     */
    public function failed(Throwable $exception): void
    {
        awsUploadFile(sprintf('%s/%s/%s/%s', $this->xls_data_storage_path, $this->organizationId, $this->userId, 'status.json'), json_encode(['success' => false, 'message' => 'Failed to import xls file. Please check your file for correctness before importing again.'], JSON_THROW_ON_ERROR));
    }

    /**
     * Delete the job from the queue.
     *
     * @return void
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function delete()
    {
        if ($this->job) {
            $completionStatus = json_decode(awsGetFile(sprintf('%s/%s/%s/%s', $this->xls_data_storage_path, $this->organizationId, $this->userId, 'status.json')), true, 512, 0);
            $importStatus = app()->make(ImportStatusRepository::class);
            $status = $importStatus->getImportStatus($this->organizationId, $this->userId);

            if (!empty($status)) {
                $importStatus->update($status['id'], ['status' => $completionStatus['message'] === 'Complete' ? 'completed' : 'failed']);
            }

            return $this->job->delete();
        }
    }
}
