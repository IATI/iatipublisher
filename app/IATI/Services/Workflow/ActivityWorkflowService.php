<?php

declare(strict_types=1);

namespace App\IATI\Services\Workflow;

use App\IATI\Models\Activity\Activity;
use App\IATI\Models\Activity\ActivityPublished;
use App\IATI\Models\Organization\Organization;
use App\IATI\Models\Setting\Setting;
use App\IATI\Repositories\ApiLog\ApiLogRepository;
use App\IATI\Services\Activity\ActivityPublishedService;
use App\IATI\Services\Activity\ActivityService;
use App\IATI\Services\Activity\ActivitySnapshotService;
use App\IATI\Services\Audit\AuditService;
use App\IATI\Services\Organization\OrganizationService;
use App\IATI\Services\RegisterYourDataApi\DatasetApiService;
use App\IATI\Services\Setting\SettingService;
use App\IATI\Services\Validator\ActivityValidatorResponseService;
use App\IATI\Services\Xml\XmlGeneratorService;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Support\Arr;

/**
 * Class ActivityWorkflowService.
 */
class ActivityWorkflowService
{
    /**
     * ActivityWorkflowService Constructor.
     */
    public function __construct(
        protected OrganizationService $organizationService,
        protected SettingService $settingService,
        protected ActivityService $activityService,
        protected XmlGeneratorService $xmlGeneratorService,
        protected ActivityPublishedService $activityPublishedService,
        protected ActivitySnapshotService $activitySnapshotService,
        protected ActivityValidatorResponseService $validatorService,
        protected ApiLogRepository $apiLogRepo,
        protected AuditService $auditService,
        protected DatasetApiService $datasetApiService,
    ) {
    }

    /**
     * Returns desired activity.
     *
     * @param $activityId
     *
     * @return object
     */
    public function findActivity($activityId): object
    {
        return $this->activityService->getActivity($activityId);
    }

    /**
     * Publish an activities to the IATI registry.
     *
     * @throws \App\IATI\Services\RegisterYourDataApi\RegisterYourDataApiException
     * @throws \JsonException
     * @throws Exception
     */
    public function publishActivities(object $activities, Organization $organization, Setting $settings, string $accessToken, bool|string $uuid = false): void
    {
        if ($uuid) {
            $this->xmlGeneratorService->setUuid($uuid);
        }

        $publisherId = Arr::get($organization, 'publisher_id', false);
        $mergedFileName = "{$publisherId}-activities.xml";

        $generationData = $this->xmlGeneratorService->generateActivitiesXml(
            $activities,
            $settings,
            $organization
        );

        $successfullyProcessedActivities = $generationData['activities'];
        $innerActivityXmlArray = $generationData['inner_activity_xmls'];
        $publishedActivityFileNames = $generationData['single_xml_filenames'];
        $activityMappedToActivityIdentifier = $generationData['activity_mapped_to_identifiers'];

        $activityPublished = $this->activityPublishedService->getActivityPublished($organization->id);

        if (!empty($innerActivityXmlArray)) {
            $this->xmlGeneratorService->appendMultipleInnerActivityXmlToMergedXml(
                $innerActivityXmlArray,
                $settings,
                $organization,
                $activityMappedToActivityIdentifier
            );
        }

        $payload = generateDatasetApiPayload($organization, 'activities', 'public');

        $response = $activityPublished
            ? $this->datasetApiService->updateDataset($accessToken, $activityPublished->dataset_uuid, $payload)
            : $this->datasetApiService->createDataset($accessToken, $payload);

        $mergedXmlPath = "xml/mergedActivityXml/$mergedFileName";
        $mergedFilesize = calculateStringSizeInMb(awsGetFile($mergedXmlPath));

        $this->activityPublishedService->trackActivityPublished($organization->id, $mergedFileName, $publishedActivityFileNames, $mergedFilesize, $response['id']);

        $activityIds = $activities->pluck('id')->toArray();
        $this->activityService->bulkUpdatePublishedStatus($activityIds, 'published', true);
    }

    /**
     * Unpublish activity and then republish required file to the IATI registry.
     *
     * @throws \App\IATI\Services\RegisterYourDataApi\RegisterYourDataApiException
     * @throws Exception
     */
    public function unpublishActivity(Activity $activity, string $accessToken): void
    {
        $organization = $activity->organization;
        $settings = $organization->settings;

        $activityPublished = $this->activityPublishedService->getActivityPublished($organization->id);
        $payload = generateDatasetApiPayload($organization, 'activities');

        $_ = $this->removeActivityFromPublishedArray($activityPublished, $activity);

        $this->xmlGeneratorService->removeActivityXmlFromMergedXmlInS3($activity, $organization, $settings);

        $this->activityService->updatePublishedStatus($activity, 'draft', false);

        if (count($organization->allActivities->where('status', 'published')) === 0) {
            $this->datasetApiService->deleteDataset($accessToken, $activityPublished->dataset_uuid);
            $this->activityPublishedService->deleteActivity($activityPublished->id);
        } else {
            $this->datasetApiService->updateDataset($accessToken, $activityPublished->dataset_uuid, $payload);
        }

        $this->validatorService->deleteValidatorResponse($activity->id);
    }

