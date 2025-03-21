<?php

declare(strict_types=1);

namespace App\IATI\Repositories\Activity;

use App\Constants\Enums;
use App\IATI\Models\Activity\Activity;
use App\IATI\Models\Organization\Organization;
use App\IATI\Repositories\Repository;
use App\IATI\Services\Activity\ActivityService;
use App\IATI\Services\ElementCompleteService;
use App\IATI\Traits\FillDefaultValuesTrait;
use Auth;
use Carbon\Carbon;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use JsonException;

/**
 * Class ActivityRepository.
 */
class ActivityRepository extends Repository
{
    use FillDefaultValuesTrait;

    /**
     * Returns activity model.
     *
     * @return string
     */
    public function getModel(): string
    {
        return Activity::class;
    }

    /**
     * Returns activity identifiers used by an organization.
     *
     * @param $organizationId
     *
     * @return Collection
     */
    public function getActivityIdentifiersForOrganization($organizationId): Collection
    {
        return $this->model->where('org_id', $organizationId)->get(['iati_identifier']);
    }

    /**
     * Returns activity identifiers used by an organization.
     *
     * @param       $organizationId
     * @param array $queryParams
     * @param int $page
     *
     * @return Collection|LengthAwarePaginator
     */
    public function getActivityForOrganization($organizationId, array $queryParams = [], int $page = 1): Collection|LengthAwarePaginator
    {
        $whereSql = '1=1';
        $bindParams = [];

        if (!empty($organizationId)) {
            $whereSql .= " AND org_id=$organizationId";
        }

        if (array_key_exists('query', $queryParams) && (!empty($queryParams['query']) || $queryParams['query'] === '0')) {
            $query = $queryParams['query'];
            $innerSql = 'select id, json_array_elements(title) title_array from activities';

            if (!empty($organizationId)) {
                $innerSql . " org_id=$organizationId";
            }

            $whereSql .= " AND ((iati_identifier->>'activity_identifier')::text ilike ? or id in (select x1.id from ($innerSql)x1 where (x1.title_array->>'narrative')::text ilike ?))";
            $bindParams[] = "%$query%";
            $bindParams[] = "%$query%";
        }

        $orderBy = 'updated_at';
        $direction = 'desc';
        $limit = '25';

        if (array_key_exists('orderBy', $queryParams) && !empty($queryParams['orderBy'])) {
            $orderBy = $queryParams['orderBy'];

            if (array_key_exists('direction', $queryParams) && !empty($queryParams['direction'])) {
                $direction = $queryParams['direction'];
            }
        }

        if (array_key_exists('limit', $queryParams) && !empty($queryParams['limit'])) {
            $limit = $queryParams['limit'];
        }

        return $this->model->whereRaw($whereSql, $bindParams)
            ->orderBy($orderBy, $direction)
            ->orderBy('id', $direction)
            ->when(isset($queryParams['filterBy']), fn ($query) => $this->filterByPublishStatus($query, $queryParams['filterBy']))
            ->paginate($limit, ['*'], 'activity', $page);
    }

    /**
     * @param Builder $query
     * @param $filterByStatus
     *
     * @return Builder
     */
    public function filterByPublishStatus(Builder $query, $filterByStatus): Builder
    {
        return match ($filterByStatus) {
            'published'              => $query->where('linked_to_iati', true)->where('status', 'published'),
            'ready_for_republishing' => $query->where('linked_to_iati', true)->where('status', 'draft'),
            'draft'                  => $query->where('linked_to_iati', false)->where('status', 'draft'),
            default                  => $query,
        };
    }

    /**
     * Updates status column of activity row.
     *
     * @param $activity
     * @param $status
     * @param $linkedToIati
     *
     * @return bool
     */
    public function updatePublishedStatus($activity, $status, $linkedToIati): bool
    {
        return (bool) $this->model->where('id', $activity->id)->update([
            'status' => $status,
            'linked_to_iati' => $linkedToIati,
            'has_ever_been_published' => true,
        ]);
    }

