<?php

declare(strict_types=1);

namespace App\IATI\Elements\Xml;

use App\Constants\Enums;
use App\IATI\Services\Activity\ActivityPublishedService;
use App\IATI\Services\Activity\ActivityService;
use App\IATI\Services\Activity\BudgetService;
use App\IATI\Services\Activity\CapitalSpendService;
use App\IATI\Services\Activity\CollaborationTypeService;
use App\IATI\Services\Activity\ConditionService;
use App\IATI\Services\Activity\ContactInfoService;
use App\IATI\Services\Activity\CountryBudgetItemService;
use App\IATI\Services\Activity\DateService;
use App\IATI\Services\Activity\DefaultAidTypeService;
use App\IATI\Services\Activity\DefaultFinanceTypeService;
use App\IATI\Services\Activity\DefaultFlowTypeService;
use App\IATI\Services\Activity\DefaultTiedStatusService;
use App\IATI\Services\Activity\DescriptionService;
use App\IATI\Services\Activity\DocumentLinkService;
use App\IATI\Services\Activity\HumanitarianScopeService;
use App\IATI\Services\Activity\LegacyDataService;
use App\IATI\Services\Activity\LocationService;
use App\IATI\Services\Activity\OtherIdentifierService;
use App\IATI\Services\Activity\ParticipatingOrganizationService;
use App\IATI\Services\Activity\PlannedDisbursementService;
use App\IATI\Services\Activity\PolicyMarkerService;
use App\IATI\Services\Activity\RecipientCountryService;
use App\IATI\Services\Activity\RecipientRegionService;
use App\IATI\Services\Activity\RelatedActivityService;
use App\IATI\Services\Activity\ReportingOrgService;
use App\IATI\Services\Activity\ResultService;
use App\IATI\Services\Activity\ScopeService;
use App\IATI\Services\Activity\SectorService;
use App\IATI\Services\Activity\StatusService;
use App\IATI\Services\Activity\TagService;
use App\IATI\Services\Activity\TitleService;
use App\IATI\Services\Activity\TransactionService;
use App\IATI\Services\Organization\OrganizationService;
use DOMDocument;
use DOMException;
use Exception;
use Illuminate\Support\Arr;
use JsonException;
use SimpleXMLElement;

/**
 * Class XmlGenerator.
 */
class XmlGenerator
{
    /**
     * @var ActivityService
     */
    protected ActivityService $activityService;

    /**
     * @var OrganizationService
     */
    protected OrganizationService $organizationService;

    /**
     * @var TitleService
     */
    protected TitleService $titleService;

    /**
     * @var ReportingOrgService
     */
    protected ReportingOrgService $reportingOrgService;

    /**
     * @var OtherIdentifierService
     */
    protected OtherIdentifierService $otherIdentifierService;

    /**
     * @var DescriptionService
     */
    protected DescriptionService $descriptionService;

    /**
     * @var StatusService
     */
    protected StatusService $activityStatusService;

    /**
     * @var DateService
     */
    protected DateService $activityDateService;

    /**
     * @var ScopeService
     */
    protected ScopeService $activityScopeService;

    /**
     * @var RecipientCountryService
     */
    protected RecipientCountryService $recipientCountryService;

    /**
     * @var RecipientRegionService
     */
    protected RecipientRegionService $recipientRegionService;

    /**
     * @var SectorService
     */
    protected SectorService $sectorService;

    /**
     * @var TagService
     */
    protected TagService $tagService;

    /**
     * @var PolicyMarkerService
     */
    protected PolicyMarkerService $policyMarkerService;

    /**
     * @var CollaborationTypeService
     */
    protected CollaborationTypeService $collaborationTypeService;

    /**
     * @var DefaultFlowTypeService
     */
    protected DefaultFlowTypeService $defaultFlowTypeService;

    /**
     * @var DefaultFinanceTypeService
     */
    protected DefaultFinanceTypeService $defaultFinanceTypeService;

    /**
     * @var DefaultAidTypeService
     */
    protected DefaultAidTypeService $defaultAidTypeService;

