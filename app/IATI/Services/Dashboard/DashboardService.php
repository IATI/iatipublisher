<?php

declare(strict_types=1);

namespace App\IATI\Services\Dashboard;

use App\IATI\Repositories\Activity\ActivityRepository;
use App\IATI\Repositories\Organization\OrganizationRepository;
use App\IATI\Repositories\User\UserRepository;
use App\IATI\Traits\DateRangeResolverTrait;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

/**
 * Class OrganizationService.
 */
class DashboardService
{
    use DateRangeResolverTrait;

    /**
     * @var OrganizationRepository
     */
    private OrganizationRepository $organizationRepo;

    /**
     * @var UserRepository
     */
    private UserRepository $userRepo;

    /**
     * @var ActivityRepository
     */
    private ActivityRepository $activityRepo;

    /**
     * UserService constructor.
     *
     * @param OrganizationRepository $organizationRepo
     * @param ActivityRepository $activityRepo
     * @param UserRepository $userRepo
     */
    public function __construct(OrganizationRepository $organizationRepo, ActivityRepository $activityRepo, UserRepository $userRepo)
    {
        $this->organizationRepo = $organizationRepo;
        $this->activityRepo = $activityRepo;
        $this->userRepo = $userRepo;
    }

    /**
     * Returns array containing publisher stats.
     *
     * @return array
     */
    public function getPublisherStats(): array
    {
        $publisherStat = $this->organizationRepo->getPublisherStats();
        $publisherStat['lastRegisteredPublisher']['user_id'] = $this->userRepo->findBy('organization_id', (int) $publisherStat['lastRegisteredPublisher']['id'])->id;

        return $publisherStat;
    }

    /**
     * Returns array containing Organisation Type.
     *
     * @param $queryParams
     * @param $type
     *
     * @return array|LengthAwarePaginator
     */
    public function getPublisherBy($queryParams, $type): array|LengthAwarePaginator
    {
        return $this->organizationRepo->getPublisherByPagination($queryParams, $type);
    }

    /**
     * Return publisher by setup.
     *
     * @param $queryParams
     *
     * @return array
     */
    public function getPublisherBySetup($queryParams): array
    {
        return $this->organizationRepo->getPublisherBySetup($queryParams);
    }

    /**
     * Return publisher grouped by date.
     *
     * @param $queryParams
     * @param $column
     *
     * @return array
     */
    public function getPublisherGroupedByDate($queryParams, $column): array
    {
        if (!$this->startAndEndDateAreSet($queryParams)) {
            list($startDate, $endDate) = $this->getStartAndEndDateForAlltime('activities');
            $queryParams['start_date'] = $startDate;
            $queryParams['end_date'] = $endDate;
        }

        list($startDate, $endDate, $groupBy) = $this->resolveCustomRangeParams($queryParams['start_date'], $queryParams['end_date']);

        $unformattedResults = $this->organizationRepo->getTimeSeriesDataGroupedByInterval($startDate, $endDate, $groupBy, $column);

        return $this->fillDataToMissingDates($startDate, $endDate, $unformattedResults, $groupBy);
    }

    /**
     * Returns activity stats.
     *
     * @param $queryParams
     *
     * @return array
     */
    public function getActivityStats($queryParams): array
    {
        $latestUpdatedActivity = ($this->activityRepo->find($this->activityRepo->getLastUpdatedActivity()->id))->toArray();
        $latestUpdateOrganization = ($this->activityRepo->getLastUpdatedActivity())->organization->toArray();

        $userId = $this->userRepo->applyConditions([
            ['organization_id', '=', $latestUpdateOrganization['id']],
            ['status', '=', true],
            ['deleted_at', '=', null],
        ])?->id;

        return [
            'totalCount' => $this->activityRepo->all()->count(),
            'lastUpdatedPublisher' => $latestUpdateOrganization,
            'lastUpdatedActivity'=> $latestUpdatedActivity,
            'userId'=> $userId,
            'publisherWithoutActivity' => $this->organizationRepo->publisherWithoutActivity(),
        ];
    }

    /**
     * Gets user count for user dashboard.
     *
     * @return array
     */
    public function getUserCounts(): array
    {
        $results = $this->userRepo->getUserCounts();
        $userCounts = [
            'iati_admin' => ['active' => 0, 'disabled' => 0, 'display'=>'IATI Admin', 'roleId'=>''],
            'admin' => ['active' => 0, 'disabled' => 0, 'display'=>'Organisation Admin', 'roleId'=>''],
        ];

        foreach ($results as $result) {
            if ($result->role !== 'superadmin') {
                $userCounts[$result->role][$result->status ? 'active' : 'disabled'] = $result->count;
                $userCounts[$result->role]['roleId'] = $result->role_id;
            }
        }

        return $userCounts;
    }