    /**
     * Deletes desired activity.
     *
     * @param Activity $activity
     *
     * @return bool
     */
    public function deleteActivity(Activity $activity): bool
    {
        return $activity->delete();
    }

    /**
     * Sets activity status to draft.
     *
     * @param $activity_id
     *
     * @return void
     */
    public function resetActivityWorkflow($activity_id): void
    {
        $this->model->whereId($activity_id)->update(['status' => 'draft']);
    }

    /**
     * Returns activities having given ids.
     *
     * @param $activityIds
     *
     * @return object
     */
    public function getActivitiesHavingIds($activityIds): object
    {
        return $this->model->whereIn('id', $activityIds)->where('org_id', auth()->user()->organization->id)->where('status', 'draft')->with(['transactions', 'results.indicators.periods'])->get();
    }

    /**
     * Provides activity identifiers.
     *
     * @param $orgId
     *
     * @return Collection|array
     */
    public function getActivityIdentifiers($orgId): Collection|array
    {
        return $this->model->where('org_id', $orgId)->get(['id', 'iati_identifier->activity_identifier as identifier']);
    }

    /**
     * Provides activity identifiers.
     *
     * @param $orgId
     *
     * @return Collection|array
     */
    public function getActivities($orgId): Collection|array
    {
        return $this->model->where('org_id', $orgId)->get();
    }

    /**
     * Create activity from xml data.
     *
     * @param      $activity_id
     * @param array $mappedActivity
     *
     * @return Builder|Model|bool
     *
     * @throws JsonException | BindingResolutionException
     */
    public function importXmlActivities($activity_id, array $mappedActivity): Builder|Model|bool
    {
        $mappedActivity = json_decode(json_encode($mappedActivity, JSON_THROW_ON_ERROR | 512), true, 512, JSON_THROW_ON_ERROR);
        $collaborationType = $this->autoFillSettingsValue($this->getSingleValuedActivityElement($mappedActivity, 'collaboration_type'), 'default_collaboration_type');
        $defaultFlowType = $this->autoFillSettingsValue($this->getSingleValuedActivityElement($mappedActivity, 'default_flow_type'), 'default_flow_type');
        $defaultFinanceType = $this->autoFillSettingsValue($this->getSingleValuedActivityElement($mappedActivity, 'default_finance_type'), 'default_finance_type');
        $defaultTiedStatus = $this->autoFillSettingsValue($this->getSingleValuedActivityElement($mappedActivity, 'default_tied_status'), 'default_tied_status');
        $defaultAidType = $this->autoFillDefaultAidTypeSettingValue($this->getActivityElement($mappedActivity, 'default_aid_type'));

        $data = [
            'iati_identifier' => trimStringValueInArray($mappedActivity['iati_identifier']),
            'title' => $this->getActivityElement($mappedActivity, 'title'),
            'description' => $this->getActivityElement($mappedActivity, 'description'),
            'activity_status' => $this->getSingleValuedActivityElement($mappedActivity, 'activity_status'),
            'activity_date' => $this->getActivityElement($mappedActivity, 'activity_date'),
            'participating_org' => $this->getActivityElement($mappedActivity, 'participating_org'),
            'recipient_country' => $this->getActivityElement($mappedActivity, 'recipient_country'),
            'recipient_region' => $this->getActivityElement($mappedActivity, 'recipient_region'),
            'sector' => $this->getActivityElement($mappedActivity, 'sector'),
            'location' => $this->getActivityElement($mappedActivity, 'location'),
            'conditions' => $this->getActivityElement($mappedActivity, 'conditions', false),
            'document_link' => $this->getActivityElement($mappedActivity, 'document_link'),
            'country_budget_items' => Arr::get($this->getActivityElement($mappedActivity, 'country_budget_items'), '0', null),
            'planned_disbursement' => $this->getActivityElement($mappedActivity, 'planned_disbursement'),
            'humanitarian_scope' => $this->getActivityElement($mappedActivity, 'humanitarian_scope'),
            'other_identifier' => $this->getActivityElement($mappedActivity, 'other_identifier'),
            'legacy_data' => $this->getActivityElement($mappedActivity, 'legacy_data'),
            'tag' => $this->getActivityElement($mappedActivity, 'tag'),
            'org_id' => Auth::user()->organization->id,
            'policy_marker' => $this->getActivityElement($mappedActivity, 'policy_marker'),
            'budget' => $this->getActivityElement($mappedActivity, 'budget'),
            'activity_scope' => $this->getSingleValuedActivityElement($mappedActivity, 'activity_scope'),
            'collaboration_type' => !empty($collaborationType) ? (int) $collaborationType : null,
            'capital_spend' => $this->getSingleValuedActivityElement($mappedActivity, 'capital_spend'),
            'default_flow_type' => !empty($defaultFlowType) ? (int) $defaultFlowType : null,
            'default_finance_type' => !empty($defaultFinanceType) ? (int) $defaultFinanceType : null,
            'default_aid_type' => $defaultAidType,
            'default_tied_status' => !empty($defaultTiedStatus) ? (int) $defaultTiedStatus : null,
            'contact_info' => $this->getActivityElement($mappedActivity, 'contact_info'),
            'related_activity' => $this->getActivityElement($mappedActivity, 'related_activity'),
            'default_field_values' => $mappedActivity['default_field_values'],
            'reporting_org' => $this->getActivityElement($mappedActivity, 'reporting_org'),
            'upload_medium' => Enums::UPLOAD_TYPE['xml'],
        ];
        $data['deprecated_status_map'] = refreshActivityDeprecationStatusMap($data);

        if ($activity_id) {
            return $this->update($activity_id, $data, true);
        }

        return $this->store($data);
    }

