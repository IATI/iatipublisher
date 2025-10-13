<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Download;

use App\Http\Controllers\Controller;
use App\IATI\Services\Audit\AuditService;
use App\IATI\Services\Download\CsvGenerator;
use App\IATI\Services\Download\DownloadActivityService;
use App\IATI\Services\Download\DownloadXlsService;
use App\XmlImporter\Foundation\Support\Providers\XmlServiceProvider;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Class DownloadActivityController.
 */
class DownloadActivityController extends Controller
{
    /**
     * @var DownloadActivityService
     */
    protected DownloadActivityService $downloadActivityService;

    /**
     * @var DownloadXlsService
     */
    protected DownloadXlsService $downloadXlsService;

    /**
     * @var CsvGenerator
     */
    protected CsvGenerator $csvGenerator;

    /**
     * @var XmlServiceProvider
     */
    protected XmlServiceProvider $xmlServiceProvider;

    /**
     * @var AuditService
     */
    protected AuditService $auditService;

    /**
     * DownloadActivityController Constructor.
     *
     * @param DownloadActivityService $downloadActivityService
     * @param CsvGenerator $csvGenerator
     * @param XmlServiceProvider $xmlServiceProvider
     * @param AuditService $auditService
     * @param DownloadXlsService $downloadXlsService
     */
    public function __construct(
        DownloadActivityService $downloadActivityService,
        CsvGenerator $csvGenerator,
        XmlServiceProvider $xmlServiceProvider,
        AuditService $auditService,
        DownloadXlsService $downloadXlsService,
    ) {
        $this->downloadActivityService = $downloadActivityService;
        $this->csvGenerator = $csvGenerator;
        $this->xmlServiceProvider = $xmlServiceProvider;
        $this->auditService = $auditService;
        $this->downloadXlsService = $downloadXlsService;
    }

    /**
     * Downloads selected activities in csv format.
     *
     * @param Request $request
     *
     * @return BinaryFileResponse|JsonResponse
     */
    public function downloadActivityCsv(Request $request): BinaryFileResponse|JsonResponse
    {
        try {
            $activityIds = ($request->get('activities') && $request->get('activities') !== 'all') ?
                json_decode($request->get('activities'), true, 512, JSON_THROW_ON_ERROR) : [];
            $headers = $this->downloadActivityService->getCsvHeaderArray('Activity', 'other_fields_transaction');
            $filename = $this->downloadActivityService->getOrganizationPublisherId();

            if (request()->get('activities') === 'all') {
                $activities = $this->downloadActivityService->getAllActivitiesToDownload($this->sanitizeRequest($request));
            } elseif (is_array($activityIds) && !empty($activityIds)) {
                $activities = $this->downloadActivityService->getActivitiesToDownload($activityIds);
            }

            if (!isset($activities) || !count($activities)) {
                $translatedMessage = trans('common/common.no_activities_selected');

                return response()->json(['success' => false, 'message' => $translatedMessage]);
            }

            $csvData = $this->downloadActivityService->getCsvData($activities);
            $humanitarianScopeVocabularyArray = getCodeList('HumanitarianScopeVocabulary', 'Activity', filterDeprecated: true);

            foreach ($csvData as $index => $data) {
                $csvData[$index]['Humanitarian Scope Vocabulary'] = Arr::get($humanitarianScopeVocabularyArray, $csvData[$index]['Humanitarian Scope Vocabulary']);
            }

            $this->auditService->auditEvent($activities, 'download', 'csv');

            return $this->csvGenerator->generateWithHeaders(getTimeStampedText($filename), $csvData, $headers);
        } catch (\Exception $e) {
            logger()->error($e);
            $this->auditService->auditEvent(null, 'download', 'csv');
            $translatedMessage = trans('common/common.error_has_occurred_while_downloading_activity_csv');

            return response()->json(['success' => false, 'message' => $translatedMessage]);
        }
    }

