<?php

declare(strict_types=1);

namespace App\IATI\Repositories\Organization;

use App\IATI\Models\Organization\Organization;
use App\IATI\Models\Setting\Setting;
use App\IATI\Models\User\Role;
use App\IATI\Repositories\Repository;
use App\IATI\Traits\FillDefaultValuesTrait;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Class OrganizationRepository.
 */
class OrganizationRepository extends Repository
{
    use FillDefaultValuesTrait;

    /**
     * Return Organization model.
     *
     * @return string
     */
    public function getModel(): string
    {
        return Organization::class;
    }

    /**
     * Creates new organization.
     *
     * @param array $data
     *
     * @return Builder|Model|mixed
     */
    public function createOrganization(array $data): mixed
    {
        return $this->model->updateOrCreate(['publisher_id' => $data['publisher_id']], $data);
    }

    /**
     * Returns organization object.
     *
     * @param $id
     *
     * @return Model
     */
    public function getOrganizationData($id): Organization
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Updates status column of activity row.
     *
     * @param $organization
     * @param $status
     * @param $alreadyPublished
     *
     * @return bool
     */
    public function updatePublishedStatus($organization, $status, $alreadyPublished): bool
    {
        $organization->status = $status;
        $organization->is_published = $alreadyPublished;

        return $organization->save();
    }

    /**
     * Returns organizations in paginated format.
     *
     * @param $page
     * @param $queryParams
     *
     * @return null|LengthAwarePaginator
     */
    public function getPaginatedOrganizations($page, $queryParams): ?LengthAwarePaginator
    {
        $adminRoleId = app(Role::class)->getOrganizationAdminId();
        $whereSql = '1=1';
        $organizations = $this->model->selectRaw(
            'organizations.id,
            organizations.name,
            organizations.country,
            organizations.created_at,
            organizations.publisher_type,
            organizations.data_license,
            organizations.registration_type,
            MAX(usr.id) AS usr_id,
            MAX(usr.last_logged_in) AS last_logged_in'
        )
            ->leftJoin('users AS usr', 'usr.organization_id', '=', 'organizations.id')
            ->whereRaw($whereSql)
            ->withCount('allActivities')
            ->with([
                'user' => function ($user) use ($adminRoleId) {
                    return $user->where('role_id', $adminRoleId)
                        ->where('status', 1)
                        ->whereNull('deleted_at')
                        ->min('created_at');
                },
            ])
            ->with('latestUpdatedActivity')
            ->with('settings');

        $organizations = $this->applyDateRange($organizations, $queryParams);

        if (Arr::get($queryParams, 'q', false)) {
            $organizations = $this->applySearch($organizations, $queryParams['q']);
        }

        if (Arr::get($queryParams, 'filters', false)) {
            $organizations = $this->applyFilters($organizations, $queryParams['filters']);
        }

        $organizations = $organizations->groupBy('organizations.id');

        return $this->applyOrderBy($organizations, $queryParams, $page);
    }

    /**
     * Apply order by for sorting.
     *
     * @param mixed $organizations
     * @param $queryParams
     * @param $page
     *
     * @return LengthAwarePaginator
     */
    private function applyOrderBy(mixed $organizations, $queryParams, $page):LengthAwarePaginator
    {
        $orderBy = Arr::get($queryParams, 'orderBy', 'organizations.updated_at');
        $direction = Arr::get($queryParams, 'direction', 'desc');

        if (in_array($orderBy, ['country', 'data_license', 'name'])) {
            $mappedOrderBy = $this->orderByMap($orderBy);

            if ($direction == 'asc') {
                return $organizations
                    ->orderByRaw("CASE WHEN $mappedOrderBy IS NULL OR $mappedOrderBy = '' THEN 'zzz' ELSE $mappedOrderBy END asc")
                    ->paginate(10, ['*'], 'organization', $page);
            }

            return $organizations
                ->orderByRaw("CASE WHEN $mappedOrderBy IS NULL OR $mappedOrderBy = '' THEN '' ELSE $mappedOrderBy END desc")
                ->paginate(10, ['*'], 'organization', $page);
        }

        if ($orderBy === 'registered_on') {
            return $organizations->orderBy('created_at', $direction)
                ->paginate(10, ['*'], 'organization', $page);
        }

        if ($orderBy === 'last_logged_in') {
            if ($direction == 'asc') {
                return $organizations
                    ->orderByRaw("CASE WHEN MAX(usr.last_logged_in) IS NULL THEN '9999-01-31 00:00:00' ELSE MAX(usr.last_logged_in) END asc")
                    ->paginate(10, ['*'], 'organization', $page);
            }

            return $organizations
                ->orderByRaw("CASE WHEN MAX(usr.last_logged_in) IS NULL THEN '1753-01-01 00:00:00' ELSE MAX(usr.last_logged_in) END desc")
                ->paginate(10, ['*'], 'organization', $page);
        }

        return $organizations->orderBy($orderBy, $direction)->orderBy('organizations.id', 'desc')
            ->paginate(10, ['*'], 'organization', $page);
    }