    /**
     * Returns activity element.
     *
     * @param      $activity
     * @param      $type
     * @param bool $get_values
     *
     * @return array|null
     */
    public function getActivityElement($activity, $type, bool $get_values = true): ?array
    {
        if (isset($activity[$type]) && !empty($activity[$type])) {
            return $get_values ? array_values((array) $activity[$type]) : (array) $activity[$type];
        }

        return null;
    }

    /**
     * Returns int valued element.
     *
     * @param      $activity
     * @param      $type
     *
     * @return int|float|null
     */
    public function getSingleValuedActivityElement($activity, $type): int|float|null
    {
        $data = Arr::get($activity, $type, '');

        if ($data !== '' && !is_array($data)) {
            if (is_string($data) && $type == 'capital_spend') {
                $data = (float) $data;
            } elseif (is_string($data)) {
                $data = (int) $data;
            }

            return $data;
        }

        return null;
    }

    /**
     * Create Activity from the csv data.
     *
     * @param $activityData
     *
     * @return Model
     *
     * @throws BindingResolutionException
     */
    public function createActivity($activityData): Model
    {
        $collaborationType = $this->autoFillSettingsValue($this->getSingleValuedActivityElement($activityData, 'collaboration_type'), 'default_collaboration_type');
        $defaultFlowType = $this->autoFillSettingsValue($this->getSingleValuedActivityElement($activityData, 'default_flow_type'), 'default_flow_type');
        $defaultFinanceType = $this->autoFillSettingsValue($this->getSingleValuedActivityElement($activityData, 'default_finance_type'), 'default_finance_type');
        $defaultTiedStatus = $this->autoFillSettingsValue($this->getSingleValuedActivityElement($activityData, 'default_tied_status'), 'default_tied_status');
        $defaultAidType = $this->autoFillDefaultAidTypeSettingValue($this->getActivityElement($activityData, 'default_aid_type'));

        $activity = [
            'iati_identifier' => trimStringValueInArray($activityData['identifier']),
            'title' => $this->getActivityElement($activityData, 'title'),
            'description' => $this->getActivityElement($activityData, 'description'),
            'activity_status' => $this->getSingleValuedActivityElement($activityData, 'activity_status'),
            'activity_date' => $this->getActivityElement($activityData, 'activity_date'),
            'participating_org' => $this->getActivityElement($activityData, 'participating_organization'),
            'recipient_country' => $this->getActivityElement($activityData, 'recipient_country'),
            'recipient_region' => $this->getActivityElement($activityData, 'recipient_region'),
            'sector' => $this->getActivityElement($activityData, 'sector'),
            'org_id' => $activityData['organization_id'],
            'policy_marker' => $this->getActivityElement($activityData, 'policy_marker'),
            'budget' => $this->getActivityElement($activityData, 'budget'),
            'activity_scope' => $this->getSingleValuedActivityElement($activityData, 'activity_scope'),
            'default_field_values' => $activityData['default_field_values'] ?? [],
            'contact_info' => $this->getActivityElement($activityData, 'contact_info'),
            'related_activity' => $this->getActivityElement($activityData, 'related_activity'),
            'other_identifier' => $this->getActivityElement($activityData, 'other_identifier'),
            'tag' => $this->getActivityElement($activityData, 'tag'),
            'collaboration_type' => !empty($collaborationType) ? (int) $collaborationType : null,
            'default_flow_type' => !empty($defaultFlowType) ? (int) $defaultFlowType : null,
            'default_finance_type' => !empty($defaultFinanceType) ? (int) $defaultFinanceType : null,
            'default_tied_status' => !empty($defaultTiedStatus) ? (int) $defaultTiedStatus : null,
            'default_aid_type' => $defaultAidType,
            'country_budget_items' => Arr::get($activityData, 'country_budget_item', null),
            'humanitarian_scope' => $this->getActivityElement($activityData, 'humanitarian_scope'),
            'capital_spend' => $this->getSingleValuedActivityElement($activityData, 'capital_spend'),
            'conditions' => Arr::get($activityData, 'condition', null),
            'legacy_data' => $this->getActivityElement($activityData, 'legacy_data'),
            'document_link' => $this->getActivityElement($activityData, 'document_link'),
            'location' => $this->getActivityElement($activityData, 'location'),
            'planned_disbursement' => $this->getActivityElement($activityData, 'planned_disbursement'),
            'reporting_org' => $this->getActivityElement($activityData, 'reporting_organization'),
            'upload_medium' => Enums::UPLOAD_TYPE['csv'],
        ];
        $activity['deprecation_status_map'] = refreshActivityDeprecationStatusMap($activity) ?? [];

        return $this->store($activity);
    }