    /**
     * Returns data in range, grouped by (if param).
     *
     * @param $startDate
     * @param $endDate
     * @param string|false $groupBy
     * @param string $activeColumn
     *
     * @return Collection|array
     */
    public function getDataCountInRange($startDate, $endDate, bool|string $groupBy = false, string $activeColumn = 'created_at'): Collection|array
    {
        if ($groupBy) {
            return $this->userRepo->getTimeSeriesDataGroupedByInterval($startDate, $endDate, $groupBy, $activeColumn);
        }

        return $this->userRepo->getUserDataForDownloadOnly($startDate, $endDate, $activeColumn);
    }

    /**
     * Returns user data for report download.
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     *
     * @return array|Collection
     */
    public function getUserDataForReportDownload(Carbon $startDate, Carbon $endDate): array|Collection
    {
        return $this->getDataCountInRange($startDate, $endDate);
    }

    /**
     * Return activity count.
     *
     * @param $queryParams
     *
     * @return array
     */
    public function getActivityCount($queryParams): array
    {
        if (!$this->startAndEndDateAreSet($queryParams)) {
            list($startDate, $endDate) = $this->getStartAndEndDateForAlltime('activities');
            $queryParams['start_date'] = $startDate;
            $queryParams['end_date'] = $endDate;
        }

        list($startDate, $endDate, $groupBy) = $this->resolveCustomRangeParams($queryParams['start_date'], $queryParams['end_date']);

        $unformattedResults = $this->activityRepo->getTimeSeriesDataGroupedByInterval($startDate, $endDate, $groupBy, 'created_at');

        return $this->fillDataToMissingDates($startDate, $endDate, $unformattedResults, $groupBy);
    }

    /**
     * Return activity status.
     *
     * @param $queryParams
     *
     * @return array
     */
    public function getActivityStatus($queryParams): array
    {
        if (!$this->startAndEndDateAreSet($queryParams)) {
            list($startDate, $endDate) = $this->getStartAndEndDateForAlltime('activities');
            $queryParams['start_date'] = $startDate;
            $queryParams['end_date'] = $endDate;
        }

        $activityStatus = $this->activityRepo->getActivityStatus($queryParams);
        $processedStatus = [
            'Published' => 0,
            'Draft (Not Published)' => 0,
            'Draft (Need to republish)' => 0,
        ];

        foreach ($activityStatus as $status) {
            if ($status['status'] === 'published') {
                $processedStatus['Published'] = $status['count'];
            } elseif ($status['linked_to_iati'] === true) {
                $processedStatus['Draft (Need to republish)'] = $status['count'];
            } else {
                $processedStatus['Draft (Not Published)'] = $status['count'];
            }
        }

        $orderBy = Arr::get($queryParams, 'orderBy', false);
        $direction = Arr::get($queryParams, 'direction', 'asc');

        return $orderBy && $direction ? $this->sortAssocArray($processedStatus, $orderBy, $direction) : $processedStatus;
    }

    /**
     * Returns array containing Organisation Type.
     *
     * @param $queryParams
     * @param $type
     *
     * @return array
     */
    public function getActivityBy($queryParams, $type): array
    {
        if (!$this->startAndEndDateAreSet($queryParams)) {
            list($startDate, $endDate) = $this->getStartAndEndDateForAlltime('activities');
            $queryParams['start_date'] = $startDate;
            $queryParams['end_date'] = $endDate;
        }

        $activityStatus = $this->activityRepo->getActivityBy($queryParams, $type);
        $processedStatus = [
            'By importing via CSV' => 0,
            'By importing via XML' => 0,
            'By importing via XLS' => 0,
            'Manually' => 0,
        ];

        foreach ($activityStatus as $type => $count) {
            switch ($type) {
                case 'manual':
                    $processedStatus['Manually'] = $count;
                    break;
                case 'xls':
                    $processedStatus['By importing via XLS'] = $count;
                    break;
                case 'xml':
                    $processedStatus['By importing via XML'] = $count;
                    break;
                case 'csv':
                    $processedStatus['By importing via CSV'] = $count;
                    break;
            }
        }

        $orderBy = Arr::get($queryParams, 'orderBy', false);
        $direction = Arr::get($queryParams, 'direction', 'asc');

        return $orderBy && $direction ? $this->sortAssocArray($processedStatus, $orderBy, $direction) : $processedStatus;
    }

    /**
     * Return activities for download.
     *
     * @param $queryParams
     *
     * @return array
     */
    public function getAllActivitiesToDownload($queryParams): array
    {
        if (!$this->startAndEndDateAreSet($queryParams)) {
            list($startDate, $endDate) = $this->getStartAndEndDateForAlltime('activities');
            $queryParams['start_date'] = $startDate;
            $queryParams['end_date'] = $endDate;
        }

        return $this->activityRepo->getActivitiesDashboardDownload($queryParams);
    }