    /**
     * Removes activity file name from activity published row.
     *
     * @param ActivityPublished $activityPublished
     * @param Activity          $activity
     *
     * @return bool
     */
    public function removeActivityFromPublishedArray(ActivityPublished $activityPublished, Activity $activity): bool
    {
        $containedActivities = $activityPublished->extractActivities();
        $newPublishedFiles = Arr::except($containedActivities, $activity->id);

        return $this->activityPublishedService->update(
            $activityPublished->id,
            [
                'published_activities'=>array_values($newPublishedFiles),
            ]
        );
    }

    /**
     * Validates the activity on IATI validator and returns errors.
     *
     * @param $activity
     *
     * @return string
     *
     * @throws GuzzleException
     * @throws \JsonException
     */
    public function validateActivityOnIATIValidator($activity): string
    {
        if (!$activity->relationLoaded('transactions') || !$activity->relationLoaded('results.indicators.periods')) {
            $activity->load(['transactions', 'results.indicators.periods']);
        }

        $organization = $activity->organization;
        $settings = $organization->settings;

        $xmlData = $this->xmlGeneratorService->getActivityXmlData(
            $activity,
            $activity->transactions,
            $activity->results,
            $settings,
            $organization
        );

        awsUploadFile("xmlValidation/$activity->org_id/activity_$activity->id.xml", $xmlData);

        return $this->getResponse($xmlData);
    }

    /**
     * Returns response of validation activity on IATI validator.
     *
     * @param $xmlData
     *
     * @return string
     *
     * @throws BadResponseException
     * @throws GuzzleException
     */
    public function getResponse($xmlData): string
    {
        $client = new Client();
        $URI = env('IATI_VALIDATOR_ENDPOINT');
        $params['headers'] = [
            'Content-Type'              => 'application/json',
            'Ocp-Apim-Subscription-Key' => env('IATI_VALIDATOR_KEY'),
        ];
        $params['query'] = ['group' => 'false', 'details' => 'true'];
        $params['body'] = $xmlData;
        $response = $client->post($URI, $params);

        return $response->getBody()->getContents();
    }

    public function getResponseAsync(string $xmlData): PromiseInterface
    {
        $client = new Client();
        $URI = env('IATI_VALIDATOR_ENDPOINT');
        $params = [
            'headers' => [
                'Content-Type'              => 'application/json',
                'Ocp-Apim-Subscription-Key' => env('IATI_VALIDATOR_KEY'),
            ],
            'query'   => [
                'group'   => 'false',
                'details' => 'true',
            ],
            'body'    => $xmlData,
        ];

        return $client->postAsync($URI, $params);
    }

    /**
     * Returns errors related to publishing activity.
     *
     * @param        $organization
     * @param string $type
     *
     * @return array
     *
     * @throws \JsonException
     * @throws GuzzleException
     */
    public function getPublishErrorMessage($organization, string $type = 'activity'): array
    {
        $messages = [];

        if ($organization->publisher_id === env('PRODUCTION_TEST_ACC')) {
            return $messages;
        }

        if (!$organization->registry_approved) {
            $messages[] = 'Your organisation is pending approval by the IATI team.';
        }

        if ($type === 'activity' && !$this->isOrganizationPublished($organization)) {
            $messages[] = 'Your Organisation data is not published.';
        }

        return $messages;
    }

    /**
     * Checks of organization is published or not.
     *
     * @param $organization
     *
     * @return bool
     */
    public function isOrganizationPublished($organization): bool
    {
        return $organization->is_published;
    }

    /**
     * Deletes the unpublished file.
     *
     * @param $activity
     *
     * @return void
     */
    public function deletePublishedFile($activity): void
    {
        $settings = $activity->organization->settings;
        $publishingInfo = $settings->publishing_info;
        $publisherId = Arr::get($publishingInfo, 'publisher_id', 'Not Available');
        $publishedActivity = sprintf('%s-%s.xml', $publisherId, $activity->id);

        $this->xmlGeneratorService->deleteUnpublishedFile($publishedActivity);
    }
}