    /**
     * Populates organization settings value if csv value is empty.
     *
     * @param $csvValue
     * @param $defaultElementName
     *
     * @return int|string|null
     *
     * @throws BindingResolutionException
     */
    public function autoFillSettingsValue($csvValue, $defaultElementName): int|string|null
    {
        if (!empty($csvValue)) {
            return $csvValue;
        }

        $defaultValues = $this->getOrganizationSettingDefaultValues();

        return isset($defaultValues[$defaultElementName]) && !empty($defaultValues[$defaultElementName]) ? $defaultValues[$defaultElementName] : null;
    }

    /**
     * Populates default aid type value from organization setting if empty.
     *
     * @param $csvValue
     *
     * @return array[]|null
     *
     * @throws BindingResolutionException
     */
    public function autoFillDefaultAidTypeSettingValue($csvValue): ?array
    {
        if (!empty($csvValue)) {
            return $csvValue;
        }

        $defaultValues = $this->getOrganizationSettingDefaultValues();

        if (empty($defaultValues) || empty($defaultValues['default_aid_type'])) {
            return null;
        }

        return [
            [
                'default_aid_type_vocabulary' => '1',
                'default_aid_type' => $defaultValues['default_aid_type'],
            ],
        ];
    }

    /**
     * returns organization settings value.
     *
     * @return array|null
     *
     * @throws BindingResolutionException
     */
    public function getOrganizationSettingDefaultValues(): ?array
    {
        return app()->make(ActivityService::class)->getDefaultValues();
    }

