<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin;

use App\Constants\Enums;
use App\Http\Controllers\Controller;
use App\IATI\Services\Dashboard\DashboardService;
use App\IATI\Services\Organization\OrganizationService;
use App\IATI\Services\User\UserService;
use App\IATI\Traits\DateRangeResolverTrait;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use JsonException;

/**
 * Class SuperAdminController.
 */
class SuperAdminController extends Controller
{
    use DateRangeResolverTrait;

    /**
     * SuperAdminController Constructor.
     *
     * @param OrganizationService $organizationService
     * @param UserService $userService
     * @param DashboardService $dashboardService
     */
    public function __construct(public OrganizationService $organizationService, public UserService $userService, public DashboardService $dashboardService)
    {
        //
    }

    /**
     * Returns super-admin page for viewing all organisations.
     *
     * @return Application|Factory|View|JsonResponse
     */
    public function listOrganizations(): View|Factory|JsonResponse|Application
    {
        try {
            $country = getCodeList('Country', 'Activity', code: false, filterDeprecated: true);
            $setupCompleteness = [
                'Publishers_with_complete_setup' => 'Publishers with complete setup',
                'Publishers_settings_not_completed' => 'Publishers setting not completed',
                'Default_values_not_completed' => 'Default values not completed',
                'Both_publishing_settings_and_default_values_not_completed' => 'Both publishing settings and default values not completed',
            ];
            $registrationType = Enums::ORGANIZATION_REGISTRATION_METHOD;
            $publisherType = getCodeList('OrganizationType', 'Organization', filterDeprecated: true);
            $dataLicense = getCodeList('DataLicense', 'Activity', code: false, filterDeprecated: true);
            $oldestDates = $this->dashboardService->getOldestDate('publisher');

            return view('superadmin.organisationsList', compact('country', 'setupCompleteness', 'registrationType', 'publisherType', 'dataLicense', 'oldestDates'));
        } catch (Exception $e) {
            logger()->error($e);

            return response()->json(
                ['success' => false, 'error' => 'Error has occurred while fetching organisations.']
            );
        }
    }

    /**
     * Returns organizations in paginated format.
     *
     * @param Request $request
     * @param int $page
     *
     * @return JsonResponse
     */
    public function getPaginatedOrganizations(Request $request, int $page = 1): JsonResponse
    {
        try {
            $organizations = $this->organizationService->getPaginatedOrganizations(
                $page,
                $this->sanitizeRequest($request)
            );

            return response()->json([
                'success' => true,
                'message' => 'Organizations fetched successfully',
                'data' => $organizations,
            ]);
        } catch (Exception $e) {
            logger()->error($e);

            return response()->json(['success' => false, 'message' => 'Error occurred while fetching the data']);
        }
    }

    /**
     * Sanitizes the request for removing code injections.
     *
     * @param $request
     *
     * @return array
     *
     * @throws JsonException
     */
    public function sanitizeRequest($request): array
    {
        $tableConfig = getTableConfig('organisation');
        $queryParams = [];

        if (!empty($request->get('q'))) {
            $queryParams['q'] = $request->get('q');
        }

        if (in_array($request->get('orderBy'), $tableConfig['orderBy'], true)) {
            $queryParams['orderBy'] = $request->get('orderBy');

            if (in_array($request->get('direction'), $tableConfig['direction'], true)) {
                $queryParams['direction'] = $request->get('direction');
            }
        }

        list($startDateString, $endDateString, $column) = $this->resolveDateRangeFromRequest($request);
        $queryParams['date_column'] = $column;

        if ($startDateString && $endDateString) {
            list($queryParams['start_date'], $queryParams['end_date']) = $this->resolveCustomRangeParams($startDateString, $endDateString);
        }

        if (array_intersect_key($request->toArray(), $tableConfig['filters'])) {
            foreach ($tableConfig['filters'] as $filterKey => $filterMode) {
                $value = Arr::get($request, $filterKey, false);

                if ($value) {
                    if ($filterMode === 'multiple') {
                        $exploded = explode(',', $value);
                        $queryParams['filters'][$filterKey] = $exploded;
                    } else {
                        $queryParams['filters'][$filterKey] = $value;
                    }
                }
            }
        }

        return $queryParams;
    }

    /**
     * Allows super-admin to masquerade as a user of an organization.
     *
     * @param $userId
     *
     * @return JsonResponse
     */
    public function proxyOrganization($userId): JsonResponse
    {
        try {
            if (isSuperAdmin()) {
                $user = $this->userService->getUser($userId);

                if ($user) {
                    auth()->loginUsingId($userId);

                    return response()->json(['success' => true, 'message' => 'Proxy successful.']);
                }
            }

            return response()->json(['success' => false, 'message' => 'Error occurred while trying to proxy']);
        } catch (Exception $e) {
            logger()->error($e);

            return response()->json(['success' => false, 'message' => 'Error occurred while trying to proxy']);
        }
    }

    /**
     * Returns System Version UI.
     *
     * @return View|Factory|JsonResponse|Application
     */
    public function listSystemVersion(): View|Factory|JsonResponse|Application
    {
        try {
            $composerPackageDetails = json_decode(file_get_contents('../app_versions/composer_package_versions.json'));

            $phpDependencies = $composerPackageDetails->package_details->installed ?? '';
            $nodeDependencies = json_decode(file_get_contents('../app_versions/npm_package_versions.json'), true) ?? '';
            $version = json_decode(file_get_contents('../app_versions/current_versions.json')) ?? '';
            $latestVersion = json_decode(file_get_contents('../app_versions/latest_versions.json')) ?? '';

            $version->composer = $composerPackageDetails->composer_version;

            return view(
                'superadmin.systemVersion',
                compact(
                    'phpDependencies',
                    'nodeDependencies',
                    'version',
                    'latestVersion'
                )
            );
        } catch (Exception $e) {
            logger()->error($e);

            return redirect('listOrganizations')->with('error', 'Failed opening System Version page.');
        }
    }
}
