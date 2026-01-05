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
    public function publishOrganization(Organization $organization, string $accessToken): void
    {
        $settings = $organization->settings;

        $organizationPublished = $this->organizationPublishedService->getOrganizationPublished($organization->id);

        $this->xmlGeneratorService->generateOrganizationXml($settings, $organization);

        $payload = generateDatasetApiPayload($organization, 'organization', 'public');

        $response = $organizationPublished
            ? $this->datasetApiService->updateDataset($accessToken, $organizationPublished->dataset_uuid, $payload)
            : $this->datasetApiService->createDataset($accessToken, $payload);

        $filename = "$organization->publisher_id.organisation.xml";
        $organizationPublishedData = [
            'filename'              => $filename,
            'organization_id'       => $organization->id,
            'published_to_registry' => true,
            'dataset_uuid'          => $response['id'],
        ];

        $organizationPublished = $this->organizationPublishedService->findOrCreate($filename, $organization->id);
        $organizationPublished->fill($organizationPublishedData)->save();

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
    public function unpublishOrganization($organization, string $accessToken): void
    {
        $organizationPublished = $this->organizationPublishedService->getOrganizationPublished($organization->id);

        $datasetUUID = $organizationPublished->dataset_uuid;

        if ($this->datasetApiService->getDatasetDetails($accessToken, $datasetUUID)) {
            $this->datasetApiService->deleteDataset($accessToken, $datasetUUID);
        }

        $this->organizationService->updatePublishedStatus($organization, 'draft', false);
        $this->xmlGeneratorService->deleteUnpublishedFile($organizationPublished['filename']);

        $this->organizationPublishedService->delete($organizationPublished->id);
    }
}