    /**
     * Only updates data provided in activity csv.
     *
     * @param       $id
     * @param array $activityData
     *
     * @return bool
     */
    public function updateActivity($id, array $activityData): bool
    {
        $activity = [
            'iati_identifier' => trimStringValueInArray($activityData['identifier']),
            'title' => $this->getActivityElement($activityData, 'title'),
            'description' => $this->getActivityElement($activityData, 'description'),
            'activity_status' => $this->getSingleValuedActivityElement($activityData, 'activity_status'),
            'activity_date' => $this->getActivityElement($activityData, 'activity_date'),
            'participating_org' => $this->getActivityElement($activityData, 'participating_organization'),
            'recipient_country' => $this->getActivityElement($activityData, 'recipient_country'),
            'recipient_region' => $this->getActivityElement($activityData, 'recipient_region'),
            'sector' => $this->getActivityElement($activityData, 'sector'),
            'org_id' => $activityData['organization_id'],
            'policy_marker' => $this->getActivityElement($activityData, 'policy_marker'),
            'budget' => $this->getActivityElement($activityData, 'budget'),
            'activity_scope' => $this->getSingleValuedActivityElement($activityData, 'activity_scope'),
            'default_field_values' => $activityData['default_field_values'] ?? [],
            'contact_info' => $this->getActivityElement($activityData, 'contact_info'),
            'related_activity' => $this->getActivityElement($activityData, 'related_activity'),
            'other_identifier' => $this->getActivityElement($activityData, 'other_identifier'),
            'tag' => $this->getActivityElement($activityData, 'tag'),
            'collaboration_type' => $this->getSingleValuedActivityElement($activityData, 'collaboration_type'),
            'default_flow_type' => $this->getSingleValuedActivityElement($activityData, 'default_flow_type'),
            'default_finance_type' => $this->getSingleValuedActivityElement($activityData, 'default_finance_type'),
            'default_tied_status' => $this->getSingleValuedActivityElement($activityData, 'default_tied_status'),
            'default_aid_type' => $this->getActivityElement($activityData, 'default_aid_type'),
            'country_budget_items' => Arr::get($activityData, 'country_budget_item', null),
            'humanitarian_scope' => $this->getActivityElement($activityData, 'humanitarian_scope'),
            'capital_spend' => $this->getSingleValuedActivityElement($activityData, 'capital_spend'),
            'conditions' => Arr::get($activityData, 'condition', null),
            'legacy_data' => $this->getActivityElement($activityData, 'legacy_data'),
            'document_link' => $this->getActivityElement($activityData, 'document_link'),
            'location' => $this->getActivityElement($activityData, 'location'),
            'planned_disbursement' => $this->getActivityElement($activityData, 'planned_disbursement'),
            'reporting_org' => $this->getActivityElement($activityData, 'reporting_organization'),
        ];
        $activity['deprecation_status_map'] = refreshActivityDeprecationStatusMap($activity) ?? [];

        return $this->update($id, $activity, true);
    }

    /**
     * Returns activity.
     *
     * @param $org_id
     * @param $identifier
     *
     * @return mixed
     */
    public function getActivityWithIdentifier($org_id, $identifier): mixed
    {
        $activities = $this->model->where('org_id', $org_id)->whereJsonContains('iati_identifier->activity_identifier', trim((string) $identifier))->get();

        if ($activities) {
            return $activities->first();
        }

        return null;
    }

    /**
     * Returns activities having given ids for downloading.
     *
     * @param $activityIds
     *
     * @return object
     */
    public function getActivitiesToDownload($activityIds): object
    {
        return $this->model->whereIn('id', $activityIds)->where('org_id', auth()->user()->organization->id)->with(['transactions', 'results', 'organization.settings'])->orderBy('updated_at', 'desc')->orderBy('id', 'desc')->get();
    }

    /**
     * Returns activities query having given ids for downloading.
     *
     * @param $activityIds
     * @param $authUser
     *
     * @return object
     */
    public function getActivitiesQueryToDownload($activityIds, $authUser): object
    {
        return $this->model->whereIn('id', $activityIds)->where('org_id', $authUser['organization']['id'])->with(['transactions', 'results', 'organization.settings'])->orderBy('updated_at', 'desc')->orderBy('id', 'desc');
    }

