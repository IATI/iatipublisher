<?php

declare(strict_types=1);

namespace App\Http\Controllers\Audit;

use App\Http\Controllers\Controller;
use App\IATI\Services\Audit\AuditService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Class AuditController.
 */
class AuditController extends Controller
{
    /**
     * @var AuditService
     */
    private AuditService $auditService;

    /**
     * @param AuditService $auditService
     */
    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    /**
     * Show the audit log listing page.
     *
     * @return View|RedirectResponse
     */
    public function index(): View|RedirectResponse
    {
        try {
            return view('admin.audit.index');
        } catch (\Exception $e) {
            logger()->error($e);

            return redirect()->route('admin.activities.index')->with('error', 'Error while rendering audit listing page');
        }
    }

    /**
     * Returns paginated audit logs.
     *
     * @param Request $request
     * @param int $page
     *
     * @return JsonResponse
     */
    public function getAuditLog(Request $request, int $page = 1): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'message' => 'Audit log has been successfully fetched.',
                'data'    => $this->auditService->getAuditLog($page, $this->sanitizeRequest($request)), ]);
        } catch (\Exception $e) {
            logger()->error($e);

            return response()->json(['success'=>false, 'message'=> 'Error has occurred while trying to fetch audit log.']);
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
        $tableConfig = getTableConfig('audit');
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
