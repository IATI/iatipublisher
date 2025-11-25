<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\User;

use App\Http\Controllers\Controller;
use App\IATI\Services\Audit\AuditService;
use App\IATI\Services\Dashboard\DashboardService;
use App\IATI\Services\Download\CsvGenerator;
use App\IATI\Services\Organization\OrganizationService;
use App\IATI\Services\User\UserService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\DatabaseManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Class UserController.
 */
class UserController extends Controller
{
    /**
     * @var UserService
     */
    protected UserService $userService;

    /**
     * @var OrganizationService
     */
    protected OrganizationService $organizationService;

    /**
     * @var AuditService
     */
    protected AuditService $auditService;

    /**
     * @var CsvGenerator
     */
    protected CsvGenerator $csvGenerator;

    /**
     * @var DatabaseManager
     */
    protected DatabaseManager $db;

    /**
     * @var DashboardService
     */
    protected DashboardService $dashboardService;

    /**
     * Create a new controller instance.
     *
     * @param UserService $userService
     * @param OrganizationService $organizationService
     * @param AuditService $auditService
     * @param CsvGenerator $csvGenerator
     * @param DatabaseManager $db
     * @param DashboardService $dashboardService
     */
    public function __construct(UserService $userService, OrganizationService $organizationService, AuditService $auditService, CsvGenerator $csvGenerator, DatabaseManager $db, DashboardService $dashboardService)
    {
        $this->userService = $userService;
        $this->organizationService = $organizationService;
        $this->auditService = $auditService;
        $this->csvGenerator = $csvGenerator;
        $this->db = $db;
        $this->dashboardService = $dashboardService;
    }

    /**
     * Renders user listing page.
     *
     * @return View|RedirectResponse
     */
    public function index(): View|RedirectResponse
    {
        try {
            $organizations = $this->organizationService->pluckAllOrganizations();
            $status = getUserStatus();
            $roles = $this->userService->getRoles();
            $userRole = Auth::user()->role->role;
            $oldestDates = $this->dashboardService->getOldestDate('user');

            return view('admin.user.index', compact('status', 'organizations', 'roles', 'userRole', 'oldestDates'));
        } catch (\Exception $e) {
            logger()->error($e);

            $translatedMessage = 'Error Has Occurred While Rendering User Listing Page';

            return redirect()->back()->with('error', $translatedMessage);
        }
    }

    /**
     * Get User status.
     *
     * @return JsonResponse
     */
    public function getUserVerificationStatus(): JsonResponse
    {
        try {
            $status = $this->userService->getStatus();
            $translatedMessage = 'User status successfully retrieved.';

            return response()->json([
                'success' => true,
                'message' => $translatedMessage,
                'data'    => ['account_verified' => $status],
            ]);
        } catch (\Exception $e) {
            logger()->error($e);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Show user profile.
     *
     * @return View|RedirectResponse
     */
    public function showUserProfile(): View|RedirectResponse
    {
        try {
            $user = Auth::user();
            $user['user_role'] = $user->role->role;
            $user['organization_name'] = $user->organization_id ? $user->organization->publisher_name : null;
            $languagePreference = getLanguagePreference();

            return view('admin.user.profile', compact('user', 'languagePreference'));
        } catch (\Exception $e) {
            logger()->error($e);
            $translatedMessage = trans('common/common.error_while_rendering_setting_page');

            return redirect()->route('admin.activities.index')->with('error', $translatedMessage);
        }
    }

    /**
     * Return paginated users.
     *
     * @param Request $request
     * @param int $page
     *
     * @return JsonResponse
     */
    public function getPaginatedUsers(Request $request, int $page = 1): JsonResponse
    {
        try {
            $queryParams = $this->getQueryParams($request);
            $users = $this->userService->getPaginatedUsers($page, $queryParams);
            $translatedMessage = 'Paginated users fetched successfully.';

            return response()->json([
                'success' => true,
                'message' => $translatedMessage,
                'data' => $users,
            ]);
        } catch (\Exception $e) {
            logger()->error($e);
            $translatedMessage = 'Error occurred while trying to get paginated user.';

            return response()->json([
                'success' => false,
                'message' => $translatedMessage,
            ]);
        }
    }

    /**
     * Get queryParams.
     *
     * @param $request
     *
     * @return array
     */
    public function getQueryParams($request): array
    {
        $tableConfig = getTableConfig('user');
        $accessibleRoles = array_keys($this->userService->getRoles());
        $accessibleOrg = Auth::user()->organization_id;
        $requestData = $request->all();
        $organization_id = Arr::get($requestData, 'organization', null) ? explode(',', Arr::get($requestData, 'organization')) : [];
        $roles = Arr::get($request, 'roles', null) ? explode(',', Arr::get($request, 'roles')) : [];
        $queryParams = [];

        if ($accessibleOrg) {
            $organization_id[] = $accessibleOrg;
        }

        if ($accessibleRoles) {
            $roles = empty($roles) ? $accessibleRoles : $roles;
        }

        $queryParams['organization_id'] = $organization_id;
        $queryParams['role'] = $roles;

        if (!empty($request->get('q')) || $request->get('q') === '0') {
            $queryParams['q'] = $request->get('q');
        }

        if (!empty($request->get('status')) || $request->get('status') === '0') {
            $queryParams['status'] = explode(',', $request->get('status'));
        }

        if (!empty($request->get('users')) || $request->get('users') === '0') {
            $queryParams['users'] = explode(',', $request->get('users'));
        }

        if (in_array($request->get('orderBy'), $tableConfig['orderBy'], true)) {
            $queryParams['orderBy'] = $request->get('orderBy');

            if (in_array($request->get('direction'), $tableConfig['direction'], true)) {
                $queryParams['direction'] = $request->get('direction');
            }
        }

        if (!empty($request->get('date_type'))) {
            $queryParams['dateType'] = $request->get('date_type');
            $queryParams['startDate'] = $request->get('start_date');
            $queryParams['endDate'] = $request->get('end_date');
        }

        return $queryParams;
    }

    /**
     * Download users in csv format.
     *
     * @param Request $request
     *
     * @return BinaryFileResponse|JsonResponse
     */
    public function downloadUsers(Request $request): BinaryFileResponse|JsonResponse
    {
        try {
            $headers = getUserCsvHeader();
            $queryParams = $this->getQueryParams($request);
            $users = $this->userService->getUserDownloadData($queryParams);

            $this->auditService->auditEvent($users, 'download');

            return $this->csvGenerator->generateWithHeaders(getTimeStampedText('users'), $users->toArray(), $headers);
        } catch (\Exception $e) {
            logger()->error($e);
            $this->auditService->auditEvent(null, 'download');

            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