    /**
     * Returns all activities of organization.
     *
     * @param $organizationId
     * @param $queryParams
     *
     * @return object
     */
    public function getAllActivitiesToDownload($organizationId, $queryParams): object
    {
        return $this->getAllActivitiesQueryToDownload($organizationId, $queryParams)->get();
    }

    /**
     * Updates ReportingOrg of activity with that of organisation.
     *
     * @param $id
     * @param $reportingOrg
     *
     * @return int
     */
    public function syncReportingOrg($id, $reportingOrg): int
    {
        $activitiesCount = $this->model->where('org_id', $id)->count();

        if ($activitiesCount > 0) {
            $this->model->where('org_id', $id)->update(['reporting_org->0->ref' => $reportingOrg['ref'] ?? '']);
            $this->model->where('org_id', $id)->update(['reporting_org->0->type' => $reportingOrg['type'] ?? '']);

            return $this->model->where('org_id', $id)->update([
                'reporting_org->0->narrative' => $reportingOrg['narrative'] ?? '',
                'status' => 'draft',
            ]);
        }

        return 1;
    }

    /**
     * Updates specific key inside reporting_org (json field).
     *
     * @param $id
     * @param $key
     * @param $data
     *
     * @return int
     */
    public function updateReportingOrg($id, $key, $data): int
    {
        return $this->model->where('id', $id)->update(["reporting_org->0->{$key}" => $data]);
    }

    /**
     * Returns activities by org ids.
     * @param array $orgIds
     *
     * @return Collection|array
     */
    public function getActivitiesByOrgIds(array $orgIds): Collection|array
    {
        return $this->model->whereIn('org_id', $orgIds)->get();
    }

    /*
     *
     * Returns activities with result belonging to an organization.
     *
     * @param $organizationId
     * @param $queryParams
     *
     * @return object
     */
    public function getAllActivitiesQueryToDownload($organizationId, $queryParams): object
    {
        $whereSql = '1=1';
        $bindParams = [];
        $whereSql .= " AND org_id=$organizationId";

        if (array_key_exists('query', $queryParams) && (!empty($queryParams['query']) || $queryParams['query'] === '0')) {
            $query = $queryParams['query'];
            $innerSql = 'select id, json_array_elements(title) title_array from activities';
            $innerSql . " org_id=$organizationId";
            $whereSql .= " AND ((iati_identifier->>'activity_identifier')::text ilike ? or id in (select x1.id from ($innerSql)x1 where (x1.title_array->>'narrative')::text ilike ?))";
            $bindParams[] = "%$query%";
            $bindParams[] = "%$query%";
        }

        $orderBy = 'updated_at';
        $direction = 'desc';

        if (array_key_exists('orderBy', $queryParams) && !empty($queryParams['orderBy'])) {
            $orderBy = $queryParams['orderBy'];

            if (array_key_exists('direction', $queryParams) && !empty($queryParams['direction'])) {
                $direction = $queryParams['direction'];
            }
        }

        return $this->model->with(['transactions', 'results', 'organization.settings'])
            ->whereRaw($whereSql, $bindParams)
            ->orderBy($orderBy, $direction)
            ->orderBy('id', $direction);
    }

    /**
     * Return all result, indicator and period codes to download.
     *
     * @param $organizationId
     * @param $activitiesId
     *
     * @return Collection
     */
    public function getCodesToDownload($organizationId, $activitiesId): Collection
    {
        $query = $this->model->with(['results'])->select('id', 'iati_identifier')->where('org_id', $organizationId);

        if (!empty($activitiesId)) {
            $query = $query->whereIn('id', $activitiesId);
        }

        return $query->get();
    }

    /**
     * returns with Nested relation.
     *
     * @param $activityId
     *
     * @return Model|null
     */
    public function getActivitityWithRelationsById($activityId): ?Model
    {
        return $this->model->with('results.indicators.periods', 'transactions')->where('id', $activityId)->first();
    }

