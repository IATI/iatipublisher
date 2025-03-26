<?php

declare(strict_types=1);

namespace App\IATI\Services\Activity;

use App\Constants\CoreElements;
use App\IATI\Models\Activity\Activity;
use App\IATI\Repositories\Activity\ActivityRepository;
use App\IATI\Repositories\Organization\OrganizationRepository;
use App\IATI\Services\ElementCompleteService;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

/**
 * Class ActivityService.
 */
class ActivityService
{
    /**
     * @var ActivityRepository
     */
    protected ActivityRepository $activityRepository;

    /**
     * @var array
     */
    protected array $sectorFields = [
        'sector_vocabulary',
        'code',
        'category_code',
        'text',
        'sdg_goal',
        'sdg_target',
        'vocabulary_uri',
        'percentage',
    ];

    /**
     * @var array
     */
    protected array $recipientCountryFields = [
        'country_code',
        'percentage',
    ];

    /**
     * @var array
     */
    protected array $recipientRegionFields = [
        'region_vocabulary',
        'region_code',
        'custom_code',
        'vocabulary_uri',
        'percentage',
    ];

    /**
     * ActivityService constructor.
     *
     * @param ActivityRepository $activityRepository
     */
    public function __construct(ActivityRepository $activityRepository)
    {
        $this->activityRepository = $activityRepository;
    }

    /**
     * Returns all activities present in database.
     *
     * @param int $page
     * @param array $queryParams
     *
     * @return Collection|LengthAwarePaginator
     */
    public function getPaginatedActivities(int $page, array $queryParams): Collection|LengthAwarePaginator
    {
        $elementCompleteService = new ElementCompleteService();
        $activities = $this->activityRepository->getActivityForOrganization(
            Auth::user()->organization_id,
            $queryParams,
            $page
        );

        $orgReportingOrgStatus = Arr::get(\auth()->user()->organization, 'element_status.reporting_org', false);

        foreach ($activities as $idx => $activity) {
            $activities[$idx]['default_title_narrative'] = $activity->default_title_narrative;
            $elementStatus = $activity->element_status;
            $elementStatus['reporting_org'] = $orgReportingOrgStatus;

            $activity->setAttribute('coreCompleted', isCoreElementCompleted($elementStatus));
            $activity->setAttribute('core_completeness', $elementCompleteService->calculateCompletePercentage($elementStatus));
        }

        return $activities;
    }

    /**
     * Stores activity in activity table.
     *
     * @param $input
     *
     * @return Model
     */
    public function store($input): Model
    {
        $authUser = auth()->user();
        $activityIdentifierOnly = $input['activity_identifier'];
        $presentOrganizationIdentifier = $authUser->organization->identifier;
        $iatiIdentifierText = $presentOrganizationIdentifier . '-' . $activityIdentifierOnly;

        $activity_identifier = [
            'activity_identifier'             => $activityIdentifierOnly,
            'iati_identifier_text'            => $iatiIdentifierText,
            'present_organization_identifier' => $presentOrganizationIdentifier,
        ];

        $activity_title = [
            [
                'narrative' => $input['narrative'],
                'language'  => $input['language'],
            ],
        ];

        $defaultElementStatus = getDefaultElementStatus();
        $budgetNotProvided = $authUser->organization->settings->activity_default_values['budget_not_provided'] ?? '';
        $defaultElementStatus['budget'] = $budgetNotProvided === '1' || $defaultElementStatus['budget'];
        $defaultValues = $this->getDefaultValues();
        $defaultAidType = null;

        if (isset($defaultValues['default_aid_type']) && !empty($defaultValues['default_aid_type'])) {
            $defaultAidType = [
                [
                    'default_aid_type_vocabulary' => '1',
                    'default_aid_type' => $defaultValues['default_aid_type'],
                ],
            ];
        }

        $orgOldIdentifiers = $authUser->organization->old_identifiers;
        $activityOtherIdentifiers = null;

        if (!empty($orgOldIdentifiers)) {
            $activityOtherIdentifiers = [];

            foreach ($orgOldIdentifiers as $oldIdentifier) {
                $appendableOtherIdentifier = [
                    'reference'      => $oldIdentifier['identifier'],
                    'reference_type' => 'B1',
                    'owner_org'      => [
                        [
                            'ref'       => null,
                            'narrative' => [
                                [
                                    'narrative' => null,
                                    'language'  => null,
                                ],
                            ],
                        ],
                    ],
                ];

                $activityOtherIdentifiers[] = $appendableOtherIdentifier;
            }
        }

        return $this->activityRepository->store([
            'iati_identifier'      => $activity_identifier,
            'title'                => $activity_title,
            'collaboration_type'   => (int) Arr::get($defaultValues, 'default_collaboration_type') ?: null,
            'default_flow_type'    => (int) Arr::get($defaultValues, 'default_flow_type') ?: null,
            'default_finance_type' => (int) Arr::get($defaultValues, 'default_finance_type') ?: null,
            'default_aid_type'     => $defaultAidType,
            'default_tied_status'  => (int) Arr::get($defaultValues, 'default_tied_status') ?: null,
            'org_id'               => $authUser->organization_id,
            'element_status'       => $defaultElementStatus,
            'default_field_values' => $this->getDefaultValues(),
            'reporting_org'        => $authUser->organization->reporting_org,
            'other_identifier'     => $activityOtherIdentifiers,
        ]);
    }

