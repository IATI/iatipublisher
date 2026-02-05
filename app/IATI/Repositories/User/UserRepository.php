<?php

declare(strict_types=1);

namespace App\IATI\Repositories\User;

use App\IATI\Models\User\Role;
use App\IATI\Models\User\User;
use App\IATI\Repositories\Repository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

/**
 * Class UserRepository.
 */
class UserRepository extends Repository
{
    /**
     * Returns user model.
     *
     * @return string
     */
    public function getModel(): string
    {
        return User::class;
    }

    /**
     * @return void
     */
    public function sendEmail(): void
    {
        User::sendEmail();
    }

    /**
     * @param $user_id
     *
     * @return bool
     */
    public function getStatus($user_id): bool
    {
        return (bool) ($this->model->find($user_id))['email_verified_at'];
    }

    /**
     * Returns user if found.
     *
     * @param $id
     *
     * @return object
     */
    public function getUser($id): object
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Get paginated users.
     *
     * @param $page
     * @param $queryParams
     *
     * @return LengthAwarePaginator
     */
    public function getPaginatedUsers($page, $queryParams): LengthAwarePaginator
    {
        $query = $this->model
            ->leftJoin('organizations', 'organizations.id', 'users.organization_id')
            ->join('roles', 'roles.id', 'users.role_id')
            ->select(
                DB::raw('
                    users.id,
                    full_name,
                    publisher_name,
                    email,
                    users.status,
                    roles.role,
                    role_id,
                    users.created_at,
                    last_logged_in,
                    email_verified_at
               ')
            );

        if (!empty($queryParams)) {
            $query = $this->filterUsers($query, $queryParams);
        }

        return $query->paginate(10, ['*'], 'users', $page);
    }

    /**
     * Returns paginated User count of organizations.
     *
     * @param $page
     * @param $queryParam
     *
     * @return LengthAwarePaginator
     */
    public function getUserCountByOrganization($page, $queryParam): LengthAwarePaginator
    {
        $roles = Role::all()->pluck('id', 'role');
        $query = $this->model::query()
            ->join('organizations', 'organizations.id', '=', 'users.organization_id')
            ->selectRaw("organizations.id as organization_id,
                         COALESCE(organizations.name->0->>'narrative', organizations.publisher_name) as organisation,
                         count(Case when users.role_id = " . $roles['admin'] . ' and users.organization_id = organizations.id then 1 end) as admin_user_count,
                         count(Case when users.status = true and users.organization_id = organizations.id then 1 end) as active_user_count,
                         count(Case when users.status = false and users.organization_id = organizations.id then 1 end) as deactivated_user_count,
                         count(organizations.id) as total_user_count')
            ->groupBy('organizations.id', 'organisation');

        $direction = Arr::get($queryParam, 'direction', 'asc');
        $orderBy = Arr::get($queryParam, 'orderBy', 'organisation');

        if ($orderBy === 'organisation') {
            $query->orderBy('organisation', $direction);
        } else {
            $orderBy = $orderBy . '_user_count';
            $query->orderBy($orderBy, $direction);
        }

        return $query->paginate(10, ['*'], 'user', $page);
    }

    /**
     * Download csv with user data.
     *
     * @param $queryParams
     *
     * @return Collection|array
     */
    public function getUserDownloadData($queryParams): Collection|array
    {
        $query = $this->model
            ->leftJoin('organizations', 'organizations.id', 'users.organization_id')
            ->join('roles', 'roles.id', 'users.role_id');

        if (!empty($queryParams)) {
            $query = $this->filterUsers($query, $queryParams);
        }

        return $query->get(['full_name', 'name->0->narrative as publisher_name', 'email', 'roles.role', 'users.created_at', 'users.id as id']);
    }

    /**
     * Create user filter query.
     *
     * @param $query
     * @param $queryParams
     *
     * @return Builder
     */
    public function filterUsers($query, $queryParams): Builder
    {
        $orderBy = 'users.created_at';
        $direction = 'desc';

        if (!empty($queryParams['organization_id'])) {
            $query = $query->whereIn('organization_id', Arr::get($queryParams, 'organization_id', []));
        }

        if (array_key_exists('users', $queryParams)) {
            $query = $query->whereIn('users.id', Arr::get($queryParams, 'users'));
        }

        if (array_key_exists('status', $queryParams)) {
            $query = $query->whereIn('users.status', Arr::get($queryParams, 'status'));
        }

        if (!empty($queryParams['role'])) {
            $query = $query->whereIn('role_id', Arr::get($queryParams, 'role', []));
        }

        if (array_key_exists('q', $queryParams)) {
            $query = $query->where(function ($query) use ($queryParams) {
                $query->where('full_name', 'ilike', '%' . Arr::get($queryParams, 'q') . '%')
                    ->orWhere('email', 'ilike', '%' . Arr::get($queryParams, 'q') . '%');
            });
        }

        if (Arr::get($queryParams, 'startDate', false) && Arr::get($queryParams, 'endDate', false)) {
            $filterType = 'users.' . Arr::get($queryParams, 'dateType', 'created_at');

            $query->whereDate($filterType, '>=', $queryParams['startDate'])
                ->whereDate($filterType, '<=', $queryParams['endDate']);
        }
        $orderBy = Arr::get($queryParams, 'orderBy', $orderBy);
        $direction = Arr::get($queryParams, 'direction', $direction);
        $query = $query->whereNull('deleted_at');

        return $this->applyOrderBy($query, $orderBy, $direction);
    }

    /**
     * Returns user count for user dashboard.
     *
     * @return Collection
     */
    public function getUserCounts(): Collection
    {
        $query = $this->model
            ->selectRaw('users.role_id, users.status, roles.role, count(*)')
            ->leftJoin('roles', 'users.role_id', '=', 'roles.id')
            ->whereNull('users.deleted_at')
            ->groupBy(['users.role_id', 'users.status', 'roles.role']);

        return $query->get();
    }

    /**
     * Get data in range.
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param string $column
     *
     * @return Collection|array
     */
    public function getUserDataForDownloadOnly(Carbon $startDate, Carbon $endDate, string $column): Collection|array
    {
        $superadminId = App::make(RoleRepository::class)->getSuperAdminId();

        return $this->model->select(DB::raw("users.email as user_identifier,
        case when organizations.name::text!='' and ((organizations.name->>0)::json)->>'narrative'!=null then ((organizations.name->>0)::json)->>'narrative' else 'Untitled' end as publisher_name,
        users.email,
        users.created_at,
        users.last_logged_in,
        roles.role,
        case when users.status = true then 'active' else 'inactive' end as user_status"))
            ->leftJoin('roles', 'users.role_id', '=', 'roles.id')
            ->leftJoin('organizations', 'users.organization_id', '=', 'organizations.id')
            ->whereDate("users.{$column}", '>=', $startDate)
            ->whereDate("users.{$column}", '<=', $endDate)
            ->whereNot('users.role_id', $superadminId)
            ->get();
    }

    /**
     * Overriding base repository function to better suit user repository.
     *
     * {@inheritDoc}
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param $interval
     * @param $column
     *
     * @return array
     */
    public function getTimeSeriesDataGroupedByInterval(Carbon $startDate, Carbon $endDate, $interval, $column): array
    {
        $superadminId = App::make(RoleRepository::class)->getSuperAdminId();

        $dateFormat = match ($interval) {
            'day'=>'YYYY-MM-DD',
            'month'=>'YYYY-MM',
            default=>'YYYY'
        };

        $query = $this->model
            ->select(DB::raw("TO_CHAR($column, '" . $dateFormat . "') AS date_string"), DB::raw('COUNT(*) AS count_value'))
            ->whereDate($column, '>=', $startDate)
            ->whereDate($column, '<=', $endDate)
            ->whereNull('deleted_at')
            ->where('role_id', '!=', $superadminId);

        return $query->groupBy('date_string')
            ->pluck('count_value', 'date_string')
            ->toArray();
    }

    /**
     * Apply order by.
     *
     * @param $query
     * @param $orderBy
     * @param $direction
     *
     * @return Builder
     */
    private function applyOrderBy($query, $orderBy, $direction):Builder
    {
        $nullableColumnWithType = [
            'publisher_name' =>'string',
            'last_logged_in' =>'date',
        ];

        if (array_key_exists($orderBy, $nullableColumnWithType)) {
            $valuesForHandlingSortingForNullableColumns = [
                'date'  =>['asc'=>'9999-01-31 00:00:00', 'desc'=>'1753-01-01 00:00:00'],
                'string'=>['asc'=>'zzz', 'desc'=>''],
            ];

            $sortingFieldType = Arr::get($nullableColumnWithType, $orderBy);
            $fixedValue = Arr::get($valuesForHandlingSortingForNullableColumns, "$sortingFieldType.$direction", 'string.asc');

            if ($sortingFieldType === 'string') {
                return $query->orderByRaw("CASE WHEN $orderBy IS NULL OR $orderBy = '' THEN '$fixedValue' ELSE $orderBy END $direction")
                 ->orderBy('users.id', $direction);
            }

            return $query->orderByRaw("CASE WHEN $orderBy IS NULL THEN '$fixedValue' ELSE $orderBy END $direction")
                 ->orderBy('users.id', $direction);
        }

        return $query->orderBy($orderBy, $direction)->orderBy('users.id', $direction);
    }
}