    /**
     * @var DefaultTiedStatusService
     */
    protected DefaultTiedStatusService $defaultTiedStatusService;

    /**
     * @var CountryBudgetItemService
     */
    protected CountryBudgetItemService $countryBudgetItemService;

    /**
     * @var HumanitarianScopeService
     */
    protected HumanitarianScopeService $humanitarianScopeService;

    /**
     * @var CapitalSpendService
     */
    protected CapitalSpendService $capitalSpendService;

    /**
     * @var RelatedActivityService
     */
    protected RelatedActivityService $relatedActivityService;

    /**
     * @var ConditionService
     */
    protected ConditionService $conditionService;

    /**
     * @var LegacyDataService
     */
    protected LegacyDataService $legacyDataService;

    /**
     * @var DocumentLinkService
     */
    protected DocumentLinkService $documentLinkService;

    /**
     * @var ContactInfoService
     */
    protected ContactInfoService $contactInfoService;

    /**
     * @var LocationService
     */
    protected LocationService $locationService;

    /**
     * @var PlannedDisbursementService
     */
    protected PlannedDisbursementService $plannedDisbursementService;

    /**
     * @var ParticipatingOrganizationService
     */
    protected ParticipatingOrganizationService $participatingOrgService;

    /**
     * @var BudgetService
     */
    protected BudgetService $budgetService;

    /**
     * @var ResultService
     */
    protected ResultService $resultService;

    /**
     * @var TransactionService
     */
    protected TransactionService $transactionService;

    /**
     * @var ArrayToXml
     */
    protected ArrayToXml $arrayToXml;

    /**
     * @var ActivityPublishedService
     */
    protected ActivityPublishedService $activityPublishedService;

    /**
     * XmlGenerator Constructor.
     *
     * @param ActivityService $activityService
     * @param OrganizationService $organizationService
     * @param ArrayToXml $arrayToXml
     * @param ActivityPublishedService $activityPublishedService
     */
    public function __construct(
        ActivityService $activityService,
        OrganizationService $organizationService,
        ArrayToXml $arrayToXml,
        ActivityPublishedService $activityPublishedService
    ) {
        $this->activityService = $activityService;
        $this->organizationService = $organizationService;
        $this->arrayToXml = $arrayToXml;
        $this->activityPublishedService = $activityPublishedService;
    }

    /**
     * 2298
     * Generates single activity and combines xml file and publishes to IATI.
     */
    public function generateActivityXml($activity, $transaction, $result, $settings, $organization): ?DomDocument
    {
        $publishingInfo = $settings->publishing_info;
        $publisherId = Arr::get($publishingInfo, 'publisher_id', 'Not Available');
        $publishedActivity = sprintf('%s-%s.xml', $publisherId, $activity->id);
        $xml = $this->getXml($activity, $transaction, $result, $settings, $organization);
        $result = awsUploadFile(
            sprintf('%s/%s/%s', 'xml', 'activityXmlFiles', $publishedActivity),
            $xml->saveXML()
        );

        if ($result) {
            $filename = sprintf('%s-%s.xml', $publisherId, 'activities');
            $this->savePublishedFiles($filename, $activity->org_id, $publishedActivity);

            return $xml;
        }

        return null;
    }

    /**
     * Generates multiple activities and combines xml file and publishes to IATI.
     *
     * @param $activityData
     * @param $settings
     * @param $organization
     *
     * @return void
     *
     * @throws JsonException
     *
     * @throws Exception
     */
    public function generateActivitiesXml($activityData, $settings, $organization): void
    {
        $publishingInfo = $settings->publishing_info;
        $publisherId = Arr::get($publishingInfo, 'publisher_id', 'Not Available');
        $innerActivityXmlArray = [];

        foreach ($activityData as $activity) {
            $publishedActivity = sprintf('%s-%s.xml', $publisherId, $activity->id);
            $activityCompleteXml = $this->getXml($activity, $activity->transactions ?? [], $activity->results ?? [], $settings, $organization);
            $innerActivityXml = $activityCompleteXml->getElementsByTagName('iati-activity')->item(0);
            $innerActivityXml = $activityCompleteXml->saveXML($innerActivityXml);
            $activityIdentifier = $organization->identifier . '-' . $activity->iati_identifier['activity_identifier'];
            $innerActivityXmlArray[$activityIdentifier] = $innerActivityXml;
            $publishedFiles[] = $publishedActivity;

            awsUploadFile(sprintf('%s/%s/%s', 'xml', 'activityXmlFiles', $publishedActivity), $activityCompleteXml->saveXML());
        }

        if (count($innerActivityXmlArray)) {
            $filename = sprintf('%s-%s.xml', $publisherId, 'activities');
            $this->savePublishedFiles($filename, $activity->org_id, $publishedFiles);
            $this->appendMultipleInnerActivityXmlToMergedXml($innerActivityXmlArray, $settings);
        }
    }