    /**
     * @param $id
     * @param $element
     *
     * @return bool
     */
    public function deleteElement($id, $element): bool
    {
        return $this->activityRepository->update($id, [$element => null], isDeleteOperation: true, deleteElement: $element);
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
        return $this->activityRepository->getActivityIdentifiersForOrganization($organizationId);
    }

    /**
     * Returns activity identifiers used by an organization.
     *
     * @param $id
     *
     * @return object|null
     */
    public function getActivity($id): ?object
    {
        return $this->activityRepository->find($id);
    }

    /**
     * Returns required service file.
     *
     * @param $serviceName
     *
     * @return mixed
     */
    public function getService($serviceName): mixed
    {
        return app(sprintf("App\IATI\Services\Activity\%s", $serviceName));
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
        return $this->activityRepository->updatePublishedStatus($activity, $status, $linkedToIati);
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
        return $this->activityRepository->deleteActivity($activity);
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
        $this->activityRepository->resetActivityWorkflow($activity_id);
    }

    /**
     * Return activity core completeness in percentage.
     *
     * @param $activity
     *
     * @return float|int
     */
    public function activityPublishingProgress($activity): float|int
    {
        $core_elements = CoreElements::all();
        $completed_core_element_count = 0;

        foreach ($core_elements as $core_element) {
            if (
                array_key_exists(
                    $core_element,
                    $activity->element_status
                ) && $activity->element_status[$core_element]
            ) {
                $completed_core_element_count++;
            }
        }

        return ($completed_core_element_count / count($core_elements)) * 100;
    }

    /**
     * Returns default values for activity.
     *
     * @return array|null
     */
    public function getDefaultValues(): ?array
    {
        $organizationSettings = Auth::user()->organization->settings;

        if (!empty($organizationSettings)) {
            if ($organizationSettings->default_values && $organizationSettings->activity_default_values) {
                return array_merge(
                    $organizationSettings->default_values,
                    $organizationSettings->activity_default_values
                );
            }

            if ($organizationSettings->default_values) {
                return $organizationSettings->default_values;
            }

            if ($organizationSettings->activity_default_values) {
                return $organizationSettings->activity_default_values;
            }
        }

        return null;
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
        return $this->activityRepository->getActivitiesHavingIds($activityIds);
    }

    /**
     * Returns possible allocation % for Recipient Region.
     *
     * @param $activityId
     *
     * @return float
     */
    public function getPossibleAllocationPercentForRecipientRegion($activityId): float
    {
        $activity = $this->getActivity($activityId);
        $data = $activity->recipient_country;
        $total = 0;

        if (!empty($data)) {
            foreach ($data as $datum) {
                $total += (float) $datum['percentage'];
            }
        }

        return 100 - $total;
    }

    /**
     * Returns allocated recipient region percent for file upload.
     *
     * @param $recipientCountries
     *
     * @return float
     */
    public function getPossibleAllocationPercentForRecipientRegionFileUpload($recipientCountries): float
    {
        $data = $recipientCountries;
        $total = 0;

        if (!empty($data)) {
            foreach ($data as $datum) {
                $total += (float) $datum['percentage'];
            }
        }

        return 100 - $total;
    }

