<?php

namespace App\XmlImporter\Foundation\Mapper\Components;

use App\XmlImporter\Foundation\Support\Helpers\Traits\XmlHelper;
use App\XmlImporter\Foundation\XmlQueueWriter;
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
    protected $iatiActivity = [];

    /**
     * @var array
     */
    protected $transaction = [];

    /**
     * @var
     */
    protected $transactionElement;

    /**
     * @var
     */
    protected $resultElement;

    /**
     * @var array
     */
    protected $result = [];

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var array
     */
    protected $activityElements = [
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
     * Iati version.
     *
     * @var string
     */
    protected $version = '2.03';

    /**
     * Upgrade flag.
     *
     * @var bool
     */
    protected $upgrade;

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
     * @return mixed
     */
    public function initComponents()
    {
        $this->iatiActivity = null;

        $this->activity = app()->make('App\XmlImporter\Foundation\Mapper\Components\Activity');
        $this->transactionElement = app()->make('App\XmlImporter\Foundation\Mapper\Components\Elements\Transaction');
        $this->resultElement = app()->make('App\XmlImporter\Foundation\Mapper\Components\Elements\Result');
    }

    /**
     * Map raw Xml data into AidStream database compatible data for import.
     *
     * @param array $activities
     * @param       $template
     * @param       $userId
     * @param       $orgId
     * @param       $dbIatiIdentifiers
     * @return $this
     */
    public function map(array $activities, $template, $userId, $orgId, $dbIatiIdentifiers)
    {
        $xmlQueueWriter = app()->makeWith(XmlQueueWriter::class, ['userId' => $userId, 'orgId' => $orgId, 'dbIatiIdentifiers' => $dbIatiIdentifiers]);

        $totalActivities = count($activities);
        $mappedData = [];

        foreach ($activities as $index => $activity) {
            $this->initComponents();
            $mappedData[$index] = $this->activity->map($this->filter($activity, 'iatiActivity'), $template, $this->upgrade);
            $mappedData[$index]['default_field_values'] = $this->defaultFieldValues($activity, $template);
            $mappedData[$index]['transactions'] = $this->transactionElement->map($this->filter($activity, 'transaction'), $template, $this->upgrade);
            $mappedData[$index]['result'] = $this->resultElement->map($this->filter($activity, 'result'), $template);

            $xmlQueueWriter->save($mappedData[$index], $totalActivities, $index);
        }

        return $this;
    }

    /**
     * Returns false if the xml is not activity file.
     * @param $activities
     * @return bool
     */
    public function isValidActivityFile($activities)
    {
        foreach ($activities as $activity) {
            if ($this->name(Arr::get($activity, 'name'), '') != 'iatiActivity') {
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
    public function data()
    {
        return $this->data;
    }

    /**
     * Store the mapped Xml data in a temporary json file.
     */
    public function keep()
    {
    }

    /**
     * Filter the default field values from the xml data.
     *
     * @param array $activity
     * @param       $template
     * @return mixed
     */
    protected function defaultFieldValues($activity, $template)
    {
        $defaultFieldValues[0] = $template['default_field_values'];
        $defaultFieldValues[0]['default_currency'] = $this->attributes($activity, 'default-currency');
        $defaultFieldValues[0]['default_language'] = $this->attributes($activity, 'language');
        $defaultFieldValues[0]['default_hierarchy'] = $this->attributes($activity, 'hierarchy');
        $defaultFieldValues[0]['linked_data_uri'] = $this->attributes($activity, 'linked-data-uri');
        $defaultFieldValues[0]['humanitarian'] = $this->attributes($activity, 'humanitarian');

        return $defaultFieldValues;
    }

    /**
     * Filter raw Xml data for a certain element with a specific elementName.
     *
     * @param $xmlData
     * @param $elementName
     */
    protected function filter($xmlData, $elementName)
    {
        list($this->transaction, $this->result) = [[], []];
        foreach ($this->value($xmlData) as $subElement) {
            if ($elementName == 'transaction') {
                $this->filterForTransactions($subElement, $elementName);
            } elseif ($elementName == 'result') {
                $this->filterForResults($subElement, $elementName);
            } elseif ($elementName == 'iatiActivity') {
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
     */
    protected function filterForActivity($subElement, $elementName)
    {
        if (in_array($this->name($subElement), $this->activityElements)) {
            $this->{$elementName}[] = $subElement;
        }
    }

    /**
     * Filter data for Transactions Elements.
     *
     * @param $subElement
     * @param $elementName
     */
    protected function filterForTransactions($subElement, $elementName)
    {
        if ($this->name($subElement) == $elementName) {
            $this->{$elementName}[] = $subElement;
        }
    }

    /**
     * @param $subElement
     * @param $elementName
     */
    protected function filterForResults($subElement, $elementName)
    {
        if ($this->name($subElement) == $elementName) {
            $this->{$elementName}[] = $subElement;
        }
    }

    /**
     * Set upgrade flag for older xml versions.
     *
     * @param bool $value
     * @return XmlMapper
     */
    public function setUpgradeFlag($value = false)
    {
        $this->upgrade = $value;

        return $this;
    }
}