    /*
     * Updates specific key inside reporting_org (json field).
     *
     * @return Model|null
     */
    public function getLastUpdatedActivity(): ?Model
    {
        return $this->model->select('id', 'org_id')->latest('updated_at')->first();
    }

    /**
     * Applies filter to the activity query.
     *
     * @param $query
     * @param $queryParams
     *
     * @return Builder
     */
    protected function filterActivity($query, $queryParams): Builder
    {
        $filteredQuery = $query;

        if ($queryParams['start_date'] && $queryParams['end_date']) {
            $date_from = Carbon::parse($queryParams['start_date'])->startOfDay();
            $date_to = Carbon::parse($queryParams['end_date'])->endOfDay();

            $filteredQuery = $query->where('created_at', '>=', $date_from)
                ->where('created_at', '<=', $date_to);
        }

        return $filteredQuery;
    }

    /**
     * Return activity count for activity dashboard graph.
     *
     * @param $queryParams
     *
     * @return array
     *
     * @throws \Exception
     */
    public function getActivityCount($queryParams): array
    {
        $query = $this->model;
        $format = $queryParams['range'] ?? 'Y-m-d';
        $startDate = date_create($queryParams['start_date']);
        $endDate = date_create($queryParams['end_date']);
        $data = [];

        if ($queryParams) {
            $query = $this->filterActivity($query, $queryParams);
        }

        $activityCount = $query->get()->groupBy(
            function ($q) use ($format) {
                return $q->created_at->format($format);
            }
        )->map(fn ($d) => count($d));

        $period = new \DatePeriod($startDate, new \DateInterval(sprintf('P1%s', $queryParams['period'])), $endDate);
        $data['count'] = 0;

        foreach ($period as $date) {
            $data['graph'][$date->format('Y-m-d')] = Arr::get($activityCount, $date->format($format), 0);
            $data['count'] += $data['graph'][$date->format('Y-m-d')];
        }

        $data['graph'][$queryParams['end_date']] = Arr::get($activityCount, $endDate->format($format), 0);
        $data['count'] += $data['graph'][$queryParams['end_date']];

        return $data;
    }

    /**
     * Returns activity by type.
     *
     * @param $queryParams
     * @param $type
     *
     * @return array
     */
    public function getActivityBy($queryParams, $type): array
    {
        $query = $this->model->select(DB::raw('count(*) as count, ' . $type));

        if ($queryParams) {
            $query = $this->filterActivity($query, $queryParams);
        }

        return $query->groupBy($type)->pluck('count', $type)->toArray();
    }

    /**
     * Return activity status based on publish.
     *
     * @param $queryParams
     * @param false|int $orgId
     *
     * @return array
     */
    public function getActivityStatus($queryParams, false|int $orgId = false): array
    {
        $query = $this->model->select(DB::raw('count(*) as count,status,linked_to_iati'));

        if ($queryParams) {
            $query = $this->filterActivity($query, $queryParams);
        }

        if ($orgId) {
            $query->where('org_id', $orgId);
        }

        return $query->groupBy('status', 'linked_to_iati')->get()->toArray();
    }

    /**
     * Returns array with complete status of activities.
     *
     * @param $queryParams
     *
     * @return array
     */
    public function getCompleteStatus($queryParams): array
    {
        $completeQuery = $this->model->select(DB::raw('count(*) as count, status'))->where('complete_percentage', 100);
        $incompleteQuery = $this->model->select(DB::raw('count(*) as count, status'))->where('complete_percentage', '!=', 100);

        if ($queryParams) {
            $completeQuery = $this->filterActivity($completeQuery, $queryParams);
            $incompleteQuery = $this->filterActivity($incompleteQuery, $queryParams);
        }

        return [
            'complete' => $completeQuery->groupBy('status')->get()->toArray(),
            'incomplete' => $incompleteQuery->groupBy('status')->get()->toArray(),
        ];
    }

