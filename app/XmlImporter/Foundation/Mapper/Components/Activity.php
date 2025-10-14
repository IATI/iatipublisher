<?php

declare(strict_types=1);

namespace App\XmlImporter\Foundation\Mapper\Components;

use App\IATI\Traits\DataSanitizeTrait;
use App\XmlImporter\Foundation\Support\Helpers\Traits\XmlHelper;
use Illuminate\Support\Arr;

/**
 * Class Activity.
 */
class Activity
{
    use XmlHelper;
    use DataSanitizeTrait;

    /**
     * @var array
     */
    protected array $activityElements = [
        'iatiIdentifier' => 'iati_identifier',
        'otherIdentifier' => 'other_identifier',
        'reportingOrg' => 'identifier',
        'title' => 'title',
        'description' => 'description',
        'activityStatus' => 'activity_status',
        'activityDate' => 'activity_date',
        'contactInfo' => 'contact_info',
        'activityScope' => 'activity_scope',
        'participatingOrg' => 'participating_org',
        'tag' => 'tag',
        'recipientCountry' => 'recipient_country',
        'recipientRegion' => 'recipient_region',
        'location' => 'location',
        'sector' => 'sector',
        'countryBudgetItems' => 'country_budget_items',
        'humanitarianScope' => 'humanitarian_scope',
        'policyMarker' => 'policy_marker',
        'collaborationType' => 'collaboration_type',
        'defaultFlowType' => 'default_flow_type',
        'defaultFinanceType' => 'default_finance_type',
        'defaultAidType' => 'default_aid_type',
        'defaultTiedStatus' => 'default_tied_status',
        'budget' => 'budget',
        'plannedDisbursement' => 'planned_disbursement',
        'capitalSpend' => 'capital_spend',
        'documentLink' => 'document_link',
        'relatedActivity' => 'related_activity',
        'legacyData' => 'legacy_data',
        'conditions' => 'conditions',
        'defaultFieldValues' => 'default_field_values',
    ];

    /**
     * @var array
     */
    public array $activity = [];

    /**
     * @var array
     */
    public array $identifier = [];

    /**
     * @var array
     */
    public array $otherIdentifier = [];

    /**
     * @var array
     */
    public array $title = [];

    /**
     * @var array
     */
    public array $reporting = [];

    /**
     * @var
     */
    public $orgRef;

    /**
     * @var array
     */
    public array $description = [];

    /**
     * @var array
     */
    public array $participatingOrg = [];

    /**
     * @var array
     */
    public array $activityDate = [];

    /**
     * @var array
     */
    public array $contactInfo = [];

    /**
     * @var array
     */
    public array $sector = [];

    /**
     * @var array
     */
    public array $budget = [];

    /**
     * @var array
     */
    public array $recipientRegion = [];

    /**
     * @var array
     */
    public array $recipientCountry = [];

    /**
     * @var array
     */
    public array $location = [];

    /**
     * @var array
     */
    public array $plannedDisbursement = [];

    /**
     * @var array
     */
    public array $capitalSpend = [];

    /**
     * @var array
     */
    public array $countryBudgetItems = [];

    /**
     * @var array
     */
    public array $documentLink = [];

    /**
     * @var array
     */
    public array $policyMarker = [];

    /**
     * @var array
     */
    public array $conditions = [];

    /**
     * @var array
     */
    protected array $legacyData = [];

    /**
     * @var array
     */
    protected array $humanitarianScope = [];

    /**
     * @var array
     */
    protected array $relatedActivity = [];

    /**
     * @var array
     */
    protected array $defaultAidType = [];

    /**
     * @var int
     */
    protected int $index = 0;

    /**
     * @var array
     */
    protected array $tagVariable = [];

    /**
     * @var array
     */
    protected array $organizationReportingOrg = [];

    /**
     * @var array
     */
    protected array $emptyNarrative = [['narrative' => null, 'language' => null]];

    public function __construct($organizationReportingOrg)
    {
        $this->organizationReportingOrg = $organizationReportingOrg;
    }

    /**
     * @param $element
     * @param $template
     * @return array
     */
    public function iatiIdentifier($element, $template): array
    {
        $this->identifier = $template['identifier'];
        $this->identifier['iati_identifier_text'] = trim((string) $this->value($element));

        if ($this->orgRef) {
            $this->identifier['activity_identifier'] = substr(trim((string) $this->value($element)), strlen($this->orgRef) + 1);
        }

        return $this->identifier;
    }

