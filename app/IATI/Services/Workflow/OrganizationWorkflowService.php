<?php

declare(strict_types=1);

namespace App\IATI\Services\Workflow;

use App\IATI\Services\Organization\OrganizationPublishedService;
use App\IATI\Services\Organization\OrganizationService;
use App\IATI\Services\Publisher\PublisherService;
use App\IATI\Services\Xml\OrganizationXmlGeneratorService;

/**
 * Class OrganizationWorkflowService.
 */
class OrganizationWorkflowService
{
    /**
     * @var OrganizationService
     */
    protected OrganizationService $organizationService;

    /**
     * @var OrganizationXmlGeneratorService
     */
    protected OrganizationXmlGeneratorService $xmlGeneratorService;

    /**
     * @var PublisherService
     */
    protected PublisherService $publisherService;

    /**
     * @var OrganizationPublishedService
     */
    protected OrganizationPublishedService $organizationPublishedService;

    /**
     * OrganizationWorkflowService Constructor.
     *
     * @param OrganizationService $organizationService
     * @param OrganizationXmlGeneratorService $xmlGeneratorService
     * @param PublisherService $publisherService
     * @param OrganizationPublishedService $organizationPublishedService
     */
    public function __construct(
        OrganizationService $organizationService,
        OrganizationXmlGeneratorService $xmlGeneratorService,
        PublisherService $publisherService,
        OrganizationPublishedService $organizationPublishedService,
    ) {
        $this->organizationService = $organizationService;
        $this->xmlGeneratorService = $xmlGeneratorService;
        $this->publisherService = $publisherService;
        $this->organizationPublishedService = $organizationPublishedService;
    }

    /**
     * Returns desired activity.
     *
     * @param $organizationId
     *
     * @return \App\IATI\Models\Organization\Organization
     */
    public function findOrganization($organizationId): \App\IATI\Models\Organization\Organization
    {
        return $this->organizationService->getOrganizationData($organizationId);
    }

    /**
     * Publish an activity to the IATI registry.
     *
     * @param $organization
     *
     * @return void
     */
    public function publishOrganization($organization): void
    {
        $settings = $organization->settings;
        $this->xmlGeneratorService->generateOrganizationXml(
            $settings,
            $organization
        );
        $organizationPublished = $this->organizationPublishedService->getOrganizationPublished($organization->id);
        $publishingInfo = $settings->publishing_info;
        $this->publisherService->publishOrganizationFile($publishingInfo, $organizationPublished, $organization);
        $this->organizationService->updatePublishedStatus($organization, 'published', true);
    }

    /**
     * Unpublish activity from the IATI registry.
     *
     * @param $organization
     *
     * @return void
     */
    public function unpublishOrganization($organization): void
    {
        $publishedFile = $this->organizationPublishedService->getOrganizationPublished($organization->id);
        $settings = $organization->settings;
        $organizationPublished = $this->organizationPublishedService->getOrganizationPublished($organization->id);
        $publishingInfo = $settings->publishing_info;
        $this->publisherService->unpublishOrganizationFile($publishingInfo, $organizationPublished);
        $this->organizationService->updatePublishedStatus($organization, 'draft', false);
        $this->xmlGeneratorService->deleteUnpublishedFile($publishedFile['filename']);
    }
}