    /**
     * Stores published file details in database.
     *
     * @param $filename
     * @param $organizationId
     * @param $publishedActivity
     *
     * @return array
     */
    public function savePublishedFiles($filename, $organizationId, $publishedActivity): array
    {
        $published = $this->activityPublishedService->findOrCreate($filename, $organizationId);
        $publishedActivities = $publishedActivity;

        if (!is_array($publishedActivity)) {
            $publishedActivities = (array) $published->published_activities;
            (in_array($publishedActivity, $publishedActivities, true)) ?: array_push($publishedActivities, $publishedActivity);
        }

        if (is_array($publishedActivity)) {
            $publishedActivities = (array) $published->published_activities;
            $publishedActivities = array_merge($publishedActivities, $publishedActivity);
        }

        $this->activityPublishedService->update($published, array_unique($publishedActivities));

        return $publishedActivities;
    }

    /**
     * Saves merged xml file which is to be published.
     *
     * @param $publishedFiles
     * @param $filename
     *
     * @return void
     * @throws DOMException
     */
    public function getMergeXml($publishedFiles, $filename): void
    {
        $dom = new DOMDocument();
        $iatiActivities = $dom->appendChild($dom->createElement('iati-activities'));
        $iatiActivities->setAttribute('version', '2.03');
        $iatiActivities->setAttribute('generated-datetime', gmdate('c'));
        $iatiActivities->appendChild($dom->createTextNode("\n"));
        $iatiActivities->appendChild($dom->createComment('Generated By IATI Publisher'));

        foreach ($publishedFiles as $xml) {
            $addDom = new DOMDocument();
            $fileContent = awsGetFile(sprintf('%s/%s/%s', 'xml', 'activityXmlFiles', $xml));

            if ($fileContent) {
                $addDom->loadXML($fileContent);

                if ($addDom->documentElement) {
                    foreach ($addDom->documentElement->childNodes as $node) {
                        $dom->documentElement->appendChild(
                            $dom->importNode($node, true)
                        );
                    }
                }
            }
        }

        $this->saveXMLFile($dom, $filename);
    }

    /**
     * Saves merged xml file.
     *
     * @param DOMDocument $dom
     * @param $filename
     *
     * @return void
     */
    public function saveXMLFile(DOMDocument $dom, $filename = null): void
    {
        $exists = awsHasFile(sprintf('%s/%s/%s', 'xml', 'mergedActivityXml', $filename));

        if ($exists) {
            awsDeleteFile(sprintf('%s/%s/%s', 'xml', 'mergedActivityXml', $filename));
        }

        awsUploadFile(sprintf('%s/%s/%s', 'xml', 'mergedActivityXml', $filename), $dom->saveXML());
    }

    /**
     * Generates individual activity xml file.
     *
     * @param $activity
     * @param $transaction
     * @param $result
     * @param $settings
     * @param $organization
     *
     * @return DomDocument|null
     * @throws JsonException
     */
    public function getXml($activity, $transaction, $result, $settings, $organization): ?DomDocument
    {
        $defaultValues = $activity->default_field_values;

        if (is_string($activity->default_field_values)) {
            $defaultValues = json_decode($activity->default_field_values, true, 512, JSON_THROW_ON_ERROR);
        }

        $this->setServices();
        $xmlData = [];
        $xmlData['@attributes'] = [
            'version' => Enums::IATI_XML_VERSION,
            'generated-datetime' => gmdate('c'),
        ];

        $xmlData['iati-activity'] = $this->getXmlData($activity, $transaction, $result, $organization);
        $xmlData['iati-activity']['@attributes'] = $this->getXmlAttributes($defaultValues);

        return $this->arrayToXml->createXml('iati-activities', $xmlData);
    }

