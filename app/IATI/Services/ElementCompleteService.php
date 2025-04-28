<?php

declare(strict_types=1);

namespace App\IATI\Services;

use App\Constants\CoreElements;
use App\IATI\Models\Activity\Activity;
use App\IATI\Services\Activity\RecipientRegionService;
use App\IATI\Traits\ElementCompleteServiceTrait;
use App\IATI\Traits\ElementDeprecationService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use JsonException;
use ReflectionMethod;

/**
 * Class ElementCompleteService.
 */
class ElementCompleteService
{
    use ElementCompleteServiceTrait;
    use ElementDeprecationService;

    /**
     * Public variable element.
     *
     * @var string
     */
    public string $element = '';

    /**
     * @var string
     */
    public $tempNarrative = '';

    /**
     * @var string
     */
    public $tempAmount = '';

    /**
     * Checks if attribute is complete.
     *
     * @param $mandatoryAttributes
     * @param $data
     *
     * @return bool
     *
     * @throws JsonException
     */
    public function isAttributeDataCompleted($mandatoryAttributes, $data): bool
    {
        if (empty($mandatoryAttributes)) {
            return true;
        }

        if (empty($data)) {
            return false;
        }

        $elementSchema = getElementSchema($this->element);

        return $this->checkAttributeDataStatus($mandatoryAttributes, $data, $elementSchema);
    }

    /**
     * Checks if single dimension attribute is complete.
     *
     * @param $data
     *
     * @return bool
     *
     * @throws JsonException
     */
    public function singleDimensionAttributeCheck($data): bool
    {
        return $this->isSingleDimensionAttributeCompleted(getElementSchema($this->element), $data);
    }

    /**
     * Checks if all element is complete.
     *
     * @param $data
     *
     * @return bool
     *
     * @throws JsonException
     */
    public function isLevelOneMultiDimensionElementCompleted($data): bool
    {
        if (is_variable_null($data)) {
            return false;
        }

        return $this->isLevelOneMultiDimensionDataCompleted(getElementSchema($this->element), $data);
    }

    /**
     * Checks if two level sub element is complete.
     *
     * @param $data
     *
     * @return bool
     *
     * @throws JsonException
     */
    public function isLevelTwoSingleDimensionElementCompleted($data): bool
    {
        if (is_variable_null($data) || !$this->singleDimensionAttributeCheck($data)) {
            return false;
        }

        $elementSchema = getElementSchema($this->element);

        return $this->isSubElementCompleted($elementSchema['sub_elements'], $data);
    }

    /**
     * Checks if two level sub element is complete.
     *
     * @param $data
     *
     * @return bool
     *
     * @throws JsonException
     */
    public function isLevelTwoMultiDimensionElementCompleted($data): bool
    {
        if (is_variable_null($data)) {
            return false;
        }

        return $this->isLevelTwoMultiDimensionDataCompleted(getElementSchema($this->element), $data);
    }