    /**
     * Returns possible allocation % for recipient country.
     *
     * @param $activityId
     *
     * @return float
     */
    public function getPossibleAllocationPercentForRecipientCountry($activityId): float
    {
        $activity = $this->getActivity($activityId);
        $data = $activity->recipient_region;
        $groupedRegion = [];

        if (!empty($data)) {
            foreach ($data as $datum) {
                if (array_key_exists($datum['region_vocabulary'], $groupedRegion)) {
                    $groupedRegion[$datum['region_vocabulary']] += (float) $datum['percentage'];
                } else {
                    $groupedRegion[$datum['region_vocabulary']] = (float) $datum['percentage'];
                }
            }

            if (!empty($groupedRegion)) {
                $groupedRegion = array_values($groupedRegion);

                return 100 - $groupedRegion[0];
            }
        }

        return 100.0;
    }

    /**
     * Checks if activity has recipient region.
     *
     * @param $activityId
     *
     * @return bool
     */
    public function hasRecipientRegionDefinedInActivity($activityId): bool
    {
        $activity = $this->getActivity($activityId)->toArray();

        return !empty($activity) && (array_key_exists('recipient_region', $activity) && !empty($activity['recipient_region']));
    }

    /**
     * Checks if activity has recipient country.
     *
     * @param $activityId
     *
     * @return bool
     */
    public function hasRecipientCountryDefinedInActivity($activityId): bool
    {
        $activity = $this->getActivity($activityId)->toArray();

        return !empty($activity) && (array_key_exists('recipient_country', $activity) && !empty($activity['recipient_country']));
    }

    /**
     * Checks if activity has sector.
     *
     * @param $activityId
     *
     * @return bool
     */
    public function hasSectorDefinedInActivity($activityId): bool
    {
        $activity = $this->getActivity($activityId)->toArray();

        return !empty($activity) && (array_key_exists('sector', $activity) && !empty($activity['sector']));
    }