    /**
     * Returns non empty xml attributes.
     *
     * @param $defaultValues
     * @return array
     */
    public function getXmlAttributes($defaultValues): array
    {
        $data = [
            'last-updated-datetime' => gmdate('c', time()),
            'xml:lang' => Arr::get($defaultValues, 'default_language', null),
            'default-currency' => Arr::get($defaultValues, 'default_currency', null),
            'humanitarian' => Arr::get($defaultValues, 'humanitarian', 1),
            'hierarchy' => Arr::get($defaultValues, 'hierarchy', 1),
            'budget-not-provided' => Arr::get($defaultValues, 'budget_not_provided', ''),
            'linked-data-uri' => Arr::get($defaultValues, 'linked_data_uri', ''),
        ];

        foreach ($data as $key => $datum) {
            if (empty($datum)) {
                unset($data[$key]);
            }
        }

        return $data;
    }

    /**
     * Calls ActivityService to set required service for elements.
     *
     * @return void
     */
    public function setServices(): void
    {
        $this->titleService = $this->activityService->getService('TitleService');
        $this->reportingOrgService = $this->activityService->getService('ReportingOrgService');
        $this->otherIdentifierService = $this->activityService->getService('OtherIdentifierService');
        $this->descriptionService = $this->activityService->getService('DescriptionService');
        $this->activityStatusService = $this->activityService->getService('StatusService');
        $this->activityDateService = $this->activityService->getService('DateService');
        $this->activityScopeService = $this->activityService->getService('ScopeService');
        $this->recipientCountryService = $this->activityService->getService('RecipientCountryService');
        $this->recipientRegionService = $this->activityService->getService('RecipientRegionService');
        $this->sectorService = $this->activityService->getService('SectorService');
        $this->tagService = $this->activityService->getService('TagService');
        $this->policyMarkerService = $this->activityService->getService('PolicyMarkerService');
        $this->collaborationTypeService = $this->activityService->getService('CollaborationTypeService');
        $this->defaultFlowTypeService = $this->activityService->getService('DefaultFlowTypeService');
        $this->defaultFinanceTypeService = $this->activityService->getService('DefaultFinanceTypeService');
        $this->defaultAidTypeService = $this->activityService->getService('DefaultAidTypeService');
        $this->defaultTiedStatusService = $this->activityService->getService('DefaultTiedStatusService');
        $this->countryBudgetItemService = $this->activityService->getService('CountryBudgetItemService');
        $this->humanitarianScopeService = $this->activityService->getService('HumanitarianScopeService');
        $this->capitalSpendService = $this->activityService->getService('CapitalSpendService');
        $this->relatedActivityService = $this->activityService->getService('RelatedActivityService');
        $this->conditionService = $this->activityService->getService('ConditionService');
        $this->legacyDataService = $this->activityService->getService('LegacyDataService');
        $this->documentLinkService = $this->activityService->getService('DocumentLinkService');
        $this->contactInfoService = $this->activityService->getService('ContactInfoService');
        $this->locationService = $this->activityService->getService('LocationService');
        $this->plannedDisbursementService = $this->activityService->getService('PlannedDisbursementService');
        $this->participatingOrgService = $this->activityService->getService('ParticipatingOrganizationService');
        $this->budgetService = $this->activityService->getService('BudgetService');
        $this->resultService = $this->activityService->getService('ResultService');
        $this->transactionService = $this->activityService->getService('TransactionService');
    }

