<?php

declare(strict_types=1);

namespace App\IATI\Elements\Xml;

use App\IATI\Models\Organization\Organization;
use App\IATI\Models\Setting\Setting;
use App\IATI\Services\Organization\DocumentLinkService;
use App\IATI\Services\Organization\NameService;
use App\IATI\Services\Organization\OrganizationIdentifierService;
use App\IATI\Services\Organization\OrganizationPublishedService;
use App\IATI\Services\Organization\OrganizationService;
use App\IATI\Services\Organization\RecipientCountryBudgetService;
use App\IATI\Services\Organization\RecipientOrgBudgetService;
use App\IATI\Services\Organization\RecipientRegionBudgetService;
use App\IATI\Services\Organization\ReportingOrgService;
use App\IATI\Services\Organization\TotalBudgetService;
use App\IATI\Services\Organization\TotalExpenditureService;
use Illuminate\Support\Arr;

/**
 * Class OrganizationXmlGenerator.
 */
class OrganizationXmlGenerator
{
    /**
     * @var OrganizationService
     */
    protected OrganizationService $organizationService;

    /**
     * @var DocumentLinkService
     */
    protected DocumentLinkService $documentLinkService;

    /**
     * @var NameService
     */
    protected NameService $nameService;

    /**
     * @var OrganizationIdentifierService
     */
    protected OrganizationIdentifierService $organizationIdentifierService;

    /**
     * @var RecipientCountryBudgetService
     */
    protected RecipientCountryBudgetService $recipientCountryBudgetService;

    /**
     * @var RecipientOrgBudgetService
     */
    protected RecipientOrgBudgetService $recipientOrgBudgetService;

    /**
     * @var RecipientRegionBudgetService
     */
    protected RecipientRegionBudgetService $recipientRegionBudgetService;

    /**
     * @var ReportingOrgService
     */
    protected ReportingOrgService $reportingOrgService;

    /**
     * @var TotalBudgetService
     */
    protected TotalBudgetService $totalBudgetService;

    /**
     * @var TotalExpenditureService
     */
    protected TotalExpenditureService $totalExpenditureService;

    /**
     * @var ArrayToXml
     */
    protected ArrayToXml $arrayToXml;

    /**
     * @var OrganizationPublishedService
     */
    protected OrganizationPublishedService $organizationPublishedService;

    /**
     * OrganizationXmlGenerator Constructor.
     *
     * @param OrganizationService $organizationService
     * @param ArrayToXml $arrayToXml
     * @param OrganizationPublishedService $organizationPublishedService
     */
    public function __construct(
        OrganizationService $organizationService,
        ArrayToXml $arrayToXml,
        OrganizationPublishedService $organizationPublishedService
    ) {
        $this->organizationService = $organizationService;
        $this->arrayToXml = $arrayToXml;
        $this->organizationPublishedService = $organizationPublishedService;
    }

    /**
     * Generates combines activities xml file and publishes to IATI.
     */
    public function generateOrganizationXml(Setting $settings, Organization $organization, bool $refreshTimestamp = true): bool
    {
        $publisherId = Arr::get($organization, 'publisher_id', 'Not Available');
        $filename = sprintf('%s-%s.xml', $publisherId, 'organisation');

        $xml = $this->getXml($settings, $organization, $refreshTimestamp);

        return awsUploadFile(sprintf('%s/%s', 'organizationXmlFiles', $filename), $xml->saveXML());
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
     * @return \DomDocument
     */
    public function getXml($settings, $organization, bool $refreshTimestamp = true): ?\DomDocument
    {
        $this->setServices();
        $xmlData = [];
        $xmlData['@attributes'] = [
            'version' => '2.03',
            'generated-datetime' => $refreshTimestamp ? gmdate('c') : getTimestampFromOrganizationXml($organization->publisher_id, $organization),
        ];
        $default_values = $settings->default_values;
        $activity_default_values = $settings->activity_default_values;

        $xmlData['iati-organisation'] = $this->getXmlData($organization);
        $xmlData['iati-organisation']['@attributes'] = [
            'last-updated-datetime' => $refreshTimestamp ? gmdate('c', time()) : getTimestampFromOrganizationXml($organization->publisher_id, $organization),
            'xml:lang'              => Arr::get($default_values, 'default_language', null),
            'default-currency'      => Arr::get($default_values, 'default_currency', null),
        ];

        return $this->arrayToXml->createXml('iati-organisations', $xmlData);
    }

    /**
     * Calls OrganizationService to set required service for elements.
     *
     * @return void
     */
    public function setServices()
    {
        $this->nameService = $this->organizationService->getService('NameService');
        $this->organizationIdentifierService = $this->organizationService->getService('OrganizationIdentifierService');
        $this->reportingOrgService = $this->organizationService->getService('ReportingOrgService');
        $this->recipientCountryBudgetService = $this->organizationService->getService('RecipientCountryBudgetService');
        $this->recipientOrgBudgetService = $this->organizationService->getService('RecipientOrgBudgetService');
        $this->recipientRegionBudgetService = $this->organizationService->getService('RecipientRegionBudgetService');
        $this->documentLinkService = $this->organizationService->getService('DocumentLinkService');
        $this->totalBudgetService = $this->organizationService->getService('TotalBudgetService');
        $this->totalExpenditureService = $this->organizationService->getService('TotalExpenditureService');
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
     */
    public function getXmlData($organization)
    {
        $xmlOrganization = [];
        $xmlOrganization['organisation-identifier'] = $this->organizationIdentifierService->getXmlData($organization);
        $xmlOrganization['name'] = $this->nameService->getXmlData($organization);
        $xmlOrganization['reporting-org'] = $this->reportingOrgService->getXmlData($organization);
        $xmlOrganization['total-budget'] = $this->totalBudgetService->getXmlData($organization);
        $xmlOrganization['recipient-org-budget'] = $this->recipientOrgBudgetService->getXmlData($organization);
        $xmlOrganization['recipient-region-budget'] = $this->recipientRegionBudgetService->getXmlData($organization);
        $xmlOrganization['recipient-country-budget'] = $this->recipientCountryBudgetService->getXmlData($organization);
        $xmlOrganization['total-expenditure'] = $this->totalExpenditureService->getXmlData($organization);
        $xmlOrganization['document-link'] = $this->documentLinkService->getXmlData($organization);
        removeEmptyValues($xmlOrganization);

        return $xmlOrganization;
    }

    /**
     * Deletes the unpublished file from server.
     */
    public function deleteUnpublishedFile(string $filename): bool
    {
        if (awsHasFile(sprintf('%s/%s', 'organizationXmlFiles', $filename))) {
            return awsDeleteFile(sprintf('%s/%s', 'organizationXmlFiles', $filename));
        }

        return true;
    }
}