    /**
     * Checks if recipient_country of specific activity has been defined in any one of the transactions.
     *
     * @param $activityId
     *
     * @return bool
     */
    public function hasRecipientCountryDefinedInTransactions($activityId): bool
    {
        $transactions = $this->getActivity($activityId)->transactions->toArray();

        if (!empty($transactions)) {
            foreach ($transactions as $transaction) {
                if (
                    !empty($transaction['transaction'])
                    && array_key_exists('recipient_country', $transaction['transaction'])
                    && !empty($transaction['transaction']['recipient_country'])
                    && !$this->isElementEmpty($transaction['transaction']['recipient_country'], 'recipientCountryFields')
                ) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Checks if recipient_region of specific activity has been defined in any one of the transactions.
     *
     * @param $activityId
     *
     * @return bool
     */
    public function hasRecipientRegionDefinedInTransactions($activityId): bool
    {
        $transactions = $this->getActivity($activityId)->transactions->toArray();

        if (!empty($transactions)) {
            foreach ($transactions as $transaction) {
                if (
                    !empty($transaction['transaction'])
                    && array_key_exists('recipient_region', $transaction['transaction'])
                    && !empty($transaction['transaction']['recipient_region'])
                    && !$this->isElementEmpty($transaction['transaction']['recipient_region'], 'recipientRegionFields')
                ) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Checks if sector of specific activity has been defined in any one of the transactions.
     *
     * @param $activityId
     *
     * @return bool
     */
    public function hasSectorDefinedInTransactions($activityId): bool
    {
        $transactions = $this->getActivity($activityId)->transactions->toArray();

        if (!empty($transactions)) {
            foreach ($transactions as $transaction) {
                if (
                    !empty($transaction['transaction'])
                    && array_key_exists('sector', $transaction['transaction'])
                    && !empty($transaction['transaction']['sector'])
                    && !$this->isElementEmpty($transaction['transaction']['sector'], 'sectorFields')
                ) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Checks if any budgets have same type, status, period start and period end.
     *
     * @param $budgets
     *
     * @return array
     */
    public function checkSameMultipleBudgets($budgets): array
    {
        $array = [];
        $ids = [];

        if (count($budgets)) {
            foreach ($budgets as $key => $budget) {
                $getBudgetBudgetType = Arr::get($budget, 'budget_type', '1');
                $getBudgetBudgetStatus = Arr::get($budget, 'budget_status', '1');
                $getBudgetPeriodStartDate = Arr::get($budget, 'period_start.0.date', '');
                $getBudgetPeriodEndDate = Arr::get($budget, 'period_end.0.date', '');
                $getArrayPeriodStartDate = Arr::get($array, $getBudgetBudgetType . '.period_start', '');
                $getArrayPeriodEndDate = Arr::get($array, $getBudgetBudgetType . '.period_end', '');

                if (!array_key_exists($getBudgetBudgetType, $array)) {
                    $array[$getBudgetBudgetType] = [
                        'id' => $key,
                        'budget_status' => $getBudgetBudgetStatus,
                        'period_start' => $getBudgetPeriodStartDate,
                        'period_end' => $getBudgetPeriodEndDate,
                    ];
                } elseif (
                    isDate($getBudgetPeriodStartDate) && isDate($getBudgetPeriodEndDate) && (
                        $getBudgetPeriodStartDate === $getArrayPeriodStartDate
                        || $getBudgetPeriodEndDate === $getArrayPeriodEndDate
                        || Carbon::parse($getBudgetPeriodStartDate)->betweenIncluded($getArrayPeriodStartDate, $getArrayPeriodEndDate)
                        || Carbon::parse($getBudgetPeriodEndDate)->betweenIncluded($getArrayPeriodStartDate, $getArrayPeriodEndDate)
                    )
                ) {
                    if (
                        empty($ids) ||
                        !in_array(Arr::get($array, Arr::get($budget, 'budget_type', '1') . '.id'), Arr::get($ids, Arr::get($budget, 'budget_type', '1'), []), true)
                    ) {
                        $ids[Arr::get($budget, 'budget_type', '1')][] = Arr::get(
                            $array,
                            Arr::get($budget, 'budget_type', '1') . '.id'
                        );
                    }
                    $ids[Arr::get($budget, 'budget_type', '1')][] = $key;
                }
            }
        }

        return $ids;
    }

    /**
     * Sets default values for budget status and type if not provided.
     * @param $budgets
     *
     * @return array
     */
    public function setBudgets($budgets): array
    {
        if (count($budgets)) {
            foreach ($budgets as $key => $budget) {
                $budgets[$key]['budget_status'] = !empty(Arr::get($budget, 'budget_status', '1')) ? Arr::get(
                    $budget,
                    'budget_status',
                    '1'
                ) : '1';
                $budgets[$key]['budget_type'] = !empty(Arr::get($budget, 'budget_type', '1')) ? Arr::get(
                    $budget,
                    'budget_type',
                    '1'
                ) : '1';
            }
        }

        return $budgets;
    }

    /**
     * Checks if revised budgets are valid.
     *
     * @param $budgets
     *
     * @return array
     *
     * @throws \JsonException
     */
    public function checkRevisedBudgets($budgets): array
    {
        $ids = [];
        $originalBudgets = [];

        foreach (getCodeList('BudgetStatus', 'Activity', false) as $statusKey => $budgetStatus) {
            $originalBudgets[$statusKey] = [];
        }

        if (count($budgets)) {
            foreach ($budgets as $key => $budget) {
                if (Arr::get($budget, 'budget_type', '1') === '1') {
                    $originalBudgets[Arr::get($budget, 'budget_type', '1')][] = [
                        'id' => $key,
                        'period_start' => Arr::get($budget, 'period_start.0.date', ''),
                        'period_end' => Arr::get($budget, 'period_end.0.date', ''),
                    ];
                }
            }

            foreach ($budgets as $key => $budget) {
                if ((Arr::get($budget, 'budget_type', '1') === '2')) {
                    $valid = false;

                    foreach ($originalBudgets[1] as $originalBudget) {
                        if (
                            $originalBudget['period_start'] === Arr::get($budget, 'period_start.0.date', '') &&
                            $originalBudget['period_end'] === Arr::get($budget, 'period_end.0.date', '')
                        ) {
                            $valid = true;
                        }
                    }

                    if (!$valid) {
                        $ids[Arr::get($budget, 'budget_type', '1')][] = $key;

                        foreach ($originalBudgets[Arr::get($budget, 'budget_type', '1')] as $originalBudget) {
                            $ids[Arr::get($budget, 'budget_type', '1')][] = $originalBudget['id'];
                        }
                    }
                }
            }
        }

        return $ids;
    }

    /**
     * Checks if narrative is empty.
     *
     * @param $narratives
     *
     * @return bool
     */
    public function isNarrativeEmpty($narratives): bool
    {
        if (count($narratives)) {
            foreach ($narratives as $narrative) {
                if (!empty(Arr::get($narrative, 'narrative', '')) || !empty(Arr::get($narrative, 'language', ''))) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Checks if element is empty.
     *
     * @param $components
     * @param $elementFieldName
     *
     * @return bool
     */
    public function isElementEmpty($components, $elementFieldName): bool
    {
        if (count($components)) {
            foreach ($components as $component) {
                if (!$this->isNarrativeEmpty(Arr::get($component, 'narrative', []))) {
                    return false;
                }

                foreach ($this->$elementFieldName as $componentField) {
                    if (!empty(Arr::get($component, $componentField, ''))) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    /**
     * Creates activity.
     *
     * @param $data
     *
     * @return object
     */
    public function create($data): object
    {
        return $this->activityRepository->store($data);
    }

    /**
     * Returns organisation->reporting_org.
     *
     * @return mixed
     */
    public function getReportingOrg(): mixed
    {
        return app(OrganizationRepository::class)->getSpecifiedColumn(auth()->user()->organization->id, 'reporting_org');
    }

    /**
     * Returns activities by organization ids.
     *
     * @param array $idMap
     *
     * @return array|Collection
     */
    public function getActivitiesByOrgIds(array $idMap): Collection|array
    {
        return $this->activityRepository->getActivitiesByOrgIds($idMap);
    }

    /**
     * append freeze and info_text if recipient country or region exists in any one of the activity transactions
     * if exists then it freezes the section.
     *
     * @param $activity
     * @param $elementName
     *
     * @return array
     *
     * @throws \JsonException
     */
    public function getRecipientRegionOrCountryManipulatedElementSchema($activity, $elementName): array
    {
        $element = getElementSchema($elementName);
        $manipulatedElement = ucfirst(str_replace('_', ' ', $elementName));

        if (count($activity->transactions)) {
            $recipient_region = $activity->transactions->pluck('transaction.recipient_region')->toArray();
            $recipient_country = $activity->transactions->pluck('transaction.recipient_country')->toArray();

            if (!is_array_value_empty($recipient_region) || !is_array_value_empty($recipient_country)) {
                $element['freeze'] = true;
                $element['info_text'] = "$manipulatedElement is already added at transaction level. You can add a $manipulatedElement either at activity level or at transaction level but not at both.";
            }
        }

        return $element;
    }

    /*
     * Fetch activity with its relation by id
     *
     * @param $activityId
     *
     * @return Model|null
     */
    public function getActivitityWithRelationsById($activityId): ?Model
    {
        return $this->activityRepository->getActivitityWithRelationsById($activityId);
    }

    /**
     * Updates activity.
     *
     * @param $activityId
     * @param $data
     *
     * @return bool
     */
    public function updateActivity($activityId, $data): bool
    {
        return $this->activityRepository->update($activityId, $data);
    }

    public function getDeprecationStatusMap($id = '', $key = '')
    {
        if ($id) {
            try {
                $activity = $this->activityRepository->find($id);
            } catch (Exception) {
                return [];
            }

            if (!$key) {
                return $activity->deprecation_status_map;
            }

            return Arr::get($activity->deprecation_status_map, $key, []);
        }

        return [];
    }

    /**
     * @return array{all: int, published: int, ready_for_republishing: int, draft: int}
     */
    public function getActivitiesCountByPublishedStatus(int $orgId): array
    {
        $activityStatus = $this->activityRepository->getActivityStatus(false, $orgId);
        $processedStatus = [
            'all' => 0,
            'published' => 0,
            'ready_for_republishing' => 0,
            'draft' => 0,
        ];

        foreach ($activityStatus as $status) {
            if ($status['status'] === 'published') {
                $processedStatus['published'] = $status['count'];
            } elseif ($status['linked_to_iati'] === true) {
                $processedStatus['ready_for_republishing'] = $status['count'];
            } else {
                $processedStatus['draft'] = $status['count'];
            }
        }

        $processedStatus['all'] = array_sum($processedStatus);

        return $processedStatus;
    }

    /**
     * Counts the number of non-published activities with the given ids.
     *
     * @param $activityIds
     *
     * @return int
     */
    public function checkNonPublishedActivities($activityIds): int
    {
        return $this->activityRepository->checkNonPublishedActivities($activityIds);
    }
}