    /**
     * @param $element
     * @param $template
     * @return array
     */
    public function otherIdentifier($element, $template): array
    {
        $this->otherIdentifier[$this->index] = $template['other_identifier'];
        $this->otherIdentifier[$this->index]['reference'] = $this->attributes($element, 'ref');
        $this->otherIdentifier[$this->index]['reference_type'] = strtoupper($this->attributes($element, 'type'));
        $this->otherIdentifier[$this->index]['owner_org'][0]['ref'] = $this->attributes($element, 'ref', 'ownerOrg');
        $this->otherIdentifier[$this->index]['owner_org'][0]['narrative'] = $this->narrative(Arr::get($element, 'value.0', []));
        $this->index++;

        return $this->otherIdentifier;
    }

    /**
     * @param $element
     *
     * @return array
     */
    public function title($element): array
    {
        $this->title = $this->narrative($element);

        return $this->title;
    }

    /**
     * @param $element
     * @param $template
     *
     * @return array
     */
    public function reportingOrg($element, $template): array
    {
        if (empty($this->identifier)) {
            $this->orgRef = $this->attributes($element, 'ref');
        } else {
            $this->identifier['activity_identifier'] = substr($this->identifier['iati_identifier_text'], strlen($this->attributes($element, 'ref')) + 1);
        }

        $this->activity['reporting_org'][0] = $template['reporting_org'];
        $this->activity['reporting_org'][0]['ref'] = $this->getReportingOrgRef($element);
        $this->activity['reporting_org'][0]['type'] = $this->getReportingOrgType($element);
        $this->activity['reporting_org'][0]['secondary_reporter'] = $this->getSecondaryReporter($element);
        $this->activity['reporting_org'][0]['narrative'] = $this->getReportingOrgNarrative($element);

        return $this->identifier;
    }

    /**
     * Returns secondary reporter.
     *
     * @param $element
     *
     * @return string
     */
    public function getSecondaryReporter($element): string
    {
        $secondaryReporter = $this->attributes($element, 'secondary-reporter');

        if ((is_string($secondaryReporter) && strtolower($secondaryReporter) === 'true') || $secondaryReporter === '1') {
            return '1';
        }

        if ((is_string($secondaryReporter) && strtolower($secondaryReporter) === 'false') || $secondaryReporter === '0') {
            return '0';
        }

        return $this->organizationReportingOrg[0]['secondary_reporter'] ?? '';
    }

    /**
     * Returns value of single valued element\.
     *
     * @param $element
     *
     * @return int|string|null
     */
    public function getSingleValuedField($element): int|string|null
    {
        $value = $this->attributes($element, 'code');

        if (is_numeric($value)) {
            return (int) $value;
        }

        return empty($value) ? null : $value;
    }

    /**
     * @param $element
     *
     * @return array
     */
    public function description($element): array
    {
        $type = $this->attributes($element, 'type');

        $descriptionNumber = count($this->description);

        $this->description[$descriptionNumber]['type'] = $type;

        $this->description[$descriptionNumber]['narrative'] = $this->narrative($element);

        return $this->description;
    }

    /**
     * @param $element
     * @param $template
     * @return array
     */
    public function participatingOrg($element, $template): array
    {
        $this->participatingOrg[$this->index] = $template['participating_org'];
        $this->participatingOrg[$this->index]['organization_role'] = $this->attributes($element, 'role');
        $this->participatingOrg[$this->index]['ref'] = $this->attributes($element, 'ref');
        $this->participatingOrg[$this->index]['type'] = $this->attributes($element, 'type');
        $this->participatingOrg[$this->index]['identifier'] = $this->attributes($element, 'activity-id');
        $this->participatingOrg[$this->index]['crs_channel_code'] = $this->attributes($element, 'crs-channel-code');
        $this->participatingOrg[$this->index]['narrative'] = $this->narrative($element);
        $this->index++;

        return $this->participatingOrg;
    }

    /**
     * @param $element
     *
     * @return mixed|string
     * @throws \JsonException
     */
    public function activityStatus($element): mixed
    {
        return $this->getSingleValuedField($element);
    }