    /**
     * @param $organization
     *
     * @return bool
     *
     * @throws JsonException
     */
    public function syncActivityIdentifierForNeverPublishedActivities($organization): bool
    {
        $activities = $this->model
            ->where('org_id', $organization->id)
            ->where('has_ever_been_published', false)
            ->get();

        if (count($activities) > 0) {
            foreach ($activities as $index => $activity) {
                $rawActivity = $activity->getAttributes();
                $rawActivity['iati_identifier'] = json_decode($rawActivity['iati_identifier'], true, 512, JSON_THROW_ON_ERROR);
                $activityIdentifier = $rawActivity['iati_identifier']['activity_identifier'];

                $rawActivity['iati_identifier'] = [
                    'activity_identifier'             => $activityIdentifier,
                    'iati_identifier_text'            => $organization->identifier . '-' . $activityIdentifier,
                    'present_organization_identifier' => $organization->identifier,
                ];

                $rawActivity['iati_identifier'] = json_encode($rawActivity['iati_identifier'], JSON_THROW_ON_ERROR);

                $activities[$index] = $rawActivity;
            }

            return (bool) $this->model->upsert($activities->toArray(), 'id', ['iati_identifier']);
        }

        return true;
    }

    /**
     * Returns array of data for activities dashboard download.
     *
     * @param $queryParams
     *
     * @return array
     */
    public function getActivitiesDashboardDownload($queryParams): array
    {
        return $this->model->select(
            DB::raw(
                "
            (iati_identifier->>'activity_identifier') as identifier,
             title->0->>'narrative' as activity_title,
             name->0->>'narrative' as organization,
             case when linked_to_iati and activities.status='draft'
             then 'published recently'
             else activities.status
             end as case,
             upload_medium,
             complete_percentage,
             activities.created_at,
             activities.updated_at
         "
            )
        )->leftJoin('organizations', 'organizations.id', 'activities.org_id')
            ->whereDate('activities.created_at', '>=', $queryParams['start_date'])
            ->whereDate('activities.created_at', '<=', $queryParams['end_date'])
            ->get()->toArray();
    }

    /**
     * @param Organization $organization
     * @param array $appendableOtherIdentifier
     *
     * @return bool
     *
     * @throws BindingResolutionException
     * @throws JsonException
     */
    public function syncOtherIdentifierOfOrganizationActivities(Organization $organization, array $appendableOtherIdentifier): bool
    {
        /** @var ElementCompleteService $elementCompleteService */
        $elementCompleteService = app()->make(ElementCompleteService::class);

        $activities = $this->model
            ->where('org_id', $organization->id)
            ->get();

        if (count($activities) > 0) {
            foreach ($activities as $index => $activity) {
                $rawActivity = $activity->getAttributes();

                if ($rawActivity['other_identifier']) {
                    $rawActivity['other_identifier'] = json_decode($rawActivity['other_identifier'], true, 512, JSON_THROW_ON_ERROR);
                } else {
                    $rawActivity['other_identifier'] = [];
                }

                $rawActivity['other_identifier'][] = $appendableOtherIdentifier;
                $rawActivity['other_identifier'] = json_encode($rawActivity['other_identifier'], JSON_THROW_ON_ERROR);

                $elementStatus = $activity['element_status'];
                $activity['other_identifier'] = json_decode($rawActivity['other_identifier'], false, 512, JSON_THROW_ON_ERROR);
                $elementStatus['other_identifier'] = $elementCompleteService->isOtherIdentifierElementCompleted($activity);

                $rawActivity['element_status'] = json_encode($elementStatus, JSON_THROW_ON_ERROR);
                $activities[$index] = $rawActivity;
            }

            return (bool) $this->model->upsert($activities->toArray(), 'id', ['other_identifier', 'element_status']);
        }

        return true;
    }

    /**
     * Count the number of non-published activities with the given ids.
     *
     * @param $activityIds
     *
     * @return int
     */
    public function checkNonPublishedActivities($activityIds): int
    {
        return $this->model
            ->whereIn('id', $activityIds)
            ->where('status', '!=', 'published')
            ->count();
    }
}
