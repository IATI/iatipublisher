<?php

declare(strict_types=1);

namespace App\XmlImporter\Foundation\Mapper\Components;

use App\IATI\Models\Organization\Organization;
use App\IATI\Services\ElementCompleteService;
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
        $elementCompleteService = app(ElementCompleteService::class);

        $organization = Organization::find($orgId);
        $attributes = getActivityAttributes();
        $orgReportingOrgStatus = Arr::get($organization, 'element_status.reporting_org', false);

        $xmlActivityIdentifiers = $this->xmlActivityIdentifiers($activities);
        $xmlQueueWriter = app()->makeWith(XmlQueueWriter::class, [
            'authUser'                 => $authUser,
            'orgId'                    => $orgId,
            'orgRef'                   => $orgRef,
            'dbIatiIdentifiers'        => $dbIatiIdentifiers,
            'organizationReportingOrg' => $organizationReportingOrg,
            'xmlActivityIdentifiers'   => $xmlActivityIdentifiers,
        ]);

        $totalActivities = count($activities);
        $mappedData = [];

        foreach ($activities as $index => $activity) {
            $this->initComponents($organizationReportingOrg);

            $mappedData[$index] = $this->activity->map($this->filter($activity, 'iatiActivity'), $template, $orgRef);
            $mappedData[$index]['default_field_values'] = $this->defaultFieldValues($activity, $template);
            $mappedData[$index]['transactions'] = $this->transactionElement->map($this->filter($activity, 'transaction'), $template);
            $mappedData[$index]['transaction_references'] = $this->transactionElement->getReferences();
            $mappedData[$index]['result'] = $this->resultElement->map($this->filter($activity, 'result'), $template);

            $elementStatus = $elementCompleteService->prepareActivityElementStatus(
                new \App\IATI\Models\Activity\Activity($mappedData[$index]),
                $orgReportingOrgStatus,
                $attributes
            );

            $completePercentage = $elementCompleteService->calculateCompletePercentage($elementStatus);
            $deprecationStatusMap = refreshActivityDeprecationStatusMap($mappedData[$index]);

            $mappedData[$index]['element_status'] = $elementStatus;
            $mappedData[$index]['complete_percentage'] = $completePercentage;
            $mappedData[$index]['deprecation_status_map'] = $deprecationStatusMap;

            $xmlQueueWriter->save($mappedData[$index], $totalActivities, $index);
        }
        $this->mappedActivity = $mappedData;

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
