<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Dashboard;

use App\Http\Controllers\Controller;
use App\IATI\Services\Audit\AuditService;
use App\IATI\Services\Dashboard\DashboardService;
use App\IATI\Services\Download\CsvGenerator;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Exception;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Class DashboardController.
 */
class DashboardController extends Controller
{
    /**
     * @var DashboardService
     */
    public DashboardService $dashboardService;

    /**
     * @var CsvGenerator
     */
    public CsvGenerator $csvGenerator;

    /**
     * @var AuditService
     */
    public AuditService $auditService;

    /**
     * ActivityController Constructor.
     *
     * @param DashboardService $dashboardService
     * @param CsvGenerator $csvGenerator
     * @param AuditService $auditService
     */
    public function __construct(
        DashboardService $dashboardService,
        CsvGenerator $csvGenerator,
        AuditService $auditService
    ) {
        $this->dashboardService = $dashboardService;
        $this->csvGenerator = $csvGenerator;
        $this->auditService = $auditService;
    }

    /**
     * Show the application dashboard.
     *
     * @return Renderable
     */
    public function index(): Renderable
    {
        $oldestDates = $this->dashboardService->getOldestDate();

        return view('admin.dashboard.index', compact('oldestDates'));
    }

    /**
     * Returns user count for user dashboard.
     *
     * @return JsonResponse
     */
    public function getUserCounts(): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'message' => 'User count fetched successfully',
                'data' => $this->dashboardService->getUserCounts(),
            ]);
        } catch (Exception $e) {
            logger()->error($e);

            return response()->json(['success' => false, 'message' => 'Error occurred while fetching the publisher stats.']);
        }
    }

    /**
     * Returns user count grouped by organization for dashboard user table.
     *
     * @param Request $request
     * @param int $page
     *
     * @return JsonResponse
     */
    public function getUserCountByOrganization(Request $request, int $page = 1): JsonResponse
    {
        try {
            $page = (int) $request->get('page') ?? $page;
            $tableConfig = getTableConfig('user');
            $queryParams = [];

            if (in_array($request->get('orderBy'), $tableConfig['orderBy'], true)) {
                $queryParams['orderBy'] = $request->get('orderBy');

                if (in_array($request->get('direction'), $tableConfig['direction'], true)) {
                    $queryParams['direction'] = $request->get('direction');
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Paginated users fetched successfully',
                'data' => $this->dashboardService->getUserCountByOrganization($page, $queryParams),
            ]);
        } catch (Exception $e) {
            logger()->error($e);

            return response()->json(['success' => false, 'message' => 'Error occurred while fetching the paginated users.']);
        }
    }

    /**
     * Gets query params.
     *
     * @param $request
     *
     * @return array
     */
    protected function getQueryParams($request): array
    {
        $validParameters = ['start_date', 'end_date', 'orderBy', 'direction', 'page'];
        $queryParams = [];

        foreach ($validParameters as $parameter) {
            $value = $request->get($parameter);

            if (!empty($value) || is_numeric($value)) {
                $queryParams[$parameter] = $value;
            }
        }

        if (isset($queryParams['start_date']) && isset($queryParams['end_date'])) {
            $startDate = Carbon::parse($queryParams['start_date']);
            $endDate = Carbon::parse($queryParams['end_date']);
            $period = [
                'Y' => 'Y',
                'Y-m' => 'M',
                'Y-m-d' => 'D',
            ];

            $queryParams['range'] = match (true) {
                $endDate->diffInYears($startDate) > 1 => 'Y',
                $endDate->diffInMonths($startDate) > 1 => 'Y-m',
                default => 'Y-m-d',
            };

            $queryParams['period'] = $period[$queryParams['range']];
        }

        return $queryParams;
    }

    /**
     * Returns json data containing publisher stats.
     *
     * @return JsonResponse
     */
    public function publisherStats(): JsonResponse
    {
        try {
            $publisherStat = $this->dashboardService->getPublisherStats();

            return response()->json([
                'success' => true,
                'message' => 'Publisher stats fetched successfully',
                'data' => $publisherStat,
            ]);
        } catch (Exception $e) {
            logger()->error($e);

            return response()->json(['success' => false, 'message' => 'Error occurred while fetching the publisher stats.']);
        }
    }

    /**
     * Returns json data containing publisher registration count.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function publisherRegistrationCount(Request $request): JsonResponse
    {
        try {
            $params = $this->getQueryParams($request);
            $params = $this->dashboardService->resolveStartDateAndEndDate($request, $params, 'organizations');

            $publisherStat = $this->dashboardService->getPublisherGroupedByDate($params, 'created_at');

            return response()->json([
                'success' => true,
                'message' => 'Publisher registration count fetched successfully',
                'data' => [
                    'count' => array_sum($publisherStat),
                    'graph' => $publisherStat,
                ],
            ]);
        } catch (Exception $e) {
            logger()->error($e);

            return response()->json(['success' => false, 'message' => 'Error occurred while fetching the publisher stats.']);
        }
    }

    /**
     * Returns json data containing registration type.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function publisherRegistrationType(Request $request): JsonResponse
    {
        try {
            $params = $this->getQueryParams($request);
            $params = $this->dashboardService->resolveStartDateAndEndDate($request, $params, 'organizations');

            $publisherStat = $this->dashboardService->getPublisherBy($params, 'registration_type');

            return response()->json([
                'success' => true,
                'message' => 'Publisher by registration type fetched successfully',
                'data' => $publisherStat,
            ]);
        } catch (Exception $e) {
            logger()->error($e);

            return response()->json(['success' => false, 'message' => 'Error occurred while fetching the publisher by registration type.']);
        }
    }

    /**
     * Returns json data containing publisher grouped by country.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function publisherGroupedByCountry(Request $request): JsonResponse
    {
        try {
            $params = $this->getQueryParams($request);
            $params = $this->dashboardService->resolveStartDateAndEndDate($request, $params, 'organizations');

            $paginatedData = $this->dashboardService->getPublisherBy($params, 'country');
            $codeList = getCodeList('Country', 'Activity', filterDeprecated: true);

            return response()->json([
                'success' => true,
                'message' => 'Publisher grouped by country fetched successfully',
                'data' => ['paginatedData' => $paginatedData, 'codeList' => $codeList],
            ]);
        } catch (Exception $e) {
            logger()->error($e);

            return response()->json(['success' => false, 'message' => 'Error occurred while fetching the publisher stats.']);
        }
    }

    /**
     * Returns json data containing grouped by type.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function publisherGroupedByType(Request $request): JsonResponse
    {
        try {
            $params = $this->getQueryParams($request);
            $params = $this->dashboardService->resolveStartDateAndEndDate($request, $params, 'organizations');

            $paginatedData = $this->dashboardService->getPublisherBy($params, 'publisher_type');
            $codeList = getCodeList('OrganizationType', 'Organization', filterDeprecated: true);

            return response()->json([
                'success' => true,
                'message' => 'Publisher grouped by type fetched successfully',
                'data' => ['paginatedData' => $paginatedData, 'codeList' => $codeList],
            ]);
        } catch (Exception $e) {
            logger()->error($e);

            return response()->json(['success' => false, 'message' => 'Error occurred while fetching the publisher grouped by type.']);
        }
    }

    /**
     * Returns json data containing publisher grouped by data license.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function publisherGroupedByDataLicense(Request $request): JsonResponse
    {
        try {
            $params = $this->getQueryParams($request);
            $params = $this->dashboardService->resolveStartDateAndEndDate($request, $params, 'organizations');

            $paginatedData = $this->dashboardService->getPublisherBy($params, 'data_license');
            $codeList = getCodeList('DataLicense', 'Activity', filterDeprecated: true);

            return response()->json([
                'success' => true,
                'message' => 'Publisher grouped by type fetched successfully',
                'data' => ['paginatedData' => $paginatedData, 'codeList' => $codeList],
            ]);
        } catch (Exception $e) {
            logger()->error($e);

            return response()->json(['success' => false, 'message' => 'Error occurred while fetching the publisher grouped by type.']);
        }
    }

    /**
     * Returns json data containing publisher grouped by setup completeness.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function publisherGroupedBySetupCompleteness(Request $request): JsonResponse
    {
        try {
            $params = $this->getQueryParams($request);
            $params = $this->dashboardService->resolveStartDateAndEndDate($request, $params, 'organizations');

            $publisherStat = $this->dashboardService->getPublisherBySetup($params);

            return response()->json([
                'success' => true,
                'message' => 'Publisher grouped by setup completeness fetched successfully',
                'data' => $publisherStat,
            ]);
        } catch (Exception $e) {
            logger()->error($e);

            return response()->json(['success' => false, 'message' => 'Error occurred while fetching the publisher stats.']);
        }
    }

    /**
     * Returns json data containing activity stats.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function activityStats(Request $request): JsonResponse
    {
        try {
            $params = $this->getQueryParams($request);
            $publisherStat = $this->dashboardService->getActivityStats($params);

            return response()->json([
                'success' => true,
                'message' => 'Activity stats fetched successfully',
                'data' => $publisherStat,
            ]);
        } catch (Exception $e) {
            logger()->error($e);

            return response()->json(['success' => false, 'message' => 'Error occurred while fetching the activity stats.']);
        }
    }

    /**
     * Returns json data containing activity count.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function activityCount(Request $request): JsonResponse
    {
        try {
            $params = $this->getQueryParams($request);
            $activityData = $this->dashboardService->getActivityCount($params);

            return response()->json([
                'success' => true,
                'message' => 'Activity time range data fetched successfully',
                'data' => [
                    'count' => array_sum($activityData),
                    'graph' => $activityData,
                ],
            ]);
        } catch (Exception $e) {
            logger()->error($e);

            return response()->json(['success' => false, 'message' => 'Error occurred while fetching the publisher stats.']);
        }
    }

    /**
     * Returns json data containing activity status(draft, published, need to republish).
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function activityStatus(Request $request): JsonResponse
    {
        try {
            $params = $this->getQueryParams($request);
            $publisherStat = $this->dashboardService->getActivityStatus($params);

            return response()->json([
                'success' => true,
                'message' => 'Publisher grouped by setup completeness fetched successfully',
                'data' => $publisherStat,
            ]);
        } catch (Exception $e) {
            logger()->error($e);

            return response()->json(['success' => false, 'message' => 'Error occurred while fetching the publisher stats.']);
        }
    }

    /**
     * Returns json data containing activity count group by method of creation.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function activityMethod(Request $request): JsonResponse
    {
        try {
            $params = $this->getQueryParams($request);
            $activityStats = $this->dashboardService->getActivityBy($params, 'upload_medium');

            return response()->json([
                'success' => true,
                'message' => 'Publisher grouped by setup completeness fetched successfully',
                'data' => $activityStats,
            ]);
        } catch (Exception $e) {
            logger()->error($e);

            return response()->json(['success' => false, 'message' => 'Error occurred while fetching the publisher stats.']);
        }
    }

    /**
     * Returns json data containing activity completeness.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function activityCompleteness(Request $request): JsonResponse
    {
        try {
            $params = $this->getQueryParams($request);
            $activityStats = $this->dashboardService->getActivityCompleteness($params);

            return response()->json([
                'success' => true,
                'message' => 'Publisher grouped by setup completeness fetched successfully',
                'data' => $activityStats,
            ]);
        } catch (Exception $e) {
            logger()->error($e);

            return response()->json(['success' => false, 'message' => 'Error occurred while fetching the publisher stats.']);
        }
    }

    /**
     * Returns count of users registered in date range, grouped by nearest largest unit of time.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getDataInDateRange(Request $request): JsonResponse
    {
        try {
            list($startDateString, $endDateString, $column) = $this->dashboardService->resolveDateRangeFromRequest($request);

            if (!$startDateString || !$endDateString) {
                list($startDateString, $endDateString) = $this->dashboardService->getStartAndEndDateForAlltime();
            }

            list($startDate, $endDate, $groupBy) = $this->dashboardService->resolveCustomRangeParams($startDateString, $endDateString);
            $unformattedResults = $this->dashboardService->getDataCountInRange($startDate, $endDate, $groupBy, $column);
            $results = $this->dashboardService->fillDataToMissingDates($startDate, $endDate, $unformattedResults, $groupBy);

            return response()->json([
                'success' => true,
                'message' => 'User count in date range fetched successfully',
                'data' => ['graph' => $results, 'count' => array_sum($results)],
            ]);
        } catch (InvalidFormatException $e) {
            logger()->error($e);

            return response()->json(['success' => false, 'message' => 'Invalid date value entered in date range.']);
        } catch (Exception $e) {
            logger()->error($e);

            return response()->json(['success' => false, 'message' => 'Error occurred while fetching user count in custom-range.']);
        }
    }

    /**
     * Download user report csv.
     *
     * @param Request $request
     *
     * @return BinaryFileResponse|JsonResponse
     */
    public function downloadUserReport(Request $request): BinaryFileResponse|JsonResponse
    {
        try {
            list($startDateString, $endDateString) = $this->dashboardService->resolveDateRangeFromRequest($request);

            if (!$startDateString || !$endDateString) {
                list($startDateString, $endDateString) = $this->dashboardService->getStartAndEndDateForAlltime();
            }

            list($startDate, $endDate) = $this->dashboardService->resolveCustomRangeParams($startDateString, $endDateString);
            $userData = $this->dashboardService->getUserDataForReportDownload($startDate, $endDate);

            if (count($userData)) {
                $this->auditService->auditEvent($userData, 'download', 'csv');
                $headers = ['Username', 'Organization', 'Email', 'Created Date', 'Last Logged in', 'Role', 'Status'];

                return $this->csvGenerator->generateWithHeaders(getTimeStampedText('users_report'), $userData->toArray(), $headers);
            }

            return response()->json(['success' => false, 'message' => 'No user data to download within this range']);
        } catch (InvalidFormatException $e) {
            logger()->error($e);

            return response()->json(['success' => false, 'message' => 'Invalid date value entered in date range.']);
        } catch (Exception $e) {
            logger()->error($e);

            return response()->json(['success' => false, 'message' => 'Error occurred while downloading user report.']);
        }
    }

    /*
     * Downloads selected activities in csv format.
     *
     * @param Request $request
     *
     * @return BinaryFileResponse|JsonResponse
     */
    public function downloadActivity(Request $request): BinaryFileResponse|JsonResponse
    {
        try {
            $params = $this->getQueryParams($request);
            $headers = ['identifier', 'title', 'organization_name', 'status', 'medium', 'complete_percentage', 'created_at', 'updated_at'];

            $activities = $this->dashboardService->getAllActivitiesToDownload($params);

            return $this->csvGenerator->generateWithHeaders(getTimeStampedText('activities'), $activities, $headers);
        } catch (Exception $e) {
            logger()->error($e);

            return response()->json(['success' => false, 'message' => 'Error has occurred while downloading activity csv.']);
        }
    }

    /**
     * Download summary of organization in csv format.
     *
     * @param Request $request
     *
     * @return BinaryFileResponse|JsonResponse
     */
    public function downloadOrganization(Request $request): BinaryFileResponse|JsonResponse
    {
        try {
            $params = $this->getQueryParams($request);
            $params = $this->dashboardService->resolveStartDateAndEndDate($request, $params, 'organizations');

            $headers = ['organization', 'identifier', 'organisation_type', 'country', 'registration_type', 'data_license', 'publisher setting', 'default values', 'created_at', 'updated_at'];

            $organizations = $this->dashboardService->getOrganizationToDownload($params);

            return $this->csvGenerator->generateWithHeaders(getTimeStampedText('organizations'), $organizations, $headers);
        } catch (Exception $e) {
            logger()->error($e);

            return response()->json(['success' => false, 'message' => 'Error has occurred while downloading activity csv.']);
        }
    }
}