    /**
     * @param $element
     * @param $template
     * @return array
     */
    public function activityDate($element, $template): array
    {
        $this->activityDate[$this->index] = $template['activity_date'];
        $this->activityDate[$this->index]['date'] = dateFormat('Y-m-d', $this->attributes($element, 'iso-date'));
        $this->activityDate[$this->index]['type'] = $this->attributes($element, 'type');
        $this->activityDate[$this->index]['narrative'] = $this->narrative($element);
        $this->index++;

        return $this->activityDate;
    }

    /**
     * @param $element
     *
     * @return mixed
     */
    public function activityScope($element): mixed
    {
        return $this->getSingleValuedField($element);
    }

    /**
     * Maps contactInfo fields.
     *
     * @param $element
     * @param $template
     *
     * @return array
     */
    public function contactInfo($element, $template): array
    {
        $value = Arr::get($element, 'value', []) ?? [];
        $this->contactInfo[$this->index] = $template['contact_info'];
        $this->contactInfo[$this->index]['type'] = $this->attributes($element, 'type');
        $this->contactInfo[$this->index]['organisation'][0]['narrative'] = $this->value($value, 'organisation');
        $this->contactInfo[$this->index]['department'][0]['narrative'] = $this->value($value, 'department');
        $this->contactInfo[$this->index]['person_name'][0]['narrative'] = $this->value($value, 'personName');
        $this->contactInfo[$this->index]['job_title'][0]['narrative'] = $this->value($value, 'jobTitle');
        $this->contactInfo[$this->index]['telephone'] = $this->filterValues($value, 'telephone');
        $this->contactInfo[$this->index]['email'] = $this->filterValues($value, 'email');
        $this->contactInfo[$this->index]['website'] = $this->filterValues($value, 'website');
        $this->contactInfo[$this->index]['mailing_address'] = $this->getMailingAddress($value) ?? [];
        $this->index++;

        return $this->contactInfo;
    }

    /**
     * Returns array for mailing address.
     *
     * @param $component
     *
     * @return array
     */
    public function getMailingAddress($component): array
    {
        $array = [];

        foreach ($component as $key => $value) {
            if (($this->name($value) === 'mailingAddress') && is_array($value)) {
                $array[]['narrative'] = $this->value($component, 'mailingAddress');
                unset($component[$key]);
            }
        }

        return $array;
    }

    /**
     * @param $element
     * @param $template
     * @return array
     */
    public function sector($element, $template): array
    {
        $this->sector[$this->index] = $template['sector'];
        $vocabulary = $this->attributes($element, 'vocabulary');
        $this->sector[$this->index]['sector_vocabulary'] = $vocabulary;
        $this->sector[$this->index]['vocabulary_uri'] = $this->attributes($element, 'vocabulary-uri');
        $this->sector[$this->index]['code'] = ($vocabulary === '1') ? $this->attributes($element, 'code') : '';
        $this->sector[$this->index]['category_code'] = ($vocabulary === '2') ? $this->attributes($element, 'code') : '';
        $this->sector[$this->index]['sdg_goal'] = ($vocabulary === '7') ? $this->attributes($element, 'code') : '';
        $this->sector[$this->index]['sdg_target'] = ($vocabulary === '8') ? $this->attributes($element, 'code') : '';
        $this->sector[$this->index]['text'] = ($vocabulary !== '1' && $vocabulary !== '2') ? $this->attributes($element, 'code') : '';
        $this->sector[$this->index]['percentage'] = $this->attributes($element, 'percentage');
        $this->sector[$this->index]['narrative'] = $this->narrative($element);
        $this->index++;

        return $this->sector;
    }

    /**
     * @param $element
     *
     * @return mixed
     */
    public function defaultFlowType($element): mixed
    {
        return $this->getSingleValuedField($element);
    }

    /**
     * @param $element
     *
     * @return mixed
     */
    public function defaultFinanceType($element): mixed
    {
        return $this->getSingleValuedField($element);
    }

    /**
     * @param $element
     *
     * @return int|null
     */
    public function defaultTiedStatus($element): ?int
    {
        return $this->getSingleValuedField($element);
    }