    /**
     * Apply where query for search.
     *
     * @param $organizations
     * @param $searchString
     *
     * @return Builder
     */
    private function applySearch($organizations, $searchString):Builder
    {
        $searchString = strtolower($searchString);
        $searchString = '%' . $searchString . '%';

        $organizations->where(function ($query) use ($searchString) {
            $query->whereRaw("LOWER(organizations.name->0->>'narrative') LIKE ?", [$searchString])
                ->orWhereRaw('LOWER(organizations.publisher_name) LIKE ?', [$searchString])
                ->orWhereHas('user', function ($user) use ($searchString) {
                    $user->whereRaw('LOWER(email) LIKE ?', [$searchString]);
                });
        });

        return $organizations;
    }

    /**
     * Apply where query for  date range.
     *
     * @param $organizations
     * @param $queryParams
     *
     * @return Builder
     */
    private function applyDateRange($organizations, $queryParams):Builder
    {
        $dateColumn = Arr::get($queryParams, 'date_column', 'created_at');
        $startDate = Arr::get($queryParams, 'start_date', ($this->getOldestData()?->created_at ?? false));
        $endDate = Arr::get($queryParams, 'end_date', now()->endOfDay());

        if ($dateColumn === 'last_logged_in') {
            $organizations->whereHas('latestLoggedInUser', function ($user) use ($startDate, $endDate) {
                $user->whereDate('last_logged_in', '>=', $startDate)->whereDate('last_logged_in', '<=', $endDate);
            });
        } else {
            $organizations->whereDate("organizations.$dateColumn", '>=', $startDate)
                ->whereDate("organizations.$dateColumn", '<=', $endDate);
        }

        return $organizations;
    }

    /**
     * Applies filters to organization listing page.
     *
     * @param mixed $organizations
     * @param array $filters
     *
     * @return Builder
     */
    private function applyFilters(mixed $organizations, array $filters = []): Builder
    {
        $tableConfig = getTableConfig('organisation');
        $filterMode = $tableConfig['filters'];
        $queryMap = $this->getCompletenessMap();

        foreach ($filters as $filterName => $filterValue) {
            if ($filterName !== 'completeness') {
                if (Arr::get($filterMode, $filterName) === 'single') {
                    $organizations->where($filterName, $filterValue);
                } else {
                    $organizations->whereIn($filterName, $filterValue);
                }
            } else {
                $organizations
                    ->leftJoin('settings', 'settings.organization_id', 'organizations.id')
                    ->whereRaw($queryMap[$filterValue]);
            }
        }

        return $organizations;
    }

    /**
     * Returns list of organization name with their id.
     *
     * @return Collection
     */
    public function pluckAllOrganizations(): Collection
    {
        return $this->model->select(DB::raw("case when name::text!='' and ((name->>0)::json)->>'narrative'!=null then ((name->>0)::json)->>'narrative' else publisher_name end as pub_name,id"))->get()->pluck('pub_name', 'id');
    }