    /**
     * Returns array of xml data.
     *
     * @param $activity
     * @param $transaction
     * @param $result
     * @param $organization
     *
     * @return array
     *
     * @throws JsonException
     */
    public function getXmlData($activity, $transaction, $result, $organization): array
    {
        $xmlActivity = [];
        $xmlActivity['iati-identifier'] = ($organization->identifier ?: 'Not Available') . '-' . Arr::get($activity->iati_identifier, 'activity_identifier', 'Not Available');
        $xmlActivity['reporting-org'] = $this->reportingOrgService->getXmlData($activity);
        $xmlActivity['title'] = $this->titleService->getXmlData($activity);
        $xmlActivity['description'] = $this->descriptionService->getXmlData($activity);
        $xmlActivity['participating-org'] = $this->participatingOrgService->getXmlData($activity);
        $xmlActivity['other-identifier'] = $this->otherIdentifierService->getXmlData($activity);
        $xmlActivity['activity-status'] = $this->activityStatusService->getXmlData($activity);
        $xmlActivity['activity-date'] = $this->activityDateService->getXmlData($activity);
        $xmlActivity['contact-info'] = $this->contactInfoService->getXmlData($activity);
        $xmlActivity['activity-scope'] = $this->activityScopeService->getXmlData($activity);
        $xmlActivity['recipient-country'] = $this->recipientCountryService->getXmlData($activity);
        $xmlActivity['recipient-region'] = $this->recipientRegionService->getXmlData($activity);
        $xmlActivity['location'] = $this->locationService->getXmlData($activity);
        $xmlActivity['sector'] = $this->sectorService->getXmlData($activity);
        $xmlActivity['tag'] = $this->tagService->getXmlData($activity);
        $xmlActivity['country-budget-items'] = $this->countryBudgetItemService->getXmlData($activity);
        $xmlActivity['humanitarian-scope'] = $this->humanitarianScopeService->getXmlData($activity);
        $xmlActivity['policy-marker'] = $this->policyMarkerService->getXmlData($activity);
        $xmlActivity['collaboration-type'] = $this->collaborationTypeService->getXmlData($activity);
        $xmlActivity['default-flow-type'] = $this->defaultFlowTypeService->getXmlData($activity);
        $xmlActivity['default-finance-type'] = $this->defaultFinanceTypeService->getXmlData($activity);
        $xmlActivity['default-aid-type'] = $this->defaultAidTypeService->getXmlData($activity);
        $xmlActivity['default-tied-status'] = $this->defaultTiedStatusService->getXmlData($activity);
        $xmlActivity['budget'] = $this->budgetService->getXmlData($activity);
        $xmlActivity['planned-disbursement'] = $this->plannedDisbursementService->getXmlData($activity);
        $xmlActivity['capital-spend'] = $this->capitalSpendService->getXmlData($activity);
        $xmlActivity['transaction'] = $this->transactionService->getXmlData($transaction);
        $xmlActivity['document-link'] = $this->documentLinkService->getXmlData($activity);
        $xmlActivity['related-activity'] = $this->relatedActivityService->getXmlData($activity);
        $xmlActivity['legacy-data'] = $this->legacyDataService->getXmlData($activity);
        $xmlActivity['conditions'] = $this->conditionService->getXmlData($activity);
        $xmlActivity['result'] = $this->resultService->getXmlData($result);
        removeEmptyValues($xmlActivity);

        $this->mapActivityTransactionAndResultIndex($xmlActivity, $activity);

        return $xmlActivity;
    }

    /**
     * maps transaction and result index.
     *
     * @param $xmlActivity
     * @param $activity
     *
     * @return void
     *
     * @throws JsonException
     */
    public function mapActivityTransactionAndResultIndex($xmlActivity, $activity): void
    {
        $transactions = Arr::get($xmlActivity, 'transaction', []);
        $results = Arr::get($xmlActivity, 'result', []);
        $mapper = [];

        foreach ($transactions as $transactionKey => $transaction) {
            $mapper['transactions'][] = 'transactions.' . $transactionKey;
        }

        foreach ($results as $resultKey => $result) {
            $mapper['results'][] = 'results.' . $resultKey;

            if (isset($result['indicator']) && !empty($result['indicator'])) {
                foreach ($result['indicator'] as $indicatorKey => $indicator) {
                    $mapper['indicators']["results.$resultKey"][] = "results.$resultKey.indicators." . $indicatorKey;

                    if (isset($indicator['period']) && !empty($indicator['period'])) {
                        foreach ($indicator['period'] as $periodKey => $period) {
                            $mapper['periods']["results.$resultKey.indicators.$indicatorKey"][] = "results.$resultKey.indicators.$indicatorKey.periods." . $periodKey;
                        }
                    }
                }
            }
        }

        awsUploadFile("xmlValidation/$activity->org_id/activity_mapper_$activity->id.xml", json_encode($mapper, JSON_THROW_ON_ERROR));
    }