    /**
     * Returns default status type.
     *
     * @param $element
     * @param $attribute
     *
     * @return string
     */
    public function getDefaultStatusType($element, $attribute)
    {
        $value = $this->attributes($element, $attribute);

        if (is_string($value) && !empty($value)) {
            return $value;
        }

        return '1';
    }

    /**
     * Returns activity budget.
     *
     * @param $element
     * @param $template
     *
     * @return array
     */
    public function budget($element, $template): array
    {
        $this->budget[$this->index] = $template['budget'];
        $this->budget[$this->index]['budget_status'] = $this->getDefaultStatusType($element, 'status');
        $this->budget[$this->index]['budget_type'] = $this->getDefaultStatusType($element, 'type');
        $this->budget[$this->index]['period_start'][0]['date'] = dateFormat('Y-m-d', $this->attributes($element, 'iso-date', 'periodStart'));
        $this->budget[$this->index]['period_end'][0]['date'] = dateFormat('Y-m-d', $this->attributes($element, 'iso-date', 'periodEnd'));
        $this->budget[$this->index]['budget_value'][0]['amount'] = $this->value(Arr::get($element, 'value', []), 'value');
        $this->budget[$this->index]['budget_value'][0]['currency'] = strtoupper($this->attributes($element, 'currency', 'value'));
        $this->budget[$this->index]['budget_value'][0]['value_date'] = dateFormat('Y-m-d', $this->attributes($element, 'value-date', 'value'));
        $this->index++;

        return $this->budget;
    }

    /**
     * @param $element
     * @param $template
     *
     * @return array
     */
    public function recipientRegion($element, $template): array
    {
        $this->recipientRegion[$this->index] = $template['recipient_region'];
        $this->recipientRegion[$this->index]['region_vocabulary'] = $this->attributes($element, 'vocabulary');
        $this->recipientRegion[$this->index]['vocabulary_uri'] = $this->attributes($element, 'vocabulary-uri');
        $this->recipientRegion[$this->index]['percentage'] = $this->attributes($element, 'percentage');
        $this->recipientRegion[$this->index]['narrative'] = $this->narrative($element);
        $code_field = $this->attributes($element, 'vocabulary') === '1' ? 'region_code' : 'custom_code';
        $this->recipientRegion[$this->index][$code_field] = $this->attributes($element, 'code');
        unset($this->recipientRegion[$this->index][$code_field === 'region_code' ? 'custom_code' : 'region_code']);
        $this->index++;

        return $this->recipientRegion;
    }

    /**
     * @param $element
     * @param $template
     *
     * @return array
     */
    public function recipientCountry($element, $template): array
    {
        $this->recipientCountry[$this->index] = $template['recipient_country'];
        $this->recipientCountry[$this->index]['country_code'] = strtoupper($this->attributes($element, 'code'));
        $this->recipientCountry[$this->index]['percentage'] = $this->attributes($element, 'percentage');
        $this->recipientCountry[$this->index]['narrative'] = $this->narrative($element);
        $this->index++;

        return $this->recipientCountry;
    }

    /**
     * @param $element
     * @param $template
     *
     * @return array
     */
    public function location($element, $template): array
    {
        $value = Arr::get($element, 'value', []) ?? [];
        $this->location[$this->index] = $template['location'];
        $this->location[$this->index]['ref'] = $this->attributes($element, 'ref');
        $this->location[$this->index]['location_reach'][0]['code'] = $this->attributes($element, 'code', 'locationReach');
        $this->location[$this->index]['location_id'] = $this->getLocationIdData($element);
        $this->location[$this->index]['location_id'][0]['vocabulary'] = strtoupper($this->attributes($element, 'vocabulary', 'locationId'));
        $this->location[$this->index]['location_id'][0]['code'] = $this->attributes($element, 'code', 'locationId');
        $this->location[$this->index]['name'][0]['narrative'] = (($name = $this->value($value, 'name')) === '') ? $this->emptyNarrative : $name;
        $this->location[$this->index]['description'][0]['narrative'] = ((
            $locationDesc = $this->value(
                $value,
                'description'
            )
        ) === '') ? $this->emptyNarrative : $locationDesc;
        $this->location[$this->index]['activity_description'][0]['narrative'] = ((
            $elementDesc = $this->value(
                $value,
                'activityDescription'
            )
        ) === '') ? $this->emptyNarrative : $elementDesc;
        $this->location[$this->index]['administrative'] = $this->filterAttributes($value, 'administrative', ['code', 'vocabulary', 'level']);
        $this->location[$this->index]['point'][0]['srs_name'] = $this->attributes($element, 'srsName', 'point');
        $this->location[$this->index]['point'][0]['pos'][0] = $this->latAndLong($value);
        $this->location[$this->index]['exactness'][0]['code'] = $this->attributes($element, 'code', 'exactness');
        $this->location[$this->index]['location_class'][0]['code'] = $this->attributes($element, 'code', 'locationClass');
        $this->location[$this->index]['feature_designation'][0]['code'] = strtoupper($this->attributes($element, 'code', 'featureDesignation'));
        $this->index++;

        return $this->location;
    }