    /**
     * Override base repository update method.
     *
     * @param $data
     *
     * @inheritDoc
     *
     * @return Model|void
     */
    public function update($id, array $data): bool
    {
        $orgId = auth()->user()->organization->id;
        $defaultValues = Setting::where('organization_id', $orgId)->first()?->default_values ?? [];

        if (!empty($defaultValues)) {
            $data = $this->populateDefaultFields($data, $defaultValues);
        }

        if (isset($data['name'])) {
            $data['name'] = $data['name']['narrative'];
        }

        return $this->model->find($id)->update($data);
    }

    /**
     * Returns organization by publisher id.
     *
     * @param $publisherId
     *
     * @return object|null
     */
    public function getOrganizationByPublisherId($publisherId): ?object
    {
        return $this->model->where('publisher_id', $publisherId)->with(['settings', 'users', 'activities'])->first();
    }

    /**
     * Returns Organizations by publisher ids.
     *
     * @param array $publisherIds
     *
     * @return array|Collection
     */
    public function getOrganizationByPublisherIds(array $publisherIds): Collection|array
    {
        return $this->model->whereIn('publisher_id', $publisherIds)->get();
    }

    /**
     * Applies filter to the publisher query.
     *
     * @param $query
     * @param $queryParams
     * @param null $type
     *
     * @return LengthAwarePaginator
     */
    protected function filterPublisher($query, $queryParams, $type = null): LengthAwarePaginator
    {
        $direction = 'desc';
        $orderBy = 'count';

        if (Arr::get($queryParams, 'start_date', false) && Arr::get($queryParams, 'end_date', false)) {
            $query = $query->whereDate('organizations.created_at', '>=', $queryParams['start_date'])
                ->whereDate('organizations.created_at', '<=', $queryParams['end_date']);
        }

        if ($type) {
            $orderBy = Arr::get($queryParams, 'orderBy', $orderBy);
            $direction = Arr::get($queryParams, 'direction', $direction);

            $query = $query->orderBy($orderBy, $direction)->groupBy($type);
        }

        return $query->paginate(10, ['*'], 'publisher', Arr::get($queryParams, 'page', 1));
    }

    /**
     * Return publisher stats for dashboard.
     *
     * @return array
     */
    public function getPublisherStats(): array
    {
        return [
            'totalCount' => $this->model->count(),
            'lastRegisteredPublisher' => $this->model->select('id', 'created_at', 'name')->latest('created_at')->first(),
            'inActivePublisher' => $this->model->with('latestLoggedInUser')
                ->whereHas('latestLoggedInUser', function (Builder $q) {
                    $q->where('last_logged_in', '<', Carbon::today()->subMonths(6));
                })
                ->orDoesntHave('latestLoggedInUser')
                ->count(),
        ];
    }

    /**
     * Get publisher by type.
     *
     * @param $queryParams
     * @param $type
     *
     * @return LengthAwarePaginator
     */
    public function getPublisherByPagination($queryParams, $type): LengthAwarePaginator
    {
        $query = $this->model->select(DB::raw('count(*) as count, ' . $type))->whereNotNull($type)->where($type, '<>', '');

        return $this->filterPublisher($query, $queryParams, $type);
    }

    /**
     * Get publisher by type.
     *
     * @param $queryParams
     * @param $type
     * @param int $page
     *
     * @return array
     */
    public function getPublisherBy($queryParams, $type, int $page = 1): array
    {
        $query = $this->model->select(DB::raw('count(*) as count, ' . $type))->whereNotNull($type)->where($type, '<>', '');

        if ($queryParams) {
            $query = $this->filterPublisher($query, $queryParams, $type);
        }

        return [
            $type = $query->pluck('count', $type),
        ];
    }

