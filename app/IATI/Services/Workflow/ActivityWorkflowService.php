<?php

declare(strict_types=1);

namespace App\IATI\Services\Workflow;

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
    private Client $client;

    /**
     * ActivityWorkflowService Constructor.
     *
     * @param OrganizationService                                      $organizationService
     * @param SettingService                                           $settingService
     * @param ActivityService                                          $activityService
     * @param XmlGeneratorService                                      $xmlGeneratorService
     * @param ActivityPublishedService                                 $activityPublishedService
     * @param ActivitySnapshotService                                  $activitySnapshotService
     * @param ActivityValidatorResponseService                         $validatorService
     * @param ApiLogRepository                                         $apiLogRepo
     * @param AuditService                                             $auditService
     * @param DatasetApiService $datasetApiService
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
        $this->client = new Client();
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
     * Publish an activity to the IATI registry.
     *
     * @param $activity
     * @param bool $publishFile
     *
     * @return void
     *
     * @throws Exception
     */
    public function publishActivity($activity, bool $publishFile = true): void
    {
        $organization = $activity->organization;
        $settings = $organization->settings;
        $generatedXmlContent = $this->xmlGeneratorService->generateActivityXml(
            $activity,
            $activity->transactions,
            $activity->results,
            $settings,
            $organization
        );

        if ($generatedXmlContent) {
            $this->xmlGeneratorService->appendCompleteActivityXmlToMergedXml($generatedXmlContent, $settings, $activity, $organization);
        } else {
            throw new Exception('Failed appending new activity to merged xml.');
        }

        if ($publishFile) {
            $activityPublished = $this->activityPublishedService->getActivityPublished($organization->id);
            $accessToken = session('oidc_access_token');
            $payload = generateDatasetApiPayload($organization, 'activities');

            $_ = $activityPublished
                ? $this->datasetApiService->updateDataset($accessToken, $activityPublished->dataset_uuid, $payload)
                : $this->datasetApiService->createDataset($accessToken, $payload);
        }

        $organizationIdentifier = $activity->organization->identifier;
        $iatiIdentifier = [
            'activity_identifier'             => $activity->activity_identifier,
            'iati_identifier_text'            => $organizationIdentifier . '-' . $activity->activity_identifier,
            'present_organization_identifier' => $organizationIdentifier,
        ];

        $this->activityService->updateActivity($activity->id, [
            'status'                  => 'published',
            'linked_to_iati'          => true,
            'iati_identifier'         => $iatiIdentifier,
            'has_ever_been_published' => true,
        ]);
        $this->activitySnapshotService->createOrUpdateActivitySnapshot($activity);
    }

    /**
     * Publish an activities to the IATI registry.
     *
     * @param             $activities
     * @param             $organization
     * @param             $settings
     * @param bool        $publishFile
     * @param bool|string $uuid
     *
     * @return void
     *
     * @throws \JsonException
     * @throws \App\IATI\Services\RegisterYourDataApi\RegisterYourDataApiException
     */
    public function publishActivities($activities, $organization, $settings, bool $publishFile = true, bool|string $uuid = false): void
    {
        if ($uuid) {
            $this->xmlGeneratorService->setUuid($uuid);
        }

        $successfullyProcessedActivities = $this->xmlGeneratorService->generateActivitiesXml(
            $activities,
            $settings,
            $organization
        );

        $activityPublished = $this->activityPublishedService->getActivityPublished($organization->id);
        $accessToken = session('oidc_access_token');
        $payload = generateDatasetApiPayload($organization, 'activities');

        $_ = $activityPublished
            ? $this->datasetApiService->updateDataset($accessToken, $activityPublished->dataset_uuid, $payload)
            : $this->datasetApiService->createDataset($accessToken, $payload);

        $publisherId = Arr::get($organization, 'publisher_id', false);
        $mergedXmlPath = "xml/mergedActivityXml/$publisherId-activities.xml";
        $mergedFilesize = calculateStringSizeInMb(awsGetFile($mergedXmlPath));

        $this->activityPublishedService->updateFilesize($activityPublished, $mergedFilesize);

        foreach ($successfullyProcessedActivities as $activity) {
            $this->activityService->updatePublishedStatus($activity, 'published', true);
            $this->activitySnapshotService->createOrUpdateActivitySnapshot($activity);
        }
    }

    /**
     * Unpublish activity and then republish required file to the IATI registry.
     *
     * @param $activity
     *
     * @return void
     *
     * @throws Exception
     */
    public function unpublishActivity($activity): void
    {
        $organization = $activity->organization;
        $settings = $organization->settings;
        $publishedFile = $this->activityPublishedService->getActivityPublished($activity->org_id);

        $this->removeActivityFromPublishedArray($publishedFile, $activity);

        $this->xmlGeneratorService->removeActivityXmlFromMergedXmlInS3($activity, $organization, $settings);

        $activityPublished = $this->activityPublishedService->getActivityPublished($organization->id);
        $accessToken = session('oidc_access_token');
        $payload = generateDatasetApiPayload($organization, 'activities');

        $_ = $this->datasetApiService->updateDataset($accessToken, $activityPublished->dataset_uuid, $payload);

        $this->activityService->updatePublishedStatus($activity, 'draft', false);
        $this->validatorService->deleteValidatorResponse($activity->id);
    }

    /**
     * Removes activity file name from activity published row.
     *
     * @param $publishedFile
     * @param $activity
     *
     * @return void
     */
    public function removeActivityFromPublishedArray($publishedFile, $activity): void
    {
        $containedActivities = $publishedFile->extractActivities();
        $newPublishedFiles = Arr::except($containedActivities, $activity->id);
        $this->activityPublishedService->updateActivityPublished($publishedFile, $newPublishedFiles);
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
        $params['headers'] = ['Content-Type' => 'application/json', 'Ocp-Apim-Subscription-Key' => env('IATI_VALIDATOR_KEY')];
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
     * Returns if logged in user is verified or not.
     *
     * @return bool
     */
    public function isUserVerified(): bool
    {
        return !is_null(auth()->user()->email_verified_at);
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

        if (!$this->isUserVerified()) {
            $messages[] = 'You have not verified your email address.';
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