    /**
     * @param $element
     * @param $template
     *
     * @return array
     */
    public function plannedDisbursement($element, $template): array
    {
        $this->plannedDisbursement[$this->index] = $template['planned_disbursement'];
        $this->plannedDisbursement[$this->index]['planned_disbursement_type'] = $this->attributes($element, 'type');
        $this->plannedDisbursement[$this->index]['period_start'][0]['date'] = dateFormat('Y-m-d', $this->attributes($element, 'iso-date', 'periodStart'));
        $this->plannedDisbursement[$this->index]['period_end'][0]['date'] = dateFormat('Y-m-d', $this->attributes($element, 'iso-date', 'periodEnd'));
        $this->plannedDisbursement[$this->index]['value'][0]['amount'] = $this->value(Arr::get($element, 'value', []), 'value');
        $this->plannedDisbursement[$this->index]['value'][0]['currency'] = strtoupper($this->attributes($element, 'currency', 'value'));
        $this->plannedDisbursement[$this->index]['value'][0]['value_date'] = dateFormat('Y-m-d', $this->attributes($element, 'value-date', 'value'));
        $this->plannedDisbursement[$this->index]['provider_org'][0]['ref'] = $this->attributes($element, 'ref', 'providerOrg');
        $this->plannedDisbursement[$this->index]['provider_org'][0]['provider_activity_id'] = $this->attributes($element, 'provider-activity-id', 'providerOrg');
        $this->plannedDisbursement[$this->index]['provider_org'][0]['type'] = $this->attributes($element, 'type', 'providerOrg');
        $this->plannedDisbursement[$this->index]['provider_org'][0]['narrative'] = ((
            $providerOrg = $this->value(
                Arr::get($element, 'value', []),
                'providerOrg'
            )
        ) === '') ? $this->emptyNarrative : $providerOrg;
        $this->plannedDisbursement[$this->index]['receiver_org'][0]['ref'] = $this->attributes($element, 'ref', 'receiverOrg');
        $this->plannedDisbursement[$this->index]['receiver_org'][0]['receiver_activity_id'] = $this->attributes($element, 'receiver-activity-id', 'receiverOrg');
        $this->plannedDisbursement[$this->index]['receiver_org'][0]['type'] = $this->attributes($element, 'type', 'receiverOrg');
        $this->plannedDisbursement[$this->index]['receiver_org'][0]['narrative'] = ((
            $receiverOrg = $this->value(
                Arr::get($element, 'value', []),
                'receiverOrg'
            )
        ) === '') ? $this->emptyNarrative : $receiverOrg;
        $this->index++;

        return $this->plannedDisbursement;
    }

    /**
     * @param $element
     * @param $template
     *
     * @return array
     */
    public function countryBudgetItems($element, $template): array
    {
        $this->countryBudgetItems[$this->index] = $template['country_budget_items'];
        $this->countryBudgetItems[$this->index]['country_budget_vocabulary'] = $this->attributes($element, 'vocabulary');

        foreach (Arr::get($element, 'value', []) as $index => $budgetItem) {
            $this->countryBudgetItems[$this->index]['budget_item'][$index]['code'] = $this->attributes($budgetItem, 'code');
            $this->countryBudgetItems[$this->index]['budget_item'][$index]['percentage'] = $this->attributes($budgetItem, 'percentage');
            $desc = $this->value(Arr::get($budgetItem, 'value', []) ?? [], 'description');
            $this->countryBudgetItems[$this->index]['budget_item'][$index]['description'][0]['narrative'] = ($desc === '') ? $this->emptyNarrative : $desc;
        }
        $this->index++;

        return $this->countryBudgetItems;
    }

