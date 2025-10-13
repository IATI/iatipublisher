<?php

declare(strict_types=1);

namespace App\IATI\Services\Workflow;

use App\IATI\Repositories\Activity\ValidationStatusRepository;
use App\IATI\Repositories\ApiLog\ApiLogRepository;
use App\IATI\Services\Activity\ActivityPublishedService;
use App\IATI\Services\Activity\ActivityService;
use App\IATI\Services\Activity\BulkPublishingStatusService;
use App\IATI\Services\Validator\ActivityValidatorResponseService;
use App\IATI\Traits\IatiValidatorResponseTrait;
use App\Jobs\RegistryValidatorJob;
use App\XlsImporter\Foundation\Factory\Validation;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * Class BulkPublishingService.
 */
class BulkPublishingService
{
    use IatiValidatorResponseTrait;

    /**
     * @var ActivityService
     */
    protected ActivityService $activityService;

    /**
     * @var ActivityWorkflowService
     */
    protected ActivityWorkflowService $activityWorkflowService;

    /**
     * @var ActivityValidatorResponseService
     */
    protected ActivityValidatorResponseService $validatorService;

    /**
     * @var BulkPublishingStatusService
     */
    protected BulkPublishingStatusService $publishingStatusService;

    protected ActivityPublishedService $activityPublishedService;

    /**
     * @var ApiLogRepository
     */
    protected ApiLogRepository $apiLogRepo;

    /**
     * @var ValidationStatusRepository
     */
    protected ValidationStatusRepository $validationStatusRepository;

    /**
     * BulkPublishingService Constructor.
     *
     * @param ActivityService $activityService
     * @param ActivityWorkflowService $activityWorkflowService
     * @param ActivityValidatorResponseService $validatorService
     * @param BulkPublishingStatusService $publishingStatusService
     * @param ApiLogRepository $apiLogRepo
     */
    public function __construct(
        ActivityService $activityService,
        ActivityWorkflowService $activityWorkflowService,
        ActivityValidatorResponseService $validatorService,
        BulkPublishingStatusService $publishingStatusService,
        ApiLogRepository $apiLogRepo,
        ValidationStatusRepository $validationStatusRepository,
        ActivityPublishedService $activityPublishedService,
    ) {
        $this->activityService = $activityService;
        $this->activityWorkflowService = $activityWorkflowService;
        $this->validatorService = $validatorService;
        $this->publishingStatusService = $publishingStatusService;
        $this->apiLogRepo = $apiLogRepo;
        $this->validationStatusRepository = $validationStatusRepository;
        $this->activityPublishedService = $activityPublishedService;
    }

    /**
     * Returns array with information of activities which have completed core elements and which have not.
     *
     * @param $activityIds
     *
     * @return array
     */
    public function getCoreElementsCompletedArray($activityIds): array
    {
        $coreElementsCompleted = [];
        $coreElementsCompleted['complete'] = [];
        $coreElementsCompleted['incomplete'] = [];

        if (count($activityIds)) {
            foreach ($activityIds as $id) {
                $activity = $this->activityService->getActivity($id);

                if ($activity && $activity->status === 'draft') {
                    $coreElementsCompleted[$this->getCompleteStatus($activity)][] = [
                        'activity_id' => $activity->id,
                        'title' => Arr::get($activity->title, '0.narrative', 'Not Available') ?: 'Not Available',
                    ];
                }
            }
        }

        return $coreElementsCompleted;
    }

    /**
     * Returns whether activity is completed or not.
     *
     * @param $activity
     *
     * @return string
     */
    public function getCompleteStatus($activity): string
    {
        $activityElementStatus = $activity->element_status;
        $activityElementStatus['reporting_org'] = $activity->organization->reporting_org_element_completed;

        if (isCoreElementCompleted($activityElementStatus)) {
            return 'complete';
        }

        return 'incomplete';
    }

    /**
     * Validates activities on IATI publisher and returns required response.
     *
     * @param $activityIds
     *
     * @return array
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function validateActivitiesOnIATI($activityIds): array
    {
        $user = Auth::user();
        $activityTitles = [];
        $activities = $this->activityService->getActivitiesHavingIds($activityIds);

        /** @var $validationStatusRepository ValidationStatusRepository */
        $validationStatusRepository = app()->make(ValidationStatusRepository::class);
        $validationStatusRepository->insertInitialValidatorResponseDataForProperResponse($activityIds, (int) $user->id);

        foreach ($activities as $activity) {
            if ($activity && $activity->status === 'draft') {
                $activityTitles[] = $activity->default_title_narrative;
            }
        }

        foreach ($activities as $activity) {
            if ($activity && $activity->status === 'draft') {
                RegistryValidatorJob::dispatch($activity, $user);
            }
        }

