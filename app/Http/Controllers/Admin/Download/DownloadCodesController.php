<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Download;

use App\Http\Controllers\Controller;
use App\IATI\Services\Audit\AuditService;
use App\IATI\Services\Download\CodesExport;
use App\IATI\Services\Download\DownloadCodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Class DownloadCodesController.
 */
class DownloadCodesController extends Controller
{
    /**
     * @var DownloadCodeService
     */
    protected DownloadCodeService $downloadCodeService;

    /**
     * @var AuditService
     */
    protected AuditService $auditService;

    /**
     * DownloadCodesController Constructor.
     *
     * @param DownloadCodeService $downloadCodeService
     * @param AuditService $auditService
     */
    public function __construct(
        DownloadCodeService $downloadCodeService,
        AuditService $auditService
    ) {
        $this->downloadCodeService = $downloadCodeService;
        $this->auditService = $auditService;
    }

    /**
     * Downloads selected result, indicator and period identifiers in xls format.
     *
     * @param Request $request
     *
     * @return BinaryFileResponse|JsonResponse
     */
    public function downloadCodes(Request $request): BinaryFileResponse|JsonResponse
    {
        try {
            $activityIds = ($request->get('activities') && $request->get('activities') !== 'all') ?
                json_decode($request->get('activities'), true, 512, JSON_THROW_ON_ERROR) : [];

            $codeData = $this->downloadCodeService->getActivitiesToDownload($activityIds);

            $filename = 'codes.xls';

            return Excel::download(new CodesExport($codeData, []), $filename, \Maatwebsite\Excel\Excel::XLSX);
        } catch (\Exception $e) {
            logger()->error($e->getMessage());
            $this->auditService->auditEvent(null, 'download', 'codes');
            $translatedMessage = trans('common/common.error_has_occurred_while_downloading_activity_csv');

            return response()->json(['success' => false, 'message' => $translatedMessage]);
        }
    }
}