    /**
     * @param $element
     * @param $template
     *
     * @return array
     */
    public function documentLink($element, $template): array
    {
        $this->documentLink[$this->index] = $template['document_link'];
        $this->documentLink[$this->index]['url'] = $this->attributes($element, 'url');
        $this->documentLink[$this->index]['format'] = $this->attributes($element, 'format');
        $this->documentLink[$this->index]['title'][0]['narrative'] = (($title = $this->value(Arr::get($element, 'value', []), 'title')) === '') ? $this->emptyNarrative : $title;
        $this->documentLink[$this->index]['description'][0]['narrative'] = (($description = $this->value(Arr::get($element, 'value', []), 'description')) === '') ? $this->emptyNarrative : $description;
        $this->documentLink[$this->index]['category'] = $this->filterAttributes($element['value'], 'category', ['code']);
        $this->documentLink[$this->index]['language'] = $this->filterAttributes($element['value'], 'language', ['code']);
        $this->documentLink[$this->index]['document_date'][0]['date'] = dateFormat('Y-m-d', $this->attributes($element, 'iso-date', 'documentDate'));
        $this->index++;

        return $this->documentLink;
    }

    /**
     * @param $element
     * @param $template
     *
     * @return array
     */
    public function policyMarker($element, $template): array
    {
        $vocabulary = $this->attributes($element, 'vocabulary');
        $code = $this->attributes($element, 'code');

        $this->policyMarker[$this->index] = $template['policy_marker'];
        $this->policyMarker[$this->index]['policy_marker_vocabulary'] = $vocabulary;
        $this->policyMarker[$this->index]['vocabulary_uri'] = $this->attributes($element, 'vocabulary-uri');
        $this->policyMarker[$this->index]['policy_marker'] = ($vocabulary === '1') ? $code : '';
        $this->policyMarker[$this->index]['policy_marker_text'] = ($vocabulary !== '1') ? $code : '';
        $this->policyMarker[$this->index]['significance'] = $this->attributes($element, 'significance');
        $this->policyMarker[$this->index]['narrative'] = $this->narrative($element);
        $this->index++;

        return $this->policyMarker;
    }

    /**
     * Returns condition attached.
     *
     * @param $element
     *
     * @return string
     */
    public function isConditionAttached($element): string
    {
        $conditionAttached = $this->attributes($element, 'attached');

        if ((is_string($conditionAttached) && strtolower($conditionAttached) === 'true') || $conditionAttached === '1') {
            return '1';
        }

        if ((is_string($conditionAttached) && strtolower($conditionAttached) === 'false') || $conditionAttached === '0') {
            return '0';
        }

        return '';
    }

    /**
     * @param $element
     * @param $template
     *
     * @return array
     */
    public function conditions($element, $template): array
    {
        $this->conditions = $template['conditions'];
        $this->conditions['condition_attached'] = $this->isConditionAttached($element);

        if (Arr::get($element, 'value', false)) {
            foreach (Arr::get($element, 'value', []) as $index => $condition) {
                $this->conditions['condition'][$index]['condition_type'] = $this->attributes($condition, 'type');
                $this->conditions['condition'][$index]['narrative'] = $this->narrative($condition);
            }
        }

        $this->index++;

        return $this->conditions;
    }

    /**
     * @param $element
     * @param $template
     *
     * @return array
     */
    public function legacyData($element, $template): array
    {
        $this->legacyData[$this->index] = $template['legacy_data'];
        $this->legacyData[$this->index]['legacy_name'] = $this->attributes($element, 'name');
        $this->legacyData[$this->index]['value'] = $this->attributes($element, 'value');
        $this->legacyData[$this->index]['iati_equivalent'] = $this->attributes($element, 'iati-equivalent');
        $this->index++;

        return $this->legacyData;
    }