    /**
     * Prepare selected activities in xls format.
     *
     * @param Request $request
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface|\Throwable
     *
     * @return BinaryFileResponse|JsonResponse
     */
    public function prepareActivityXls(Request $request): BinaryFileResponse|JsonResponse
    {
        try {
            $userId = auth()->user()->id;
            $status = $this->downloadXlsService->getDownloadStatusByUserId($userId)?->toArray();

            if (!empty($status)) {
                $translatedMessage = trans('workflow_backend/download_activity_controller.previous_download_on_process');

                return response()->json(['success' => false, 'message' => $translatedMessage]);
            }

            $activityIds = ($request->get('activities') && $request->get('activities') !== 'all')
                ? json_decode($request->get('activities'), true, 512, JSON_THROW_ON_ERROR)
                : [];

            if (request()->get('activities') === 'all') {
                $activities = $this->downloadActivityService->getAllActivitiesQueryToDownload($this->sanitizeRequest($request), auth()->user());
            } elseif (!empty($activityIds)) {
                $activities = $this->downloadActivityService->getActivitiesQueryToDownload($activityIds, auth()->user());
            }

            if (!isset($activities) || !$activities->count()) {
                $translatedMessage = trans('common/common.no_activities_selected');

                return response()->json(['success' => false, 'message' => $translatedMessage]);
            }

            $status = $this->downloadXlsService->storeStatus($userId, $activityIds);
            awsUploadFile("Xls/$userId/" . $status['id'] . '/status.json', json_encode(['success' => true, 'message' => trans('workflow_backend/download_activity_controller.processing')], JSON_THROW_ON_ERROR));
            $this->downloadXlsService->processXlsExportJobs($request, $status['id']);
            $translatedMessage = trans('workflow_backend/download_activity_controller.xls_export_on_process');

            return response()->json(['success' => true, 'message' => $translatedMessage]);
        } catch (\Exception $e) {
            logger()->error($e);
            $this->downloadXlsService->deleteDownloadStatus(auth()->user()->id);
            $this->cancelXlsDownload();
            $translatedMessage = trans('workflow_backend/download_activity_controller.error_has_occurred_while_downloading_activity_xls');

            return response()->json(['success' => false, 'message' => $translatedMessage]);
        }
    }

    /**
     * Downloads xls zip file from aws s3.
     *
     * @return bool|int
     */
    public function downloadActivityXls(): bool|int
    {
        $authUser = auth()->user();
        $userId = $authUser->id;
        $status = $this->downloadXlsService->getDownloadStatusByUserId($userId)?->toArray();
        $organizationName = $authUser->organization->publisher_name ?? 'xlsFiles';
        $fileName = $organizationName . '-' . Carbon::now()->timestamp . '.zip';
        $temporaryUrl = awsUrl("Xls/$userId/" . $status['id'] . '/xlsFiles.zip');
        header("Content-Disposition: attachment; filename=$fileName");
        header('Content-Type: application/zip');
        $this->downloadXlsService->deleteDownloadStatus($userId, $status['id']);
        $file = readfile($temporaryUrl);
        $this->downloadXlsService->clearPreviousXlsFilesOnS3($userId, $status['id']);

        return $file;
    }

    /**
     * Download api to check the progress of a xls export.
     *
     * @return JsonResponse
     */
    public function xlsDownloadInProgressStatus(): JsonResponse
    {
        try {
            [$status, $fileCount, $url] = $this->downloadXlsService->getDownloadStatus();
            $translatedMessage = trans('workflow_backend/download_activity_controller.download_status_accessed_successfully');

            return response()->json(['success' => true, 'message' => $translatedMessage, 'status' => $status, 'file_count' => $fileCount, 'url' => $url ?? null]);
        } catch (\Exception $e) {
            logger()->error($e);
            $translatedMessage = trans('workflow_backend/download_activity_controller.error_has_occurred_while_trying_to_check_download_status');

            return response()->json(['success' => false, 'message' => $translatedMessage]);
        }
    }

    /**
     * Retry download if it fails.
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \Throwable
     *
     * @return JsonResponse|void
     */
    public function retryXlsDownload()
    {
        try {
            $activities = $this->downloadXlsService->resetDownloadStatus();

            if (!empty($activities)) {
                $userId = auth()->user()->id;
                $status = $this->downloadXlsService->getDownloadStatusByUserId($userId)?->toArray();
                $this->downloadXlsService->clearPreviousXlsFilesOnS3($userId, $status['id']);
                $this->prepareActivityXls(request()->merge(['activities' => json_encode($activities, JSON_THROW_ON_ERROR)]));
            }
        } catch (\Exception $e) {
            logger()->error($e);
            $translatedMessage = trans('workflow_backend/download_activity_controller.error_has_occurred_while_trying_to_retry_download');

            return response()->json(['success' => false, 'message' => $translatedMessage]);
        }
    }

