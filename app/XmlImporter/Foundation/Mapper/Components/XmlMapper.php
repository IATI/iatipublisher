<?php

declare(strict_types=1);

namespace App\XmlImporter\Foundation\Mapper\Components;

use App\IATI\Models\Organization\Organization;
use App\IATI\Services\ElementCompleteService;
use App\IATI\Traits\HydrationTrait;
use App\XmlImporter\Foundation\Mapper\Components\Elements\Result;
use App\XmlImporter\Foundation\Mapper\Components\Elements\Transaction;
use App\XmlImporter\Foundation\Support\Helpers\Traits\XmlHelper;
use App\XmlImporter\Foundation\XmlQueueWriter;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Arr;

/**
 * Class XmlMapper.
 */
class XmlMapper
{
    use XmlHelper;
    use HydrationTrait;

    /**
     * @var
     */
    protected $activity;

    /**
     * @var array
     */
    protected $iatiActivity;

    /**
     * @var array
     */
    protected array $transaction = [];

    /**
     * @var Transaction
     */
    protected Transaction $transactionElement;

    /**
     * @var
     */
    protected $resultElement;

    /**
     * @var array
     */
    protected array $result = [];

    /**
     * @var array
     */
    protected array $data = [];

    /**
     * @var array
     */
    protected array $activityElements = [
        'iatiIdentifier',
        'otherIdentifier',
        'reportingOrg',
        'title',
        'description',
        'activityStatus',
        'activityDate',
        'activityScope',
        'contactInfo',
        'participatingOrg',
        'tag',
        'recipientCountry',
        'recipientRegion',
        'sector',
        'collaborationType',
        'defaultFlowType',
        'defaultFinanceType',
        'defaultAidType',
        'defaultTiedStatus',
        'budget',
        'location',
        'plannedDisbursement',
        'countryBudgetItems',
        'documentLink',
        'policyMarker',
        'conditions',
        'legacyData',
        'humanitarianScope',
        'collaborationType',
        'capitalSpend',
        'relatedActivity',
    ];

    /**
     * @var array
     */
    public array $mappedActivity;

    /**
     * Xml constructor.
     */
    public function __construct()
    {
        //
    }

    /**
     * Initialize XmlMapper components according to the Xml Version.
     *
     * @return void
     * @throws BindingResolutionException
     */
    public function initComponents($organizationReportingOrg): void
    {
        $this->iatiActivity = [];
        $this->activity = app()->makeWith(Activity::class, ['organizationReportingOrg'  => $organizationReportingOrg]);
        $this->transactionElement = app()->make(Transaction::class);
        $this->resultElement = app()->make(Result::class);
    }

