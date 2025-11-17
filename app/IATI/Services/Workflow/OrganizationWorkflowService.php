<?php

declare(strict_types=1);

namespace App\IATI\Services\Workflow;

use App\IATI\Models\Organization\Organization;
use App\IATI\Services\Organization\OrganizationPublishedService;
use App\IATI\Services\Organization\OrganizationService;
use App\IATI\Services\RegisterYourDataApi\DatasetApiService;
use App\IATI\Services\Xml\OrganizationXmlGeneratorService;

/**
 * Class OrganizationWorkflowService.
 */
class OrganizationWorkflowService
{
    /**
     * OrganizationWorkflowService Constructor.
     *
     * @param OrganizationService                                      $organizationService
     * @param OrganizationXmlGeneratorService                          $xmlGeneratorService
     * @param OrganizationPublishedService                             $organizationPublishedService
     * @param DatasetApiService $datasetApiService
     */
    public function __construct(
        protected OrganizationService $organizationService,
        protected OrganizationXmlGeneratorService $xmlGeneratorService,
        protected OrganizationPublishedService $organizationPublishedService,
        protected DatasetApiService $datasetApiService,
    ) {
    }

    /**
     * Returns desired activity.
     *
     * @param $organizationId
     *
     * @return Organization
     */
    public function findOrganization($organizationId): Organization
    {
        return $this->organizationService->getOrganizationData($organizationId);
    }

    /**
     * Publish an activity to the IATI registry.
     *
     * @param $organization
     *
     * @return void
     * @throws \App\IATI\Services\RegisterYourDataApi\RegisterYourDataApiException
     */
    public function publishOrganization($organization): void
    {
        $settings = $organization->settings;

        $organizationPublished = $this->organizationPublishedService->getOrganizationPublished($organization->id);
        $accessToken = session('oidc_access_token');
        $payload = generateDatasetApiPayload($organization);

        $this->xmlGeneratorService->generateOrganizationXml($settings, $organization);

        $_ = $organizationPublished
            ? $this->datasetApiService->updateDataset($accessToken, $organizationPublished->dataset_uuid, $payload)
            : $this->datasetApiService->createDataset($accessToken, $payload);

        $this->organizationService->updatePublishedStatus($organization, 'published', true);
    }

    /**
     * Unpublish activity from the IATI registry.
     *
     * @param $organization
     *
     * @return void
     * @throws \App\IATI\Services\RegisterYourDataApi\RegisterYourDataApiException
     */
    public function unpublishOrganization($organization): void
    {
        $organizationPublished = $this->organizationPublishedService->getOrganizationPublished($organization->id);
        $accessToken = session('oidc_access_token');
        $datasetUUID = $organizationPublished->dataset_uuid;

        if ($this->datasetApiService->getDatasetDetails($accessToken, $datasetUUID)) {
            $this->datasetApiService->deleteDataset($accessToken, $datasetUUID);
        }

        $this->organizationService->updatePublishedStatus($organization, 'draft', false);
        $this->xmlGeneratorService->deleteUnpublishedFile($organizationPublished['filename']);
    }
}