    /**
     * Checks three level sub element is complete.
     *
     * @param $data
     *
     * @return bool
     *
     * @throws JsonException
     */
    public function isLevelThreeSingleDimensionElementCompleted($data): bool
    {
        if (is_variable_null($data) || !$this->singleDimensionAttributeCheck($data)) {
            return false;
        }

        $elementSchema = getElementSchema($this->element);
        $subElements = $elementSchema['sub_elements'];

        foreach ($subElements as $key => $subElement) {
            $mandatorySubElementAttributes = $this->getMandatoryAttributes($subElement);

            if (empty($mandatorySubElementAttributes)) {
                continue;
            }

            if (
                !array_key_exists($key, $data)
                || empty($data[$key])
            ) {
                return false;
            }

            $tempData = $data[$key];

            foreach ($tempData as $datum) {
                if (!$this->isAttributeDataCompleted($mandatorySubElementAttributes, $datum)) {
                    return false;
                }
            }

            foreach ($tempData as $tempDatum) {
                if (!$this->isSubElementCompleted($subElement['sub_elements'], $tempDatum)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Returns identifier element complete status.
     *
     * @param $activity
     *
     * @return bool
     */
    public function isIatiIdentifierElementCompleted($activity): bool
    {
        $identifier = $activity->iati_identifier ?? [];

        return !(!array_key_exists('activity_identifier', $identifier) || empty($identifier['activity_identifier']));
    }

    /**
     * Returns title element complete status.
     *
     * @param $activity
     *
     * @return bool
     *
     * @throws JsonException
     */
    public function isTitleElementCompleted($activity): bool
    {
        $this->element = 'title';
        $elementSchema = getElementSchema($this->element);

        return $this->isSubElementDataCompleted(
            $this->mandatorySubElements($elementSchema['sub_elements']),
            ['narrative' => $activity->title]
        );
    }

    /**
     * Returns reporting org element complete status.
     *
     * @param $activity
     *
     * @return bool
     *
     * @throws JsonException
     */
    public function isReportingOrgElementCompleted($activity): bool
    {
        $this->element = 'reporting_org';

        return $this->isLevelOneMultiDimensionElementCompleted($activity->reporting_org);
    }

    /**
     * Returns description element complete status.
     *
     * @param $activity
     *
     * @return bool
     *
     * @throws JsonException
     */
    public function isDescriptionElementCompleted($activity): bool
    {
        $this->element = 'description';

        return $this->isLevelOneMultiDimensionElementCompleted($activity->description);
    }

    /**
     * Returns activity_date element complete status.
     *
     * @param $activity
     *
     * @return bool
     *
     * @throws JsonException
     */
    public function isActivityDateElementCompleted($activity): bool
    {
        $this->element = 'activity_date';

        return $this->isLevelOneMultiDimensionElementCompleted($activity->activity_date);
    }

    /**
     * Returns recipient_country element complete status.
     *
     * @param $activity
     *
     * @return bool
     *
     * @throws BindingResolutionException
     * @throws JsonException
     */
    public function isRecipientCountryElementCompleted($activity): bool
    {
        $this->element = 'recipient_country';

        return $this->checkIfRecipientCountryElementCompleted($activity);
    }

    /**
     * Returns budget element complete status.
     *
     * @param $activity
     *
     * @return bool
     *
     * @throws JsonException
     */
    public function isBudgetElementCompleted($activity): bool
    {
        $this->element = 'budget';

        return $this->isLevelOneMultiDimensionElementCompleted($activity->budget);
    }

    /**
     * Returns recipient_region element complete status.
     *
     * @param $activity
     *
     * @return bool
     *
     * @throws BindingResolutionException
     * @throws JsonException
     */
    public function isRecipientRegionElementCompleted($activity): bool
    {
        $this->element = 'recipient_region';

        return $this->checkIfRecipientRegionElementCompleted($activity);
    }

    /**
     * Returns default_aid_type element complete status.
     *
     * @param $activity
     *
     * @return bool
     *
     * @throws JsonException
     */
    public function isDefaultAidTypeElementCompleted($activity): bool
    {
        $this->element = 'default_aid_type';

        return $this->isLevelOneMultiDimensionElementCompleted($activity->default_aid_type);
    }

    /**
     * Returns related_activity element complete status.
     *
     * @param $activity
     *
     * @return bool
     *
     * @throws JsonException
     */
    public function isRelatedActivityElementCompleted($activity): bool
    {
        $this->element = 'related_activity';

        return $this->isLevelOneMultiDimensionElementCompleted($activity->related_activity);
    }

    /**
     * Returns description element complete status.
     *
     * @param $activity
     *
     * @return bool
     *
     * @throws JsonException
     */
    public function isSectorElementCompleted($activity): bool
    {
        $this->element = 'sector';

        if ($this->activityHasTransactionSector($activity) && $this->isSectorElementCompletedInTransaction($activity)) {
            return true;
        }

        if ((!$activity->sector) || (is_variable_null($activity->sector)) || (is_array_value_empty($activity->sector))) {
            return false;
        }

        $shouldConsiderPercentage = count(Arr::get($activity, 'sector', [])) > 1;

        foreach ($activity->sector as $sector) {
            $sectorVocab = Arr::get($sector, 'sector_vocabulary') ?? null;
            $codeKey = $this->getSectorCodeKeyBasedOnVocab((string) $sectorVocab);

            if ($this->isEmptyValue(Arr::get($sector, $codeKey))) {
                return false;
            }

            if ($shouldConsiderPercentage && $this->isEmptyValue(Arr::get($sector, 'percentage'))) {
                return false;
            }
        }

        return true;
    }

    public function isSectorElementCompletedInTransaction(Activity $activity): bool
    {
        $transactions = $activity['transactions'] ?? $activity->transactions;

        foreach ($transactions as $transaction) {
            foreach (Arr::get($transaction, 'transaction.sector', []) as $sector) {
                if (!is_array_value_empty($sector)) {
                    $sectorVocab = Arr::get($sector, 'sector_vocabulary') ?? null;
                    $codeKey = $this->getSectorCodeKeyBasedOnVocab((string) $sectorVocab);

                    if ($this->isEmptyValue(Arr::get($sector, $codeKey))) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    public function activityHasTransactionSector(Activity $activity): bool
    {
        $transactions = $activity['transactions'] ?? $activity->transactions;

        if (!empty($transactions)) {
            foreach ($transactions as $transaction) {
                $transactionSector = Arr::get($transaction, 'transaction.sector', []);

                if (!is_array_value_empty($transactionSector)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Returns humanitarian_scope element complete status.
     *
     * @param $activity
     *
     * @return bool
     *
     * @throws JsonException
     */
    public function isHumanitarianScopeElementCompleted($activity): bool
    {
        $this->element = 'humanitarian_scope';

        return $this->isLevelOneMultiDimensionElementCompleted($activity->humanitarian_scope);
    }

    /**
     * Returns legacy_data element complete status.
     *
     * @param $activity
     *
     * @return bool
     *
     * @throws JsonException
     */
    public function isLegacyDataElementCompleted($activity): bool
    {
        $this->element = 'legacy_data';

        return $this->isLevelOneMultiDimensionElementCompleted($activity->legacy_data);
    }

    /**
     * Returns tag element complete status.
     *
     * @param $activity
     *
     * @return bool
     *
     * @throws JsonException
     */
    public function isTagElementCompleted($activity): bool
    {
        $this->element = 'tag';

        return $this->isLevelOneMultiDimensionElementCompleted($activity->tag);
    }

    /**
     * Returns policy_marker element complete status.
     *
     * @param $activity
     *
     * @return bool
     *
     * @throws JsonException
     */
    public function isPolicyMarkerElementCompleted($activity): bool
    {
        $this->element = 'policy_marker';

        if (!$activity->policy_marker) {
            return false;
        }

        foreach ($activity->policy_marker as $policyMarker) {
            $isIncomplete = $this->policyMarkerVocabIsCustom($policyMarker)
                ? $this->customPolicyMarkerIsIncomplete($policyMarker)
                : $this->policyMarkerIsIncomplete($policyMarker);

            if ($isIncomplete) {
                return false;
            }
        }

        return true;
    }

    /**
     * Checks if the "participating_org" element in the given activity is complete.
     *
     * For the "participating_org" element to be considered complete, each entry within
     * $participatingOrgData must meet the following conditions:
     *  - The 'organization_role' field must have a non-empty value.
     *  - Either the 'ref' field must be non-empty, or there must be at least one non-empty
     *    'narrative' value within the 'narrative' array.
     *
     *
     * @param $activity
     *
     * @return bool
     */
    public function isParticipatingOrgElementCompleted($activity): bool
    {
        $this->element = 'participating_org';

        $participatingOrgData = $activity->participating_org;

        if (is_variable_null($participatingOrgData) || is_array_value_empty($participatingOrgData)) {
            return false;
        }

        foreach ($participatingOrgData as $participatingOrg) {
            if ($this->isEmptyValue(Arr::get($participatingOrg, 'organization_role', ''))) {
                return false;
            }

            $hasReference = !$this->isEmptyValue(Arr::get($participatingOrg, 'ref', ''));
            $hasNonEmptyNarrative = false;

            if (!$hasReference) {
                foreach (Arr::get($participatingOrg, 'narrative', []) as $narrative) {
                    if (!$this->isEmptyValue(Arr::get($narrative, 'narrative', ''))) {
                        $hasNonEmptyNarrative = true;
                        break;
                    }
                }

                if (!$hasNonEmptyNarrative) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Returns activity_status element complete status.
     *
     * @param $activity
     *
     * @return bool
     */
    public function isActivityStatusElementCompleted($activity): bool
    {
        $this->element = 'activity_status';

        return !empty($activity->activity_status);
    }

    /**
     * Returns activity_scope element complete status.
     *
     * @param $activity
     *
     * @return bool
     */
    public function isActivityScopeElementCompleted($activity): bool
    {
        $this->element = 'activity_scope';

        return !empty($activity->activity_scope);
    }

    /**
     * Returns collaboration_type element complete status.
     *
     * @param $activity
     *
     * @return bool
     */
    public function isCollaborationTypeElementCompleted($activity): bool
    {
        $this->element = 'collaboration_type';

        return !empty($activity->collaboration_type);
    }

    /**
     * Returns default_flow_type element complete status.
     *
     * @param $activity
     *
     * @return bool
     */
    public function isDefaultFlowTypeElementCompleted($activity): bool
    {
        $this->element = 'default_flow_type';

        return !empty($activity->default_flow_type);
    }

    /**
     * Returns default_finance_type element complete status.
     *
     * @param $activity
     *
     * @return bool
     */
    public function isDefaultFinanceTypeElementCompleted($activity): bool
    {
        $this->element = 'default_finance_type';

        return !empty($activity->default_finance_type);
    }

    /**
     * Returns default_tied_status element complete status.
     *
     * @param $activity
     *
     * @return bool
     */
    public function isDefaultTiedStatusElementCompleted($activity): bool
    {
        $this->element = 'default_tied_status';

        return !empty($activity->default_tied_status);
    }

    /**
     * Returns capital_spend element complete status.
     *
     * @param $activity
     *
     * @return bool
     */
    public function isCapitalSpendElementCompleted($activity): bool
    {
        $this->element = 'capital_spend';

        return $activity->capital_spend !== null;
    }

    /**
     * Returns other_identifier element complete status.
     *
     * @param $activity
     *
     * @return bool
     *
     * @throws JsonException
     */
    public function isOtherIdentifierElementCompleted($activity): bool
    {
        $this->element = 'other_identifier';

        return $this->isLevelTwoMultiDimensionElementCompleted($activity->other_identifier);
    }

    /**
     * Returns conditions element complete status.
     *
     * @param $activity
     *
     * @return bool
     *
     * @throws JsonException
     */
    public function isConditionsElementCompleted($activity): bool
    {
        $this->element = 'conditions';

        if (isset($activity->conditions) && Arr::get($activity->conditions, 'condition_attached') === '0') {
            return true;
        }

        return $this->isLevelTwoSingleDimensionElementCompleted($activity->conditions);
    }

    /**
     * Returns conditions element complete status.
     *
     * @param $activity
     *
     * @return bool
     *
     * @throws JsonException
     */
    public function isDocumentLinkElementCompleted($activity): bool
    {
        $this->element = 'document_link';

        return $this->isLevelTwoMultiDimensionElementCompleted($activity->document_link);
    }

    /**
     * Returns contact_info element complete status.
     *
     * @param $activity
     *
     * @return bool
     *
     * @throws JsonException
     */
    public function isContactInfoElementCompleted($activity): bool
    {
        $this->element = 'contact_info';

        if (!$activity->contact_info) {
            return false;
        }

        foreach ($activity->contact_info as $contactInfo) {
            $contactInfoTypeIsEmpty = empty(Arr::get($contactInfo, 'type'));
            $allOtherFields = Arr::except($contactInfo, 'type');
            $allOtherFieldsAreEmpty = is_variable_null($allOtherFields) || is_array_value_empty($allOtherFields);

            if ($contactInfoTypeIsEmpty || $allOtherFieldsAreEmpty) {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns location element complete status.
     *
     * @param $activity
     *
     * @return bool
     *
     * @throws JsonException
     */
    public function isLocationElementCompleted($activity): bool
    {
        $this->element = 'location';

        if (!$activity->location) {
            return false;
        }

        foreach ($activity->location as $location) {
            if (is_variable_null($location) || is_array_value_empty($location)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns planned_disbursement element complete status.
     *
     * @param $activity
     *
     * @return bool
     *
     * @throws JsonException
     */
    public function isPlannedDisbursementElementCompleted($activity): bool
    {
        $this->element = 'planned_disbursement';

        return $this->isLevelTwoMultiDimensionElementCompleted($activity->planned_disbursement);
    }

    /**
     * Returns country_budget_items element complete status.
     *
     * @param $activity
     *
     * @return bool
     *
     * @throws JsonException
     */
    public function isCountryBudgetItemsElementCompleted($activity): bool
    {
        $this->element = 'country_budget_items';

        return $this->isLevelThreeSingleDimensionElementCompleted($activity->country_budget_items);
    }

    /**
     * Checks if period element is completed.
     *
     * @param $data
     *
     * @return bool
     *
     * @throws JsonException
     */
    public function isPeriodElementCompleted($data): bool
    {
        $this->element = 'period';
        $elementSchema = getElementSchema($this->element);

        foreach ($data as $datum) {
            foreach (['period_start', 'period_end'] as $item) {
                if (!$this->isLevelOneMultiDimensionDataCompleted(
                    $elementSchema['sub_elements'][$item],
                    getArr($datum, $item)
                )) {
                    return false;
                }
            }

            foreach (['target', 'actual'] as $item) {
                $itemData = $datum[$item];
                $doc_element = $elementSchema['sub_elements'][$item]['sub_elements']['document_link'];

                foreach ($itemData as $itemDatum) {
                    $docs = getArr($itemDatum, 'document_link');

                    if (!$this->isLevelTwoMultiDimensionDataCompleted($doc_element, $docs)) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    /**
     * @param $data
     * @param $subElement
     *
     * @return bool
     *
     * @throws JsonException
     */
    public function isBaselineCompleted($data, $subElement): bool
    {
        foreach ($data as $baselineDatum) {
            if (
                !$this->isLevelOneMultiDimensionDataCompleted(
                    $subElement['sub_elements']['comment'],
                    getArr($baselineDatum, 'comment')
                )
                || !$this->isLevelTwoMultiDimensionDataCompleted(
                    $subElement['sub_elements']['document_link'],
                    getArr($baselineDatum, 'document_link')
                )
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Checks if result or indicator element is completed.
     *
     * @param $elementSchema
     * @param $data
     *
     * @return bool
     *
     * @throws JsonException
     */
    public function isResultAndIndicatorElementCompleted($elementSchema, $data): bool
    {
        if (
            !$this->isSingleDimensionAttributeCompleted($elementSchema, $data)
            || !$this->isLevelTwoMultiDimensionDataCompleted(
                $elementSchema['sub_elements']['document_link'],
                getArr($data, 'document_link')
            )
        ) {
            return false;
        }

        foreach (['title', 'description', 'reference'] as $item) {
            if (!$this->isLevelOneMultiDimensionDataCompleted(
                $elementSchema['sub_elements'][$item],
                getArr($data, $item)
            )) {
                return false;
            }
        }

        return true;
    }

    /**
     * Checks if indicator element is completed.
     *
     * @param $data
     *
     * @return bool
     *
     * @throws JsonException
     */
    public function isIndicatorElementCompleted($data): bool
    {
        $this->element = 'indicator';
        $elementSchema = getElementSchema($this->element);

        foreach ($data as $datum) {
            if (
                !$this->isResultAndIndicatorElementCompleted($elementSchema, $datum)
                || !$this->isBaselineCompleted($datum['baseline'], $elementSchema['sub_elements']['baseline'])
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Checks if result element is completed.
     *
     * @param $data
     *
     * @return bool
     *
     * @throws JsonException
     */
    public function isResultElementDataCompleted($data): bool
    {
        $this->element = 'result';
        $elementSchema = getElementSchema($this->element);

        foreach ($data as $datum) {
            if (!$this->isResultAndIndicatorElementCompleted($elementSchema, $datum)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns period, indicator and result data.
     *
     * @param $activity
     *
     * @return array
     */
    public function getFormattedResults($activity): array
    {
        $results = $activity->results()->with('indicators.periods')->get()->toArray();
        $resultData = [];
        $indicatorData = [];
        $periodData = [];

        foreach ($results as $result) {
            $resultId = $result['id'];
            $resultIndicators = Arr::get($result, 'indicators', []);

            if (empty($resultIndicators)) {
                /*
                 * Indicator is mandatory.
                 * Setting $resultData[i]  to empty array in-case indicator doesn't exist because:
                 * This is equivalent to doing an early return, no need to continue preparing indicator data or for this result.
                 * Checking if $resultData contains an empty [] in it will give us result completion faster than calculating if indicator for said result is complete
                 */
                $resultData[$resultId] = [];
                continue;
            }

            $resultData[$resultId] = Arr::get($result, 'result', []);
            $resultReference = Arr::get($resultData[$resultId], 'reference');

            foreach ($resultIndicators as $indicator) {
                $indicatorId = $indicator['id'];
                $indicatorId = "$resultId.$indicatorId";
                $indicatorValue = Arr::get($indicator, 'indicator', []);

                if (is_array_value_empty(Arr::get($indicatorValue, 'reference'))) {
                    $indicatorValue['reference'] = $resultReference;
                } else {
                    $resultData[$resultId]['reference'] = Arr::get($indicatorValue, 'reference');
                }

                $indicatorData[$indicatorId] = $indicatorValue;
                $periods = Arr::get($indicator, 'periods', []);

                if (!empty($periods)) {
                    foreach ($periods as $period) {
                        $periodData[] = Arr::get($period, 'period', []);
                    }
                }
            }
        }

        return [$resultData, $indicatorData, $periodData];
    }

    /**
     * Returns result element complete status.
     *
     * @param $activity
     *
     * @return bool
     *
     * @throws JsonException
     */
    public function isResultElementCompleted($activity): bool
    {
        [$resultData, $indicatorData, $periodData] = $this->getFormattedResults($activity);

        if (is_variable_null($resultData) || count($resultData) < 1) {
            return false;
        }

        $periodExists = is_array($periodData) && count($periodData) > 0;
        $indicatorExists = is_array($indicatorData) && count($indicatorData) > 0;

        if ($periodExists) {
            return $this->isPeriodElementCompleted($periodData) && $this->isIndicatorElementCompleted(
                $indicatorData
            ) && $this->isResultElementDataCompleted($resultData);
        }

        if ($indicatorExists) {
            return $this->isIndicatorElementCompleted($indicatorData) && $this->isResultElementDataCompleted(
                $resultData
            );
        }

        return false;
    }

    /**
     * @param $subElements
     * @param $data
     *
     * @return bool
     *
     * @throws JsonException
     */
    public function checkTransactionData($subElements, $data): bool
    {
        if (is_variable_null($data) || !$this->singleDimensionAttributeCheck($data)) {
            return false;
        }

        foreach ($subElements as $subElement) {
            if (!$this->isLevelOneMultiDimensionDataCompleted($subElement, $data[$subElement['name']])) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param Activity $activity
     *
     * @return bool
     *
     * @throws JsonException
     */
    public function isTransactionsElementCompleted(Activity $activity): bool
    {
        $transactionData = $activity['transactions'] ?? $activity->transactions()->get()->toArray();

        if (empty($transactionData) || count($transactionData) < 1) {
            return false;
        }

        $this->element = 'transactions';
        $elementSchema = getElementSchema($this->element);

        foreach ($transactionData as $transactionDatum) {
            if (!$this->checkTransactionData($elementSchema['sub_elements'], $transactionDatum['transaction'])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Checks if recipient Region is completed.
     *
     * @param $activity
     *
     * @return bool
     *
     * @throws BindingResolutionException
     * @throws JsonException
     */
    public function checkIfRecipientRegionElementCompleted($activity): bool
    {
        if ($this->recipientRegionIsSetInTransaction($activity)) {
            return $this->isRecipientRegionCompleteInTransaction($activity);
        }

        if ($this->recipientCountryIsSetInTransaction($activity)) {
            return $this->isRecipientCompleteCompleteInTransaction($activity);
        }

        $regionStatus = $this->isLevelOneMultiDimensionElementCompleted($activity->recipient_region);

        if (empty($activity->recipient_region) && !empty($activity->recipient_country)) {
            $countryTotalPercentage = (float) array_sum(array_column($activity->recipient_country, 'percentage'));

            if ($countryTotalPercentage === 100.0) {
                return true;
            }

            return false;
        }

        if (!empty($activity->recipient_region)) {
            $recipientRegionService = app()->make(RecipientRegionService::class);
            $groupPercentage = $recipientRegionService->groupRegion($activity->recipient_region);
            $firstGroupTotalPercentage = Arr::first($groupPercentage, static function ($value) {
                return $value;
            })['total'];

            /*
             * We are only comparing first groups percentage because all groups are expected to be same.
             * This is because recipient region request validation prevents unique vocab from having different percentages.
             * Comparing with ( 100 - % of RecipientCountry ) is the same as comparing with 100.0 if Recipient Country is empty.
             */
            if (empty($activity->recipient_country) && $firstGroupTotalPercentage !== 100.0) {
                return false;
            }

            if (!empty($activity->recipient_country)) {
                $countryTotalPercentage = (float) array_sum(array_column($activity->recipient_country, 'percentage'));
                $totalPercentage = $firstGroupTotalPercentage + $countryTotalPercentage;

                return $totalPercentage === 100.0 ? $regionStatus : false;
            }
        }

        return $regionStatus;
    }

    /**
     * Checks if recipient country is completed.
     *
     * @param $activity
     *
     * @return bool
     *
     * @throws BindingResolutionException
     * @throws JsonException
     */
    public function checkIfRecipientCountryElementCompleted($activity): bool
    {
        if ($this->recipientRegionIsSetInTransaction($activity)) {
            return $this->isRecipientRegionCompleteInTransaction($activity);
        }

        if ($this->recipientCountryIsSetInTransaction($activity)) {
            return $this->isRecipientCompleteCompleteInTransaction($activity);
        }

        $countryStatus = $this->isLevelOneMultiDimensionElementCompleted($activity->recipient_country);

        if (empty($activity->recipient_country) && !empty($activity->recipient_region)) {
            $recipientRegionService = app()->make(RecipientRegionService::class);
            $groupPercentage = $recipientRegionService->groupRegion($activity->recipient_region);
            $firstGroupTotalPercentage = Arr::first($groupPercentage, static function ($value) {
                return $value;
            })['total'];

            if ($firstGroupTotalPercentage === 100.0) {
                return true;
            }

            return false;
        }

        if (!empty($activity->recipient_country)) {
            $countryTotalPercentage = (float) array_sum(array_column($activity->recipient_country, 'percentage'));

            if (empty($activity->recipient_region) && $countryTotalPercentage != 100.0) {
                return false;
            }

            if ($countryTotalPercentage !== (100.0 - getAllocatedPercentageOfRecipientRegion($activity))) {
                return false;
            }
        }

        return $countryStatus;
    }

    /**
     * Refresh element_status of activity.
     *
     * @param $activity
     *
     * @return void
     *
     * @throws JsonException
     * @throws \ReflectionException
     */
    public function refreshElementStatus($activity): void
    {
        $orgReportingOrgStatus = Arr::get(auth()->user()->organization, 'element_status.reporting_org', false);
        $attributes = getActivityAttributes();

        $activity->element_status = $this->prepareActivityElementStatus($activity, $orgReportingOrgStatus, $attributes);
        $activity->complete_percentage = $this->calculateCompletePercentage($activity->element_status);
        $activity->timestamps = false;

        $activity->updateQuietly(['touch' => false]);
    }

    /**
     * @throws JsonException
     */
    public function refreshElementStatusForMultipleActivities($allActivities): array
    {
        $authUser = auth()->user();
        $orgReportingOrgStatus = Arr::get($authUser->organization, 'element_status.reporting_org', false);
        $attributes = getActivityAttributes();
        $activitiesData = [];

        foreach ($allActivities as $activity) {
            $activity->element_status = $this->prepareActivityElementStatus($activity, $orgReportingOrgStatus, $attributes);
            $activity->complete_percentage = $this->calculateCompletePercentage($activity->element_status);
            $activity->timestamps = false;

            $activitiesData[] = $activity->toArray();
        }

        return $activitiesData;
    }

    /**
     * @throws JsonException
     */
    public function prepareActivityDataForUpsert(int $orgId, array $activity): array
    {
        $activity['org_id'] = $orgId;
        $activity['activity_identifier'] = Arr::get($activity, 'iati_identifier.activity_identifier');

        unset($activity['transactions'], $activity['results']);

        foreach ($activity as $key => $properties) {
            if ($properties && is_array($properties)) {
                $activity[$key] = json_encode($properties, JSON_THROW_ON_ERROR);
            }
        }

        return $activity;
    }

    /**
     * Returns an array of relation names loaded in the model.
     *
     * @param Model $model
     *
     * @return array
     * @throws \ReflectionException
     */
    public function getRelationNames(Model $model): array
    {
        $relations = [];

        foreach (get_class_methods($model) as $method) {
            $reflection = new ReflectionMethod($model, $method);

            if ($reflection->isPublic() && !$reflection->isStatic() && $reflection->getNumberOfParameters() === 0) {
                $returnType = $reflection->getReturnType();

                if ($returnType && is_subclass_of($returnType->getName(), Relation::class)) {
                    $relations[] = $method;
                }
            }
        }

        return $relations;
    }

    /**
     * Calculate element complete percentage for an activity.
     *
     * @param $element_status
     *
     * @return float
     *
     * @throws JsonException
     */
    public function calculateCompletePercentage($element_status): float
    {
        $core_elements = CoreElements::all();
        $completed_core_element_count = 0;

        foreach ($core_elements as $core_element) {
            if (
                array_key_exists(
                    $core_element,
                    $element_status
                ) && $element_status[$core_element]
            ) {
                $completed_core_element_count++;
            }
        }

        return ($completed_core_element_count / count($core_elements)) * 100;
    }

    public function refreshDeprecationStatusMap($activity): void
    {
        $skippables = [
            'id',
            'org_id',
            'status',
            'created_at',
            'updated_at',
            'created_by',
            'updated_by',
            'upload_medium',
            'linked_to_iati',
            'element_status',
            'default_field_values',
            'migrated_from_aidstream',
            'complete_percentage',
        ];
        $deprecationMap = [];
        $attributes = getActivityAttributes();

        foreach ($attributes as $attribute) {
            $attributeMethod = dashesToCamelCase('does_' . $attribute . '_have_deprecated_code');

            if (is_callable([$this, $attributeMethod])) {
                $deprecationMap[$attribute] = call_user_func([$this, $attributeMethod], $activity);
            }
        }

        $activity->deprecation_status_map = $deprecationMap;
        $activity->timestamps = false;
        $activity->updateQuietly(['touch' => false]);
    }

    /**
     * @param $value
     *
     * @return bool
     */
    public function isEmptyValue($value): bool
    {
        return $value === null || (is_string($value) && trim($value) === '');
    }

    /**
     * @param $policy_marker
     *
     * @return bool
     */
    private function policyMarkerVocabIsCustom($policy_marker): bool
    {
        return Arr::get($policy_marker, 'policy_marker_vocabulary') !== '1';
    }

    /**
     * @param $policyMarker
     *
     * @return bool
     */
    private function customPolicyMarkerIsIncomplete($policyMarker): bool
    {
        return $this->isEmptyValue(Arr::get($policyMarker, 'policy_marker_text'));
    }

    /**
     * @param $policyMarker
     *
     * @return bool
     */
    private function policyMarkerIsIncomplete($policyMarker): bool
    {
        return $this->isEmptyValue(Arr::get($policyMarker, 'policy_marker'));
    }

    private function getSectorCodeKeyBasedOnVocab(?string $sectorVocab): string
    {
        return match ($sectorVocab) {
            '1'     => 'code',
            '2'     => 'category_code',
            '7'     => 'sdg_goal',
            '8'     => 'sdg_target',
            default => 'text',
        };
    }

    private function recipientCountryIsSetInTransaction($activity): bool
    {
        $transactions = $activity->transactions;

        foreach ($transactions as $transaction) {
            if (!is_array_value_empty(Arr::get($transaction, 'transaction.recipient_country', []))) {
                return true;
            }
        }

        return false;
    }

    private function recipientRegionIsSetInTransaction($activity): bool
    {
        $transactions = $activity->transactions;

        foreach ($transactions as $transaction) {
            if (!is_array_value_empty(Arr::get($transaction, 'transaction.recipient_region', []))) {
                return true;
            }
        }

        return false;
    }

    public function isRecipientRegionCompleteInTransaction($activity): bool
    {
        $transactions = $activity->transactions;

        foreach ($transactions as $transaction) {
            $recipientRegion = Arr::get($transaction, 'transaction.recipient_region.0', []);

            if (isset($recipientRegion['region_code']) && empty($recipientRegion['region_code'])) {
                return false;
            } elseif (isset($recipientRegion['custom_code']) && empty($recipientRegion['custom_code'])) {
                return false;
            }
        }

        return true;
    }

    public function isRecipientCompleteCompleteInTransaction($activity): bool
    {
        $transactions = $activity->transactions;

        foreach ($transactions as $transaction) {
            $recipientCountry = Arr::get($transaction, 'transaction.recipient_country.0', []);
            $countryCode = Arr::get($recipientCountry, 'country_code');

            if (empty($countryCode)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Prepare element_status.
     *
     * @param       $activity
     * @param bool  $orgReportingOrgStatus
     * @param array $attributes
     *
     * @return array
     */
    public function prepareActivityElementStatus($activity, bool $orgReportingOrgStatus, array $attributes): array
    {
        $elementStatus = [];

        foreach ($attributes as $attribute) {
            $methodName = dashesToCamelCase('is_' . $attribute . '_element_completed');

            if (is_callable([$this, $methodName])) {
                if ($attribute === 'reporting_org') {
                    $elementStatus[$attribute] = $orgReportingOrgStatus;
                } else {
                    $elementStatus[$attribute] = call_user_func([$this, $methodName], $activity);
                }
            }
        }

        return $elementStatus;
    }
}