    /**
     * Map raw Xml data into AidStream database compatible data for import.
     *
     * @param array $activities
     * @param       $template
     * @param       $authUser
     * @param       $orgId
     * @param       $orgRef
     * @param       $dbIatiIdentifiers
     * @param       $organizationReportingOrg
     *
     * @return $this
     * @throws \App\Exceptions\InvalidTag
     * @throws BindingResolutionException
     */
    public function map(array $activities, $template, $authUser, $orgId, $orgRef, $dbIatiIdentifiers, $organizationReportingOrg): static
    {
        $mapStartTime = microtime(true);
        $totalActivities = count($activities);

        logger()->info('XmlMapper: Starting mapping process', [
            'total_activities' => $totalActivities,
            'org_id' => $orgId,
        ]);

        /** @var ElementCompleteService $elementCompleteService */
        $elementCompleteService = app(ElementCompleteService::class);

        // Fetch organization
        $orgFetchStart = microtime(true);
        $organization = Organization::find($orgId);
        $orgFetchDuration = microtime(true) - $orgFetchStart;
        logger()->info('XmlMapper: Organization fetched', [
            'duration_seconds' => round($orgFetchDuration, 3),
        ]);

        $attributes = getActivityAttributes();
        $orgReportingOrgStatus = Arr::get($organization, 'element_status.reporting_org', false);

        // Extract XML activity identifiers
        $identifierExtractionStart = microtime(true);
        $xmlActivityIdentifiers = $this->xmlActivityIdentifiers($activities);
        $identifierExtractionDuration = microtime(true) - $identifierExtractionStart;
        logger()->info('XmlMapper: Activity identifiers extracted', [
            'duration_seconds' => round($identifierExtractionDuration, 3),
            'unique_identifiers' => count($xmlActivityIdentifiers),
        ]);

        // Initialize XML Queue Writer
        $writerInitStart = microtime(true);
        $xmlQueueWriter = app()->makeWith(XmlQueueWriter::class, [
            'authUser'                 => $authUser,
            'orgId'                    => $orgId,
            'orgRef'                   => $orgRef,
            'dbIatiIdentifiers'        => $dbIatiIdentifiers,
            'organizationReportingOrg' => $organizationReportingOrg,
            'xmlActivityIdentifiers'   => $xmlActivityIdentifiers,
        ]);
        $writerInitDuration = microtime(true) - $writerInitStart;
        logger()->info('XmlMapper: XmlQueueWriter initialized', [
            'duration_seconds' => round($writerInitDuration, 3),
        ]);

        $mappedData = [];
        $activityTimings = [
            'init' => [],
            'activity_map' => [],
            'filter_activity' => [],
            'filter_transaction' => [],
            'filter_result' => [],
            'transaction_map' => [],
            'result_map' => [],
            'hydrate_transactions' => [],
            'element_status' => [],
            'deprecation_map' => [],
            'queue_save' => [],
        ];

        foreach ($activities as $index => $activity) {
            $activityStartTime = microtime(true);

            // Init components
            $initStart = microtime(true);
            $this->initComponents($organizationReportingOrg);
            $activityTimings['init'][] = microtime(true) - $initStart;

            // Filter and map activity
            $filterActivityStart = microtime(true);
            $filteredActivity = $this->filter($activity, 'iatiActivity');
            $activityTimings['filter_activity'][] = microtime(true) - $filterActivityStart;

            $activityMapStart = microtime(true);
            $mappedData[$index] = $this->activity->map($filteredActivity, $template, $orgRef);
            $activityTimings['activity_map'][] = microtime(true) - $activityMapStart;

            $mappedData[$index]['default_field_values'] = $this->defaultFieldValues($activity, $template);

            // Filter and map transactions
            $filterTransactionStart = microtime(true);
            $filteredTransactions = $this->filter($activity, 'transaction');
            $activityTimings['filter_transaction'][] = microtime(true) - $filterTransactionStart;

            $transactionMapStart = microtime(true);
            $mappedData[$index]['transactions'] = $this->transactionElement->map($filteredTransactions, $template);
            $mappedData[$index]['transaction_references'] = $this->transactionElement->getReferences();
            $activityTimings['transaction_map'][] = microtime(true) - $transactionMapStart;

            // Filter and map results
            $filterResultStart = microtime(true);
            $filteredResults = $this->filter($activity, 'result');
            $activityTimings['filter_result'][] = microtime(true) - $filterResultStart;

            $resultMapStart = microtime(true);
            $mappedData[$index]['result'] = $this->resultElement->map($filteredResults, $template);
            $activityTimings['result_map'][] = microtime(true) - $resultMapStart;

            $activityData = $mappedData[$index];
            $activityModel = new \App\IATI\Models\Activity\Activity($activityData);

            /* For some reason the country_budget_items is inside an array, causing 'isCountryBudgetItemsElementCompleted' to return false */
            $activityModel->country_budget_items = Arr::get($activityModel, 'country_budget_items.0');

            $hydrateStart = microtime(true);
            $activityModel->transactions = $this->hydrateTransactions($activityData);
            $activityTimings['hydrate_transactions'][] = microtime(true) - $hydrateStart;

            // Calculate element status and completion
            $elementStatusStart = microtime(true);
            $elementStatus = $elementCompleteService->prepareActivityElementStatus($activityModel, $orgReportingOrgStatus, $attributes);
            $completePercentage = $elementCompleteService->calculateCompletePercentage($elementStatus);
            $activityTimings['element_status'][] = microtime(true) - $elementStatusStart;

            $deprecationStart = microtime(true);
            $deprecationStatusMap = refreshActivityDeprecationStatusMap($mappedData[$index]);
            $activityTimings['deprecation_map'][] = microtime(true) - $deprecationStart;

            $mappedData[$index]['element_status'] = $elementStatus;
            $mappedData[$index]['complete_percentage'] = $completePercentage;
            $mappedData[$index]['deprecation_status_map'] = $deprecationStatusMap;

            // Save to queue
            $queueSaveStart = microtime(true);
            $xmlQueueWriter->save($mappedData[$index], $totalActivities, $index);
            $activityTimings['queue_save'][] = microtime(true) - $queueSaveStart;

            $activityDuration = microtime(true) - $activityStartTime;

            // Log every single activity
            logger()->info('XmlMapper: Activity mapped', [
                'activity_index' => $index + 1,
                'total' => $totalActivities,
                'percent_complete' => round((($index + 1) / $totalActivities) * 100, 1),
                'duration_seconds' => round($activityDuration, 3),
                'breakdown_ms' => [
                    'init' => round($activityTimings['init'][$index] * 1000, 1),
                    'filter_activity' => round($activityTimings['filter_activity'][$index] * 1000, 1),
                    'activity_map' => round($activityTimings['activity_map'][$index] * 1000, 1),
                    'filter_transaction' => round($activityTimings['filter_transaction'][$index] * 1000, 1),
                    'transaction_map' => round($activityTimings['transaction_map'][$index] * 1000, 1),
                    'filter_result' => round($activityTimings['filter_result'][$index] * 1000, 1),
                    'result_map' => round($activityTimings['result_map'][$index] * 1000, 1),
                    'hydrate' => round($activityTimings['hydrate_transactions'][$index] * 1000, 1),
                    'element_status' => round($activityTimings['element_status'][$index] * 1000, 1),
                    'deprecation' => round($activityTimings['deprecation_map'][$index] * 1000, 1),
                    'queue_save' => round($activityTimings['queue_save'][$index] * 1000, 1),
                ],
            ]);
        }

        $this->mappedActivity = $mappedData;

        $totalMapDuration = microtime(true) - $mapStartTime;

        // Calculate aggregate timing statistics
        $avgTimings = [];
        $totalTimings = [];
        foreach ($activityTimings as $operation => $times) {
            $avgTimings[$operation] = !empty($times) ? round(array_sum($times) / count($times), 3) : 0;
            $totalTimings[$operation] = !empty($times) ? round(array_sum($times), 2) : 0;
        }

        logger()->info('XmlMapper: Mapping completed', [
            'total_duration_seconds' => round($totalMapDuration, 2),
            'total_duration_minutes' => round($totalMapDuration / 60, 2),
            'activities_processed' => $totalActivities,
            'avg_per_activity_seconds' => round($totalMapDuration / $totalActivities, 3),
            'total_timings_seconds' => $totalTimings,
            'avg_timings_seconds' => $avgTimings,
            'percentage_breakdown' => [
                'init_percent' => round(($totalTimings['init'] / $totalMapDuration) * 100, 1),
                'activity_map_percent' => round(($totalTimings['activity_map'] / $totalMapDuration) * 100, 1),
                'transaction_map_percent' => round(($totalTimings['transaction_map'] / $totalMapDuration) * 100, 1),
                'result_map_percent' => round(($totalTimings['result_map'] / $totalMapDuration) * 100, 1),
                'element_status_percent' => round(($totalTimings['element_status'] / $totalMapDuration) * 100, 1),
                'queue_save_percent' => round(($totalTimings['queue_save'] / $totalMapDuration) * 100, 1),
            ],
        ]);

        return $this;
    }