    /**
     * Checks if the status is completed
     * if completed then do not upload cancel json else upload.
     *
     * @return JsonResponse
     */
    public function cancelXlsDownload(): JsonResponse
    {
        try {
            $userId = auth()->user()->id;
            $status = $this->downloadXlsService->getDownloadStatusByUserId($userId)?->toArray();
            $this->downloadXlsService->clearPreviousXlsFilesOnS3($userId, $status['id']);
            awsUploadFile("Xls/$userId/" . $status['id'] . '/cancelStatus.json', json_encode(['success' => true, 'message' => trans('workflow_backend/download_activity_controller.cancelled')], JSON_THROW_ON_ERROR));
            $this->downloadXlsService->deleteDownloadStatus($userId, $status['id']);
            $translatedMessage = trans('workflow_backend/download_activity_controller.cancelled_successfully');

            return response()->json(['success' => true, 'message' => $translatedMessage]);
        } catch (\Exception $e) {
            logger()->error($e);
            $translatedMessage = trans('workflow_backend/download_activity_controller.error_has_occurred_while_trying_to_cancel_download');

            return response()->json(['success' => false, 'message' => $translatedMessage]);
        }
    }

    /**
     * Downloads selected activities in xml format.
     *
     * @param Request $request
     * @param bool $download
     *
     * @return Response|JsonResponse
     */
    public function downloadActivityXml(Request $request, bool $download = false): Response|JsonResponse
    {
        try {
            $activityIds = ($request->get('activities') && $request->get('activities') !== 'all') ?
                json_decode($request->get('activities'), true, 512, JSON_THROW_ON_ERROR) : [];
            $filename = $this->downloadActivityService->getOrganizationPublisherId();

            if (request()->get('activities') === 'all') {
                $activities = $this->downloadActivityService->getAllActivitiesToDownload($this->sanitizeRequest($request));
            } elseif (is_array($activityIds) && !empty($activityIds)) {
                $activities = $this->downloadActivityService->getActivitiesToDownload($activityIds);
            }

            if (!isset($activities) || !count($activities)) {
                $translatedMessage = trans('common/common.no_activities_selected');

                return response()->json(['success' => false, 'message' => $translatedMessage]);
            }

            $mergedContent = $this->downloadActivityService->getCombinedXmlFile($activities);

            $this->auditService->auditEvent($activities, 'download', 'xml');

            return response($mergedContent)
                ->withHeaders([
                    'Content-Type' => 'text/xml',
                    'Cache-Control' => 'public',
                    'Content-Description' => 'File Transfer',
                    'Content-Disposition' => 'attachment; filename=' . getTimeStampedText($filename) . '.xml',
                    'Content-Transfer-Encoding' => 'binary',
                ]);
        } catch (\Exception $e) {
            logger()->error($e);
            $this->auditService->auditEvent(null, 'download', 'xml');
            $translatedMessage = trans('common/common.error_has_occurred_while_downloading_activity_csv');

            return response()->json(['success' => false, 'message' => $translatedMessage]);
        }
    }

    /**
     * Sanitizes the request for removing code injections.
     *
     * @param $request
     *
     * @return array
     */
    public function sanitizeRequest($request): array
    {
        $tableConfig = getTableConfig('activity');
        $queryParams = [];

        if (!empty($request->get('q')) || $request->get('q') === '0') {
            $queryParams['query'] = $request->get('q');
        }

        if (in_array($request->get('orderBy'), $tableConfig['orderBy'], true)) {
            $queryParams['orderBy'] = $request->get('orderBy');

            if (in_array($request->get('direction'), $tableConfig['direction'], true)) {
                $queryParams['direction'] = $request->get('direction');
            }
        }

        return $queryParams;
    }
}