    /**
     * @param $element
     * @param $template
     *
     * @return array
     */
    public function humanitarianScope($element, $template): array
    {
        $this->humanitarianScope[$this->index] = $template['humanitarian_scope'];
        $this->humanitarianScope[$this->index]['type'] = $this->attributes($element, 'type');
        $this->humanitarianScope[$this->index]['vocabulary'] = $this->attributes($element, 'vocabulary');
        $this->humanitarianScope[$this->index]['vocabulary_uri'] = $this->attributes($element, 'vocabulary-uri');
        $this->humanitarianScope[$this->index]['code'] = $this->attributes($element, 'code');
        $this->humanitarianScope[$this->index]['narrative'] = $this->narrative($element);
        $this->index++;

        return $this->humanitarianScope;
    }

    /**
     * @param $element
     *
     * @return mixed
     */
    public function collaborationType($element): mixed
    {
        return $this->getSingleValuedField($element);
    }

    /**
     * @param $element
     *
     * @return float|null
     */
    private function capitalSpend($element): ?float
    {
        return is_numeric($this->attributes($element, 'percentage')) ? (float) $this->attributes($element, 'percentage') : $this->attributes($element, 'percentage');
    }

    /**
     * @param $element
     * @param $template
     *
     * @return array
     */
    public function relatedActivity($element, $template): array
    {
        $this->relatedActivity[$this->index] = $template['related_activity'];
        $this->relatedActivity[$this->index]['relationship_type'] = $this->attributes($element, 'type');
        $this->relatedActivity[$this->index]['activity_identifier'] = $this->attributes($element, 'ref');
        $this->index++;

        return $this->relatedActivity;
    }

    /**
     * @param array $elementData
     * @param       $template
     * @param       $orgRef
     *
     * @return array
     */
    public function map(array $elementData, $template, $orgRef): array
    {
        $this->orgRef = $orgRef;

        foreach ($elementData as $element) {
            $elementName = $this->name($element);

            if (array_key_exists($elementName, $this->activityElements)) {
                $elementData = $this->$elementName($element, $template);

                $this->activity[$this->activityElements[$elementName]] = is_array($elementData) ? $this->sanitizeData($elementData) : $elementData;
            }
        }

        if (array_key_exists('description', $this->activity)) {
            $descriptionData = array_values(Arr::get($this->activity, 'description', null));
            $this->activity['description'] = is_array($descriptionData) ? $this->sanitizeData($descriptionData) : $descriptionData;
        }

        return $this->activity;
    }

    /**
     * Read default aid type from xml.
     *
     * UNDERSTANDING THE SWITCH STATEMENT:
     *  - In the form level we can select code without having to select vocabulary.
     *  - But the code that can be selected without vocabulary belongs to AidType.json.
     *  - So moving case '1' as default case will handle that scenarios where xml contains code but not vocab.
     *  - But we still need to verify that the code actually belongs to AidType.json, so im filtering it.
     *
     *
     * @param mixed $element
     * @param mixed $template
     *
     * @return array
     */
    public function defaultAidType(mixed $element, mixed $template): array
    {
        $this->defaultAidType[$this->index] = $template['default_aid_type'];

        $vocabulary = $this->attributes($element, 'vocabulary');
        $code = $this->attributes($element, 'code');

        $currentDefaultAidType = $this->defaultAidType[$this->index];
        $currentDefaultAidType['default_aid_type_vocabulary'] = $vocabulary;

        switch ($vocabulary) {
            case '2':
                $currentDefaultAidType['earmarking_category'] = $code;
                $fields = ['default_aid_type_vocabulary', 'earmarking_category'];
                break;

            case '3':
                $currentDefaultAidType['earmarking_modality'] = strtoupper($code);
                $fields = ['default_aid_type_vocabulary', 'earmarking_modality'];
                break;

            case '4':
                $currentDefaultAidType['cash_and_voucher_modalities'] = $code;
                $fields = ['default_aid_type_vocabulary', 'cash_and_voucher_modalities'];
                break;

            default:
                $code = strtoupper($code);
                $currentDefaultAidType['default_aid_type'] = '';

                if ($this->isValidAidTypeCode($code)) {
                    $currentDefaultAidType['default_aid_type'] = $code;
                }

                $fields = ['default_aid_type_vocabulary', 'default_aid_type'];
                break;
        }

        $currentDefaultAidType = Arr::only($currentDefaultAidType, $fields);
        $this->defaultAidType[$this->index] = $currentDefaultAidType;

        $this->index++;

        return $this->defaultAidType;
    }