    /**
     * Collects all activityIdentifiers present in xml file.
     *
     * @param $activities
     *
     * @return array
     */
    public function xmlActivityIdentifiers($activities): array
    {
        $xmlActivityIdentifiers = [];

        foreach ($activities as $activity) {
            foreach (Arr::get($activity, 'value', []) as $element => $value) {
                if ($this->name(Arr::get($value, 'name')) === 'iatiIdentifier') {
                    $xmlActivityIdentifiers[] = $this->value($value);
                    break;
                }
            }
        }

        return !empty($xmlActivityIdentifiers) ? array_count_values($xmlActivityIdentifiers) : [];
    }

    /**
     * Returns false if the xml is not activity file.
     *
     * @param $activities
     *
     * @return bool
     */
    public function isValidActivityFile($activities): bool
    {
        foreach ($activities as $activity) {
            if ($this->name(Arr::get($activity, 'name')) !== 'iatiActivity') {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the mapped Xml data.
     *
     * @return array
     */
    public function data(): array
    {
        return $this->data;
    }

    /**
     * Store the mapped Xml data in a temporary json file.
     */
    public function keep(): void
    {
    }

    /**
     * Filter the default field values from the xml data.
     *
     * @param $activity
     * @param $template
     *
     * @return array
     */
    protected function defaultFieldValues($activity, $template): array
    {
        $defaultFieldValues[0] = $template['default_field_values'];
        $defaultFieldValues[0]['default_currency'] = $this->attributes($activity, 'default-currency');
        $defaultFieldValues[0]['default_language'] = $this->attributes($activity, 'lang');
        $defaultFieldValues[0]['hierarchy'] = $this->attributes($activity, 'hierarchy');
        $defaultFieldValues[0]['humanitarian'] = $this->attributes($activity, 'humanitarian');
        $defaultFieldValues[0]['budget_not_provided'] = $this->attributes($activity, 'budget-not-provided');

        return $defaultFieldValues;
    }

    /**
     * Filter raw Xml data for a certain element with a specific elementName.
     *
     * @param $xmlData
     * @param $elementName
     *
     * @return mixed
     */
    protected function filter($xmlData, $elementName): mixed
    {
        [$this->transaction, $this->result] = [[], []];
        foreach ($this->value($xmlData) as $subElement) {
            if ($elementName === 'transaction') {
                $this->filterForTransactions($subElement, $elementName);
            } elseif ($elementName === 'result') {
                $this->filterForResults($subElement, $elementName);
            } elseif ($elementName === 'iatiActivity') {
                $this->filterForActivity($subElement, $elementName);
            }
        }

        return $this->{$elementName};
    }

    /**
     * Filter data for Activity Elements.
     *
     * @param $subElement
     * @param $elementName
     *
     * @return void
     */
    protected function filterForActivity($subElement, $elementName): void
    {
        if (in_array($this->name($subElement), $this->activityElements, true)) {
            $this->{$elementName}[] = $subElement;
        }
    }

    /**
     * Filter data for Transactions Elements.
     *
     * @param $subElement
     * @param $elementName
     *
     * @return void
     */
    protected function filterForTransactions($subElement, $elementName): void
    {
        if ($this->name($subElement) === $elementName) {
            $this->{$elementName}[] = $subElement;
        }
    }

    /**
     * @param $subElement
     * @param $elementName
     *
     * @return void
     */
    protected function filterForResults($subElement, $elementName): void
    {
        if ($this->name($subElement) === $elementName) {
            $this->{$elementName}[] = $subElement;
        }
    }

    /**
     * This method is same as that of map() function above but without XML QUEUE writer
     * This method is only for testing purpose.
     *
     *
     * @param array $activities
     * @param $template
     * @param $orgRef
     * @param $organizationReportingOrg
     * @return array
     * @throws BindingResolutionException
     */
    public function mapForTest(array $activities, $template, $orgRef, $organizationReportingOrg): array
    {
        $mappedData = [];

        foreach ($activities as $index => $activity) {
            $this->initComponents($organizationReportingOrg);

            $mappedData[$index] = $this->activity->map($this->filter($activity, 'iatiActivity'), $template, $orgRef);
            $mappedData[$index]['default_field_values'] = $this->defaultFieldValues($activity, $template);
            $mappedData[$index]['transactions'] = $this->transactionElement->map($this->filter($activity, 'transaction'), $template);
            $mappedData[$index]['transaction_references'] = $this->transactionElement->getReferences();
            $mappedData[$index]['result'] = $this->resultElement->map($this->filter($activity, 'result'), $template);
        }

        return $mappedData;
    }
}