    /**
     * Deletes the unpublished file from server.
     *
     * @param $filename
     *
     * @return void
     */
    public function deleteUnpublishedFile($filename): void
    {
        if (awsHasFile(sprintf('%s/%s/%s', 'xml', 'activityXmlFiles', $filename))) {
            awsDeleteFile(sprintf('%s/%s/%s', 'xml', 'activityXmlFiles', $filename));
        }
    }

    /**
     * Returns combined xml file which is to be downloaded.
     *
     * @param $activities
     *
     * @return string
     * @throws DOMException
     * @throws JsonException
     */
    public function getCombinedXmlFile($activities): string
    {
        $dom = new DOMDocument();
        $iatiActivities = $dom->appendChild($dom->createElement('iati-activities'));
        $iatiActivities->setAttribute('version', '2.03');
        $iatiActivities->setAttribute('generated-datetime', gmdate('c'));
        $iatiActivities->appendChild($dom->createTextNode("\n"));
        $iatiActivities->appendChild($dom->createComment('Generated By IATI Publisher'));

        foreach ($activities as $activity) {
            $addDom = new DOMDocument();
            $fileContent = $this->getXml($activity, $activity->transactions, $activity->results, $activity->organization->settings, $activity->organization)->saveXML();
            $addDom->loadXML($fileContent);

            if ($addDom->documentElement) {
                foreach ($addDom->documentElement->childNodes as $node) {
                    $dom->documentElement->appendChild(
                        $dom->importNode($node, true)
                    );
                }
            }
        }

        return $dom->saveXML();
    }

    /**
     * Appends generated/new XML content to merged xml and uploads to S3.
     *
     * @throws Exception
     */
    public function appendCompleteActivityXmlToMergedXml(DOMDocument $generatedXmlContent, $settings): void
    {
        $mergedXml = $this->getMergedXmlFromS3($settings);
        $existingActivityIdentifierList = $this->extractIatiIdentifierFromMergedXml($mergedXml);
        $targetActivityIdentifier = $generatedXmlContent->getElementsByTagName('iati-identifier')->item(0)->nodeValue;

        if (in_array($targetActivityIdentifier, $existingActivityIdentifierList)) {
            $mergedXml = removeSingleActivityXmlFromMergedActivitiesXml($mergedXml, $targetActivityIdentifier);
        }

        $mergedXmlAsString = $mergedXml->asXML();

        $generatedXmlContent = simplexml_import_dom($generatedXmlContent);
        $innerIatiActivityXmlContent = $generatedXmlContent->xpath('//iati-activity');
        $innerIatiActivityXmlContent = $innerIatiActivityXmlContent[0];
        $innerIatiActivityXmlContentAsString = $innerIatiActivityXmlContent->asXML();
        /**
         * Remove the closing tag of merged xml using string manipulation/preg_replace.
         * This helps avoid creating malformed xml when appending the inner xml.
         * Appending the closing tag to create a valid xml.
         */
        $mergedXmlAsString = preg_replace('/<\/iati-activities>/', '', $mergedXmlAsString);
        $mergedXmlAsString .= $innerIatiActivityXmlContentAsString;
        $mergedXmlAsString .= '</iati-activities>';

        $mergedXml = new DOMDocument();
        $mergedXml->loadXML($mergedXmlAsString);

        $this->putMergedXmlToS3($mergedXml, $settings);
    }

