<?php

declare(strict_types=1);

namespace App\IATI\Repositories\Activity;

use App\IATI\Models\Activity\Result;
use App\IATI\Repositories\Repository;
use App\IATI\Traits\FillDefaultValuesTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;

/**
 * Class ResultRepository.
 */
class ResultRepository extends Repository
{
    use FillDefaultValuesTrait;

    /**
     * @return string
     */
    public function getModel(): string
    {
        return Result::class;
    }

    /**
     * Returns results of specific activity.
     *
     * @param $activityId
     *
     * @return array
     */
    public function getActivityResults($activityId): array
    {
        return $this->model->where('activity_id', $activityId)->get()->toArray();
    }

    /**
     * Returns paginated results.
     *
     * @param int   $activityId
     * @param array $queryParams
     * @param int   $page
     *
     * @return Collection|LengthAwarePaginator
     */
    public function getPaginatedResult(int $activityId, array $queryParams, int $page = 1): Collection|LengthAwarePaginator
    {
        $query = $this->model->where('activity_id', $activityId);

        $searchParam = Arr::get($queryParams, 'query', false);
        $filterApplied = Arr::get($queryParams, 'filterBy', 'all');
        $orderBy = Arr::get($queryParams, 'orderBy', 'created_at');
        $direction = strtoupper(Arr::get($queryParams, 'direction', 'ASC'));
        $limit = Arr::get($queryParams, 'limit', 10);

        $query = $query->when($filterApplied != 'all', function ($query) use ($filterApplied) {
            return $query->whereRaw("(result->>'type') = '$filterApplied'");
        });

        if ($searchParam) {
            $query->whereRaw("
                EXISTS (
                        SELECT 1
                        FROM json_array_elements(result->'title') AS title_array,
                             json_array_elements(title_array->'narrative') AS narrative_array
                        WHERE narrative_array->>'narrative' ILIKE ?
                    )
                ", ["%{$searchParam}%"]);
        }

        /**
         * Sorting by name will be handled in ResultService as sorting by requires activity default language.
         * Default language cannot be easily queried here. So it's simpler to sort outside.
         */
        $query = $query
            ->when($orderBy === 'type', function ($query) use ($direction) {
                return $query->orderByRaw("(result->>'type')::NUMERIC $direction, created_at DESC");
            })
            ->when(!in_array($orderBy, ['name', 'type']), function ($query) {
                return $query->orderBy('created_at', 'desc');
            });

        return $query->orderBy('id', 'desc')->paginate($limit, ['*'], 'result', $page);
    }

    /**
     * Returns specific result of specific activity.
     *
     * @param int $activityId
     * @param int $id
     *
     * @return mixed
     */
    public function getActivityResult(int $activityId, int $id): mixed
    {
        return $this->model->where(['activity_id'=>$activityId, 'id'=>$id])->first();
    }

    /**
     * @param int $id
     *
     * @return array
     */
    public function getResult(int $id): array
    {
        return $this->model->where(['id'=>$id])->first()->toArray();
    }

    /**
     * Returns all results with its indicators and their periods for a particular activity.
     *
     * @param $activityId
     *
     * @return Collection
     */
    public function getActivityResultsWithIndicatorsAndPeriods($activityId): Collection
    {
        return $this->model->where('activity_id', $activityId)->orderBy('created_at', 'DESC')->with('indicators', 'indicators.periods')->limit(4)->get();
    }

    /**
     * Returns result with its indicators and their periods.
     *
     * @param $resultId
     * @param $activityId
     *
     * @return Model|null
     */
    public function getResultWithIndicatorAndPeriod($resultId, $activityId): ?Model
    {
        return $this->model->where('id', $resultId)->where('activity_id', $activityId)->with(['indicators', 'indicators.periods'])->first();
    }

    /**
     * Returns result with its indicators.
     *
     * @param $resultId
     *
     * @return array
     */
    public function getResultWithIndicator($resultId): array
    {
        return $this->model->where('id', $resultId)->with(['indicators'])->first()->toArray();
    }

    /**
     * Deletes result with activity id.
     *
     * @param $activity_id
     *
     * @return mixed
     */
    public function deleteResult($activity_id): mixed
    {
        $results = $this->model->where('activity_id', $activity_id)->get();

        if (!empty($results)) {
            return $results->each->delete();
        }

        return false;
    }

    /**
     * Inserts multiple results.
     *
     * @param $results
     *
     * @return bool
     */
    public function insert($results): bool
    {
        return $this->model->insert($results);
    }

    /**
     * get result with indicator from an array of result ids.
     *
     * @param array $resultIds
     *
     * @return Builder
     */
    public function getResultsWithIndicatorQueryToDownload(array $resultIds): Builder
    {
        return $this->model->has('indicators')->with('indicators')->whereIn('id', $resultIds);
    }

    public function getActivityByResultId($resultId)
    {
        $result = $this->model->where('id', $resultId)->first();

        return $result->activity;
    }

    public function bulkDeleteResults(array $resultIds): bool
    {
        return (bool) $this->model->whereIn('id', $resultIds)->delete();
    }

    public function getResultCountStats(int $activityId): array
    {
        return $this->model->where('activity_id', $activityId)
            ->selectRaw("
                COUNT(*) as all,
                COUNT(CASE WHEN (result->>'type')::INTEGER = 1 THEN 1 END) as output,
                COUNT(CASE WHEN (result->>'type')::INTEGER = 2 THEN 2 END) as outcome,
                COUNT(CASE WHEN (result->>'type')::INTEGER = 3 THEN 3 END) as impact,
                COUNT(CASE WHEN (result->>'type')::INTEGER = 9 THEN 9 END) as other
            ")
            ->first()
            ->toArray();
    }

    /**
     * Delete results where activity_id in array.
     *
     * @param array $activityIds
     *
     * @return bool
     */
    public function bulkDeleteResultsByActivityIds(array $activityIds):bool
    {
        return (bool) $this->model->whereIn('activity_id', $activityIds)->delete();
    }
}