        return $activityTitles;
    }

    /**
     * Validates activity on IATI publisher and handles exception.
     *
     * @param $activity
     *
     * @return array
     * @throws GuzzleException
     * @throws \JsonException
     */
    public function validateWithException($activity): array
    {
        try {
            $validatorResponse = $this->activityWorkflowService->validateActivityOnIATIValidator($activity);
            $response = $this->addElementOnIatiValidatorResponse($validatorResponse, $activity);

            if ($this->validatorService->updateOrCreateResponse($activity->id, $response)) {
                return $response;
            }

            return ['success' => false, 'error' => 'Error has occurred while validating activity.'];
        } catch (BadResponseException $ex) {
            if ($ex->getCode() === 422) {
                $validatorResponse = $ex->getResponse()->getBody()->getContents();
                $response = json_decode($validatorResponse, true, 512, JSON_THROW_ON_ERROR);

                if ($this->validatorService->updateOrCreateResponse($activity->id, $response)) {
                    return $response;
                }
            }

            return ['success' => false, 'error' => 'Error has occurred while validating activity.'];
        } catch (\Exception $e) {
            logger()->error($e);
            logger()->error($e);

            throw new \RuntimeException();
        }
    }

    /**
     * Modifies response obtained from IATI publisher to send to frontend.
     *
     * @param $totalResponse
     *
     * @return array
     */
    public function modifyResponse($totalResponse): array
    {
        $modifiedResponse = [
            'no_errors' => [],
            'errors' => [],
            'critical' => [],
        ];

        foreach ($totalResponse as $response) {
            $modifiedResponse = $this->checkActivityError($response, $modifiedResponse);
        }

        return $modifiedResponse;
    }

    /**
     * Checks errors in activity and returns modified array.
     *
     * @param $response
     * @param $modifiedResponse
     *
     * @return array
     */
    public function checkActivityError($response, $modifiedResponse): array
    {
        $summary = Arr::get($response, 'response.summary', []);

        if (array_sum($summary) === 0) {
            $modifiedResponse['no_errors'][] = [
                'activity_id' => Arr::get($response, 'activity_id', 'Not Available'),
                'title' => Arr::get($response, 'title', 'Not Available'),
                'errors' => [],
            ];
        }

        if (Arr::get($summary, 'critical', 0) > 0) {
            $modifiedResponse['critical'][] = [
                'activity_id' => Arr::get($response, 'activity_id', 'Not Available'),
                'title' => Arr::get($response, 'title', 'Not Available'),
                'errors' => [
                    'critical' => Arr::get($summary, 'critical', 0),
                ],
            ];
        }

        if ((Arr::get($summary, 'error', 0) > 0) || (Arr::get($summary, 'warning', 0) > 0)) {
            $modifiedResponse['errors'][] = [
                'activity_id' => Arr::get($response, 'activity_id', 'Not Available'),
                'title' => Arr::get($response, 'title', 'Not Available'),
                'errors' => [
                    'error' => Arr::get($summary, 'error', 0),
                    'warning' => Arr::get($summary, 'warning', 0),
                ],
            ];
        }

        return $modifiedResponse;
    }

    /**
     * Returns initial response after starting bulk publishing of activities.
     *
     * @param $activities
     *
     * @return array
     */
    public function generateInitialBulkPublishingResponse($activities): array
    {
        $response = [];
        $response['status'] = 'processing';
        $response['organization_id'] = auth()->user()->organization->id;
        $response['job_batch_uuid'] = (string) Str::uuid();
        $response['message'] = 'Bulk publishing started';
        $response['activities'] = [];

        if (count($activities)) {
            foreach ($activities as $activity) {
                $response['activities'][$activity->id]['activity_id'] = $activity->id;
                $response['activities'][$activity->id]['activity_title'] = Arr::get($activity->title, '0.narrative', 'Not Available') ?: 'Not Available';
                $response['activities'][$activity->id]['status'] = 'created';
            }
        }

        return $response;
    }

    /**
     * Get response during bulk publishing of activities.
     *
     * @param $publishStatus
     *
     * @return array
     */
    public function getPublishingResponse($publishStatus): array
    {
        $processing = false;

        $response = [];
        $response['status'] = 'completed';
        $response['organization_id'] = $publishStatus->first()->organization_id;
        $response['job_batch_uuid'] = $publishStatus->first()->job_batch_uuid;
        $response['message'] = 'Bulk publishing completed';
        $response['activities'] = [];

        if (count($publishStatus)) {
            foreach ($publishStatus as $status) {
                if ($status->status === 'created' || $status->status === 'processing') {
                    $processing = true;
                }

                $response['activities'][$status->activity_id]['activity_id'] = $status->activity_id;
                $response['activities'][$status->activity_id]['activity_title'] = $status->activity_title;
                $response['activities'][$status->activity_id]['status'] = $status->status;
            }
        }

        if ($processing) {
            $response['status'] = 'processing';
            $response['message'] = 'Bulk publishing processing.';
        }

        return $response;
    }

    /**
     * Stop bulk publishing.
     *
     * @param $organizationId
     *
     * @return array
     */
    public function stopBulkPublishing($organizationId): array
    {
        return $this->publishingStatusService->stopBulkPublishing($organizationId);
    }

    /**
     * Get status of bulk publishing for users belonging to same org.
     *
     * @return array
     *
     * @throws \Exception
     */
    public function getOrganisationBulkPublishingStatus(): array
    {
        $organizationId = auth()->user()->organization->id;

        $publishStatus = $this->publishingStatusService->getActivityPublishingStatus($organizationId);

        if ($publishStatus && count($publishStatus)) {
            $response = $this->getPublishingResponse($publishStatus);

            return ['inProgress' => true, 'publishingData' => $response];
        }

        return ['inProgress' => false, 'publishingData' => []];
    }

    /**
     * Returns status of activity publish based on activity table.
     *
     * @param $activityId
     * @param $uuid
     *
     * @return bool|int|string
     */
    public function checkActivityStatusTest($activityId, $uuid): bool|int|string
    {
        $activity = $this->activityService->getActivity($activityId);
        $returnMsg = false;

        if ($activity->status != 'published') {
            if ($this->publishingStatusService->updateActivityStatus($activityId, $uuid, 'failed')) {
                $returnMsg = 'failed';
            }
        }

        return $returnMsg;
    }

    /**
     * Delete bulk publishing status having organizationId.
     *
     * @param $organizationId
     *
     * @return bool
     */
    public function deleteBulkPublishingStatus($organizationId): bool
    {
        return $this->publishingStatusService->deleteBulkPublishingStatus($organizationId);
    }

    /**
     * Returns validation status of all activity.
     *
     * @param array $activityIds
     *
     * @return array
     */
    public function getActivityValidationStatus(array $activityIds): array
    {
        return $this->validationStatusRepository->getActivitiesValidationStatus($activityIds);
    }

    /**
     * Get activities validation responses.
     *
     * @param array $activityIds
     *
     * @return array
     */
    public function getValidationResponses(array $activityIds): array
    {
        $totalResponse = [];
        $responses = $this->validationStatusRepository->getActivitiesValidationResponse($activityIds);

        if (!empty($responses) && count($responses)) {
            foreach ($responses as $response) {
                $response = json_decode($response, true);
                $totalResponse[$response['activity_id']] = $response;
            }
        }

        return $totalResponse;
    }

    /**
     * Checks if there is previous validation.
     *
     * @return array
     */
    public function checkOngoingValidationStatus(): array
    {
        return $this->validationStatusRepository->checkOngoingValidationStatus();
    }

    /**
     * Returns validation status regardless of it status.
     *
     * @return bool
     */
    public function checkPreviousValidationStatusPending(): bool
    {
        $count = $this->validationStatusRepository->checkPreviousValidationStatusPending();

        if ($count) {
            return true;
        }

        return false;
    }

    /**
     * Deletes validation status.
     *
     * @return int
     */
    public function deleteValidationResponses(): int
    {
        return $this->validationStatusRepository->deleteValidationResponses();
    }

    /**
     * @param mixed $activityIds
     * @param ActivityService $activityService
     *
     * @return array
     */
    public function getActivitiesWithDeprecatedValueArray(mixed $activityIds, ActivityService $activityService): array
    {
        $activities = $activityService->getActivitiesHavingIds($activityIds);
        $returnArr = [];

        foreach ($activities as $index => $activity) {
            $activityResults = $activity->results?->toArray() ?? [];
            $activityTransactions = $activity->transactions?->toArray() ?? [];

            $allDeprecationMaps = [
                'activity'     => $activity['deprecation_status_map'],
                'results'      => array_column($activityResults, 'deprecation_status_map'),
                'transactions' => array_column($activityTransactions, 'deprecation_status_map'),
            ];

            $allDeprecationMaps = flattenArrayWithKeys($allDeprecationMaps);

            if (arrayOr($allDeprecationMaps)) {
                $returnArr[$index] = ['activity_id'=> $activity->id, 'title'=>Arr::get($activity, 'title.0.narrative', 'No title')];
            }
        }

        return $returnArr;
    }

    /**
     * Returns array of activity_id of the activities that do not contain critical error.
     *
     * @param array $activityIds
     *
     * @return array
     */
    public function getPublishableActivityIds(array $activityIds): array
    {
        return $this->validationStatusRepository->getActivitiesWithNoCriticalErrors($activityIds);
    }
}
