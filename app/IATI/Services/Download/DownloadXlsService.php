<?php

declare(strict_types=1);

namespace App\IATI\Services\Download;

use App\IATI\Repositories\Activity\IndicatorRepository;
use App\IATI\Repositories\Activity\ResultRepository;
use App\IATI\Repositories\Download\XlsDownloadStatusRepository;
use App\Jobs\ExportXlsJob;
use App\Jobs\XlsxExportCompleteJob;
use App\Jobs\ZipXlsFileJob;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class DownloadXlsService.
 */
class DownloadXlsService
{
    /**
     * Result Repository to get results data for download.
     *
     * @var ResultRepository
     */
    protected ResultRepository $resultRepository;

    /**
     * Indicator Repository for indicator related data for download.
     *
     * @var IndicatorRepository
     */
    protected IndicatorRepository $indicatorRepository;

    /**
     * XlsDownloadStatusRepository for store, get, update, increment file count, reset download status.
     *
     * @var XlsDownloadStatusRepository
     */
    protected XlsDownloadStatusRepository $xlsDownloadStatusRepository;

    /**
     * Download Xls Service constructor.
     *
     * @param ResultRepository $resultRepository
     * @param IndicatorRepository $indicatorRepository
     * @param XlsDownloadStatusRepository $xlsDownloadStatusRepository
     */
    public function __construct(ResultRepository $resultRepository, IndicatorRepository $indicatorRepository, XlsDownloadStatusRepository $xlsDownloadStatusRepository)
    {
        $this->resultRepository = $resultRepository;
        $this->indicatorRepository = $indicatorRepository;
        $this->xlsDownloadStatusRepository = $xlsDownloadStatusRepository;
    }

    /**
     * Returns results having given ids for downloading.
     *
     * @param $resultIds
     *
     * @return Builder
     */
    public function getResultsWithIndicatorQueryToDownload($resultIds): Builder
    {
        return $this->resultRepository->getResultsWithIndicatorQueryToDownload($resultIds);
    }

    /**
     * Returns indicators having given ids for downloading.
     *
     * @param $indicatorIds
     *
     * @return Builder
     */
    public function getIndicatorWithPeriodsQueryToDownload($indicatorIds): Builder
    {
        return $this->indicatorRepository->getIndicatorWithPeriodsQueryToDownload($indicatorIds);
    }

    /**
     * Stores initial state of download status.
     *
     * @param $userId
     * @param $selected_activities
     *
     * @return Model|null
     */
    public function storeStatus($userId, $selected_activities): ?Model
    {
        return $this->xlsDownloadStatusRepository->storeStatus($userId, 'xls', $selected_activities);
    }

    /**
     * Increment file count by one.
     *
     * @param $userId
     *
     * @return void
     */
    public function incrementFileCount($userId, $statusId): void
    {
        $this->xlsDownloadStatusRepository->incrementFileCount($userId, $statusId, 'xls');
    }

    /**
     * Update download status of a suer.
     *
     * @param $statusId
     * @param $data
     *
     * @return bool
     */
    public function updateDownloadStatus($statusId, $data): bool
    {
        return $this->xlsDownloadStatusRepository->updateDownloadStatus($statusId, $data);
    }

    /**
     * Deletes download process of a user.
     *
     * @param $userId
     * @param $statusId
     *
     * @return bool
     */
    public function deleteDownloadStatus($userId, $statusId): bool
    {
        return $this->xlsDownloadStatusRepository->deleteDownloadStatus($userId, $statusId);
    }

    /**
     * Get download progress status.
     *
     * @return array
     */
    public function getDownloadStatus(): array
    {
        return $this->xlsDownloadStatusRepository->getDownloadStatus(auth()->user()->id, 'xls');
    }

    /**
     * get download status of a specific user.
     *
     * @param $userId
     *
     * @return Builder|Model|null
     */
    public function getDownloadStatusByUserId($userId): Model|Builder|null
    {
        return $this->xlsDownloadStatusRepository->getDownloadStatusObject($userId, 'xls');
    }

    /**
     * Resets download status back to starting point.
     *
     * @return array|null
     */
    public function resetDownloadStatus(): ?array
    {
        return $this->xlsDownloadStatusRepository->resetDownloadStatus(auth()->user()->id, 'xls');
    }

    /**
     * Deletes file from s3.
     *
     * @param $userId
     * @param $statusId
     *
     * @return void
     */
    public function clearPreviousXlsFilesOnS3($userId, $statusId): void
    {
        awsDeleteFile("Xls/$userId/$statusId/xlsFiles.zip");
        awsDeleteFile("Xls/$userId/$statusId/status.json");
        awsDeleteFile("Xls/$userId/$statusId/cancelStatus.json");
    }

    /**
     * Exports xls in queue.
     * First it generates all the 4 files , zips it , upload it to s3 and mail to the user.
     *
     * @param $request
     * @param $statusId
     *
     * @return void
     */
    public function processXlsExportJobs($request, $statusId): void
    {
        $authUser = auth()->user();
        ExportXlsJob::withChain([
            new ZipXlsFileJob($authUser->id, $statusId),
            new XlsxExportCompleteJob($authUser->email, $authUser->username, $authUser->id, $statusId),
        ])->dispatch($request->all(), $authUser->toArray(), $statusId);
    }
}