    /**
     * Check if code is valid by comparing against AidType.json.
     *
     * @param string $code
     *
     * @return bool
     */
    private function isValidAidTypeCode(string $code): bool
    {
        $filePath = 'AppData/Data/Activity/AidType.json';
        $aidTypes = Arr::get(json_decode(getJsonFromSource($filePath), true), 'AidType', []);
        $validCodes = array_map(fn ($item) => $item['code'], $aidTypes);

        return in_array($code, $validCodes, true);
    }

    /**
     * Read tag from xml.
     *
     * @param $element
     * @param $template
     *
     * @return array
     */
    public function tag($element, $template): array
    {
        $this->tagVariable[$this->index] = $template['tag'];
        $tagVocabulary = $this->attributes($element, 'vocabulary');

        $this->tagVariable[$this->index]['tag_vocabulary'] = $this->attributes($element, 'vocabulary');
        $this->tagVariable[$this->index]['vocabulary_uri'] = $this->attributes($element, 'vocabulary-uri');
        $this->tagVariable[$this->index]['narrative'] = $this->narrative($element);

        switch ($tagVocabulary) {
            case '2':
                $this->tagVariable[$this->index]['goals_tag_code'] = $this->attributes($element, 'code');
                $fields = ['tag_vocabulary', 'goals_tag_code', 'narrative'];

                break;
            case '3':
                $this->tagVariable[$this->index]['targets_tag_code'] = strtolower($this->attributes($element, 'code'));
                $fields = ['tag_vocabulary', 'targets_tag_code', 'narrative'];

                break;
            case '1':
            case '4':
                $this->tagVariable[$this->index]['tag_text'] = $this->attributes($element, 'code');
                $fields = ['tag_vocabulary', 'tag_text', 'narrative'];

                break;
            default:
                $this->tagVariable[$this->index]['tag_text'] = $this->attributes($element, 'code');
                $fields = ['tag_vocabulary', 'tag_text', 'narrative', 'vocabulary_uri'];

                break;
        }

        $this->tagVariable[$this->index] = Arr::only($this->tagVariable[$this->index], $fields);
        $this->index++;

        return $this->tagVariable;
    }

    /**
     * Returns location id array.
     *
     * @param $element
     *
     * @return array
     */
    public function getLocationIdData($element): array
    {
        $array = [];

        if (Arr::get($element, 'value', [])) {
            foreach (Arr::get($element, 'value', []) as $value) {
                if ($this->name($value) === 'locationId') {
                    $array[] = $this->attributes($value);
                }
            }
        }

        return $array;
    }

    /**
     * Returns org reporting org reference if reporting org reference is empty in xml.
     *
     * @param $element
     *
     * @return mixed
     */
    private function getReportingOrgRef($element): mixed
    {
        return empty($this->attributes($element, 'ref')) ? $this->organizationReportingOrg[0]['ref'] : $this->attributes($element, 'ref');
    }

    /**
     * Returns org reporting org type if reporting org type is empty in xml.
     *
     * @param $element
     *
     * @return mixed
     */
    private function getReportingOrgType($element): mixed
    {
        return empty($this->attributes($element, 'type')) ? $this->organizationReportingOrg[0]['type'] : $this->attributes($element, 'type');
    }

    /**
     * Returns org reporting org narrative if reporting org narrative is empty in xml.
     *
     * @param $element
     *
     * @return array
     */
    private function getReportingOrgNarrative($element): array
    {
        $narrativesFromXML = $this->narrative($element);
        $narrativesAtOrgLevel = $this->organizationReportingOrg[0]['narrative'];
        $narrativesToBeReturned = [];

        foreach ($narrativesAtOrgLevel as $index => $narrative) {
            if (isset($narrativesFromXML[$index])) {
                $narrativesToBeReturned[$index]['narrative'] = empty($narrativesFromXML[$index]['narrative']) ? $narrative['narrative'] : $narrativesFromXML[$index]['narrative'];
                $narrativesToBeReturned[$index]['language'] = empty($narrativesFromXML[$index]['language']) ? $narrative['language'] : $narrativesFromXML[$index]['language'];
            } else {
                $narrativesToBeReturned[$index] = $narrativesAtOrgLevel[$index];
            }
        }

        return $narrativesToBeReturned;
    }
}