    /**
     * Returns array of publisher grouped by setup.
     *
     * @param $queryParams
     *
     * @return array
     */
    public function getPublisherBySetup($queryParams): array
    {
        $data = [];
        $queryMap = $this->getCompletenessMap();

        foreach ($queryMap as $completenessStatus => $completenessStatusQuery) {
            $publisher = $this->model->select(DB::raw('publisher_name, organizations.id'))
                ->leftJoin('settings', 'settings.organization_id', 'organizations.id');

            if (Arr::get($queryParams, 'start_date', false) && Arr::get($queryParams, 'end_date', false)) {
                $startDate = Carbon::parse($queryParams['start_date'])->startOfDay();
                $endDate = Carbon::parse($queryParams['end_date'])->endOfDay();
                $publisher->whereRaw("($completenessStatusQuery)
                 AND organizations.created_at >= '$startDate'
                 AND organizations.created_at <= '$endDate'");
            } else {
                $publisher->whereRaw($completenessStatusQuery);
            }

            $data[$completenessStatus] = $publisher->count();
        }

        if (!array_sum($data)) {
            return [];
        }

        return [
            'completeSetup' => [
                'count' => $data['Publishers_with_complete_setup'],
                'types' => [],
            ],
            'incompleteSetup' => [
                'count' => $data['Publishers_with_incomplete_setup'],
                'types' => [
                    'publisher' => $data['Publishers_settings_not_completed'],
                    'defaultValue' => $data['Default_values_not_completed'],
                ],
            ],
        ];
    }

    /**
     * Returns publishers grouped by date.
     *
     * @param $queryParams
     * @param $type
     *
     * @return array
     * @throws \Exception
     */
    public function getPublisherGroupedByDate($queryParams, $type): array
    {
        $query = $this->model;
        $format = $queryParams['range'] ?? 'Y-m-d';
        $startDate = date_create($queryParams['start_date']);
        $endDate = date_create($queryParams['end_date']);
        $data = [];

        if ($queryParams) {
            $query = $this->filterPublisher($query, $queryParams);
        }

        $publisherCount = $query->get()->groupBy(
            function ($q) use ($format) {
                return $q->created_at->format($format);
            }
        )->map(fn ($d) => count($d));

        $period = new \DatePeriod($startDate, new \DateInterval(sprintf('P1%s', $queryParams['period'])), $endDate);
        $data['count'] = 0;

        foreach ($period as $date) {
            $data['graph'][$date->format('Y-m-d')] = Arr::get($publisherCount, $date->format($format), 0);
            $data['count'] += $data['graph'][$date->format('Y-m-d')];
        }

        $data['graph'][$queryParams['end_date']] = Arr::get($publisherCount, $endDate->format($format), 0);
        $data['count'] += $data['graph'][$queryParams['end_date']];

        return $data;
    }

    /**
     * Returns count of publisher without activity.
     *
     * @return int
     */
    public function publisherWithoutActivity(): int
    {
        return $this->model->doesntHave('allActivities')->count();
    }

    /**
     * Organizations for dashboard download.
     *
     * @param $queryParams
     *
     * @return array
     */
    public function getOrganizationDashboardDownload($queryParams): array
    {
        $completenessMap = $this->getCompletenessMap();
        $statementForPublisher_info = 'case when ' . $completenessMap['Publishers_settings_not_completed'] . " then 'incomplete' else 'complete' end as publisher_info";
        $statementForDefault_values = 'case when ' . $completenessMap['Default_values_not_completed'] . " then 'incomplete' else 'complete' end as default_values";

        $query = $this->model->select(DB::raw("name->0->>'narrative' as organization,
        identifier,
        publisher_type,
        country,
        registration_type,
        data_license,
        $statementForPublisher_info,
        $statementForDefault_values,
        organizations.created_at,
        organizations.updated_at
         "))->leftJoin('settings', 'organizations.id', 'settings.organization_id');

        if (isset($queryParams['start_date']) && isset($queryParams['end_date'])) {
            $query = $query->whereDate('organizations.created_at', '>=', $queryParams['start_date'])
                ->whereDate('organizations.created_at', '<=', $queryParams['end_date']);
        }

        return $query->get()->toArray();
    }

    /**
     * Returns where conditions for querying completeness based data.
     *
     * @return string[]
     */
    private function getCompletenessMap(): array
    {
        return [
            'Publishers_with_complete_setup' => "
                ((
                    (settings.publishing_info->>'publisher_id' IS NOT NULL AND settings.publishing_info->>'publisher_id' <> '') AND
                    (settings.publishing_info->>'api_token' IS NOT NULL AND settings.publishing_info->>'api_token' <> '') AND
                    (settings.publishing_info->>'publisher_verification' IS NOT NULL) AND
                    (CAST(settings.publishing_info->>'publisher_verification' as bool) = true) AND
                    (settings.publishing_info->>'token_verification' IS NOT NULL) AND
                    (CAST(settings.publishing_info->>'token_verification' as bool) = true)
                )
                AND
                (
                    (settings.default_values->>'default_currency' IS NOT NULL AND settings.default_values->>'default_currency' <> '') AND
                    (settings.default_values->>'default_language' IS NOT NULL AND settings.default_values->>'default_language' <> '') AND
                    (settings.activity_default_values->>'hierarchy' IS NOT NULL AND settings.activity_default_values->>'hierarchy' <> '') AND
                    (settings.activity_default_values->>'humanitarian' IS NOT NULL AND settings.activity_default_values->>'humanitarian' <> '') AND
                    (settings.activity_default_values->>'budget_not_provided' IS NOT NULL AND settings.activity_default_values->>'budget_not_provided' <> '')
                ))
            ",
            'Publishers_settings_not_completed' => "
                    ((settings.publishing_info->>'publisher_id' IS NULL OR settings.publishing_info->>'publisher_id' = '') OR
                    (settings.publishing_info->>'api_token' IS NULL OR settings.publishing_info->>'api_token' = '') OR
                    (settings.publishing_info->>'publisher_verification' IS NULL OR (CAST(settings.publishing_info->>'publisher_verification' as bool) = false)) OR
                    (settings.publishing_info->>'token_verification' IS NULL OR (CAST(settings.publishing_info->>'token_verification' as bool) = false)))
             ",
            'Default_values_not_completed' => "
                    ((settings.default_values->>'default_currency' IS NULL OR settings.default_values->>'default_currency' = '') OR
                    (settings.default_values->>'default_language' IS NULL OR settings.default_values->>'default_language' = '') OR
                    (settings.activity_default_values->>'hierarchy' IS NULL OR settings.activity_default_values->>'hierarchy' = '') OR
                    (settings.activity_default_values->>'humanitarian' IS NULL OR settings.activity_default_values->>'humanitarian' = '') OR
                    (settings.activity_default_values->>'budget_not_provided' IS NULL OR settings.activity_default_values->>'budget_not_provided' = ''))
            ",
            'Publishers_with_incomplete_setup' => "
                    (
                    (settings.publishing_info->>'publisher_id' IS NULL OR settings.publishing_info->>'publisher_id' = '') OR
                    (settings.publishing_info->>'api_token' IS NULL OR settings.publishing_info->>'api_token' = '') OR
                    (settings.publishing_info->>'publisher_verification' IS NULL) OR
                    (CAST(settings.publishing_info->>'publisher_verification' as bool) = false) OR
                    (settings.publishing_info->>'token_verification' IS NULL) OR
                    (CAST(settings.publishing_info->>'token_verification' as bool) = false) OR
                    (settings.default_values->>'default_currency' IS NULL OR settings.default_values->>'default_currency' = '') OR
                    (settings.default_values->>'default_language' IS NULL OR settings.default_values->>'default_language' = '') OR
                    (settings.activity_default_values->>'hierarchy' IS NULL OR settings.activity_default_values->>'hierarchy' = '') OR
                    (settings.activity_default_values->>'humanitarian' IS NULL OR settings.activity_default_values->>'humanitarian' = '') OR
                    (settings.activity_default_values->>'budget_not_provided' IS NULL OR settings.activity_default_values->>'budget_not_provided' = '')
                    )
            ",
        ];
    }

    /**
     * Returns mapped orderBy.
     *
     * @param $orderBy
     * @return string
     */
    public function orderByMap($orderBy): string
    {
        $orderByMap = [
            'country'     =>'country',
            'data_license'=>'data_license',
            'name'        =>"organizations.name->0->>'narrative'",
        ];

        return Arr::get($orderByMap, $orderBy);
    }
}