    /**
     * Returns completeness status of activity.
     *
     * @param $queryParams
     *
     * @return array
     */
    public function getActivityCompleteness($queryParams): array
    {
        if (!$this->startAndEndDateAreSet($queryParams)) {
            list($startDate, $endDate) = $this->getStartAndEndDateForAlltime('activities');
            $queryParams['start_date'] = $startDate;
            $queryParams['end_date'] = $endDate;
        }

        $activityCompleteness = $this->activityRepo->getCompleteStatus($queryParams);
        $processedCompleteness = [];

        foreach ($activityCompleteness as $completeness => $dataOfCompleteness) {
            $type = sprintf('Activities with %s core element data', $completeness);
            foreach ($dataOfCompleteness as $item) {
                if ($item['status'] == 'published') {
                    $processedCompleteness[$type]['published'] = $item['count'];
                } else {
                    $processedCompleteness[$type]['draft'] = $item['count'];
                }
            }

            $processedCompleteness[$type]['draft'] = Arr::get($processedCompleteness, "$type.draft", 0);
            $processedCompleteness[$type]['published'] = Arr::get($processedCompleteness, "$type.published", 0);
            $processedCompleteness[$type]['total'] = array_sum($processedCompleteness[$type]);
        }

        $orderBy = Arr::get($queryParams, 'orderBy', false);
        $direction = Arr::get($queryParams, 'direction', 'asc');

        return $orderBy && $direction ? $this->sortCompletenessStatus($processedCompleteness, $orderBy, $direction) : $processedCompleteness;
    }

    /**
     * Return organization data for download.
     *
     * @param $params
     *
     * @return array
     */
    public function getOrganizationToDownload($params): array
    {
        return $this->organizationRepo->getOrganizationDashboardDownload($params);
    }

    /**
     * Returns data for user dashboard table component.
     *
     * @param int   $page
     * @param array $queryParams
     *
     * @return LengthAwarePaginator
     */
    public function getUserCountByOrganization(int $page, array $queryParams): LengthAwarePaginator
    {
        return $this->userRepo->getUserCountByOrganization($page, $queryParams);
    }

    /**
     * Returns start and end data for All time filter.
     *
     * @param string $tablename
     *
     * @return array
     */
    public function getStartAndEndDateForAlltime(string $tablename = 'users'): array
    {
        $oldestData = match ($tablename) {
            'organizations'=> $this->organizationRepo->getOldestData(),
            'activities'=> $this->activityRepo->getOldestData(),
            default=> $this->userRepo->getOldestData(),
        };
        $startDate = $oldestData->created_at->format('Y-m-d');
        $endDate = today()->format('Y-m-d');

        return [$startDate, $endDate];
    }

    /**
     * Check if start and end date are set.
     *
     * @param $params
     *
     * @return bool
     */
    public function startAndEndDateAreSet($params): bool
    {
        return Arr::get($params, 'start_date', false) && Arr::get($params, 'end_date', false);
    }

    /**
     * Sets start date and end date.
     *
     * @param $request
     * @param array $params
     * @param string $tablename
     *
     * @return mixed
     */
    public function resolveStartDateAndEndDate($request, array $params, string $tablename): array
    {
        list($startDateString, $endDateString) = $this->resolveDateRangeFromRequest($request);

        if (!$startDateString || !$endDateString) {
            list($startDateString, $endDateString) = $this->getStartAndEndDateForAlltime($tablename);
            $params['start_date'] = $startDateString;
            $params['end_date'] = $endDateString;
        }

        return $params;
    }

    /**
     * Returns oldest created_at date.
     *
     * @param string $select
     *
     * @return array
     */
    public function getOldestDate(string $select = '*'): array
    {
        $userDate = $this->userRepo->getOldestData()?->created_at->format('Y-m-d') ?? '';
        $activityDate = $this->activityRepo->getOldestData()?->created_at->format('Y-m-d') ?? '';
        $publisherDate = $this->organizationRepo->getOldestData()?->created_at->format('Y-m-d') ?? '';

        return match ($select) {
            'publisher' => [$publisherDate],
            'activity' => [$activityDate],
            'user' => [$userDate],
            default => [
                'user' => $userDate,
                'activity' => $activityDate,
                'publisher' => $publisherDate,
            ],
        };
    }

    /**
     * Sorts assoc array by order by and direction.
     *
     * @param array $array
     * @param string $orderBy
     * @param string $direction
     *
     * @return array
     */
    public function sortAssocArray(array $array, string $orderBy, string $direction = 'asc'): array
    {
        $direction = strtolower($direction);

        if ($orderBy === 'count') {
            $direction === 'asc' ? asort($array) : arsort($array);
        } else {
            $direction === 'asc' ? ksort($array) : krsort($array);
        }

        return $array;
    }

    /**
     * Sort nested array by top level keys or children's value.
     *
     * @param array $array
     * @param string $orderBy
     * @param string $direction
     *
     * @return array
     */
    public function sortCompletenessStatus(array $array, string $orderBy, string $direction = 'asc'): array
    {
        $direction = strtolower($direction);

        if ($orderBy === 'completeness') {
            $direction === 'asc' ? ksort($array) : krsort($array);
        } else {
            uasort($array, function ($a, $b) use ($orderBy, $direction) {
                if (array_key_exists($orderBy, $a) && array_key_exists($orderBy, $b)) {
                    return $direction === 'asc' ? $a[$orderBy] <=> $b[$orderBy] : $b[$orderBy] <=> $a[$orderBy];
                }

                return 0;
            });
        }

        return $array;
    }
}