    /**
     * Removes given activity from merged xml and re-uploads it to s3.
     *
     * @param $activity
     * @param $organization
     * @param $settings
     *
     * @return void
     *
     * @throws Exception
     */
    public function removeActivityXmlFromMergedXmlInS3($activity, $organization, $settings): void
    {
        $activityIdentifier = $activity->iati_identifier['activity_identifier'];
        $targetActivityIdentifier = "$organization->identifier-$activityIdentifier";
        $mergedXml = $this->getMergedXmlFromS3($settings);
        $mergedXml = removeSingleActivityXmlFromMergedActivitiesXml($mergedXml, $targetActivityIdentifier);

        $domDocument = new DOMDocument();
        $domDocument->loadXML($mergedXml->asXml());

        $this->putMergedXmlToS3($domDocument, $settings);
    }

    /**
     * Removes multiple activities from merged xml.
     * Note:
     * - Type of $innerActivityXmlArray must be of [array of string]
     * - String must be xml string that looks like :
     *   "<iati-activity>..content..</iati-activity>".
     *
     * @param array $innerActivityXmlArray
     * @param $settings
     * @return void
     *
     * @throws Exception
     */
    public function appendMultipleInnerActivityXmlToMergedXml(array $innerActivityXmlArray, $settings): void
    {
        $mergedXml = $this->getMergedXmlFromS3($settings);
        $existingActivityIdentifierList = $this->extractIatiIdentifierFromMergedXml($mergedXml);

        foreach ($innerActivityXmlArray as $activityIdentifier => $innerActivity) {
            if (in_array($activityIdentifier, $existingActivityIdentifierList)) {
                $mergedXml = removeSingleActivityXmlFromMergedActivitiesXml($mergedXml, $activityIdentifier);
            }
        }

        $mergedXmlAsString = $mergedXml->asXML();
        $mergedXmlAsString = preg_replace('/<\/iati-activities>/', '', $mergedXmlAsString);

        foreach ($innerActivityXmlArray as $innerActivity) {
            $mergedXmlAsString .= $innerActivity;
        }

        $mergedXmlAsString .= '</iati-activities>';

        $mergedXml = new DOMDocument();
        $mergedXml->loadXML($mergedXmlAsString);

        $this->putMergedXmlToS3($mergedXml, $settings);
    }

    /**
     * Fetches merged xml from s3 and returns it as SimpleXMLElement|false.
     *
     * @param mixed $settings
     *
     * @return SimpleXMLElement|bool
     *
     * @throws Exception
     */
    private function getMergedXmlFromS3(mixed $settings): SimpleXMLElement|false
    {
        if ($settings) {
            $publisherId = Arr::get($settings, 'publishing_info.publisher_id', false);

            if ($publisherId) {
                $mergedXmlPath = "xml/mergedActivityXml/$publisherId-activities.xml";
                $mergedXmlContents = awsGetFile("$mergedXmlPath");
                $emptyRootXml = "<?xml version='1.0'?>
                    <iati-activities version='" . Enums::IATI_XML_VERSION . "' generated-datetime='" . gmdate('c') . "'>
                    </iati-activities>
                ";
                $mergedXmlContents = $mergedXmlContents ?? $emptyRootXml;

                return new SimpleXMLElement($mergedXmlContents);
            }
        }

        return false;
    }

    /**
     * Uploads merged xml to s3.
     *
     * @param DOMDocument $content
     * @param $settings
     *
     * @return void
     */
    private function putMergedXmlToS3(DOMDocument $content, $settings): void
    {
        $iatiActivitiesXml = $content->getElementsByTagName('iati-activities')->item(0);
        $iatiActivitiesXml->setAttribute('generated-datetime', gmdate('c'));

        $publisherId = Arr::get($settings, 'publishing_info.publisher_id', false);
        $filename = "$publisherId-activities.xml";
        $this->saveXMLFile($content, $filename);
    }

    /**
     * @param SimpleXMLElement $mergedXml
     *
     * @return array
     */
    public function extractIatiIdentifierFromMergedXml(SimpleXMLElement $mergedXml): array
    {
        $iatiIdentifierArray = [];

        foreach ($mergedXml->xpath('//iati-activity/iati-identifier') as $identifierElement) {
            $iatiIdentifierArray[] = (string) $identifierElement;
        }

        return $iatiIdentifierArray;
    }
}
