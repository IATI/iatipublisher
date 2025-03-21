<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Workflow;

use App\Constants\Enums;
use App\Exceptions\MaxBatchSizeExceededException;
use App\Exceptions\MaxMergeSizeExceededException;
use App\Helpers\BulkPublishCacheHelper;
use App\Http\Controllers\Controller;
use App\IATI\Elements\Xml\XmlGenerator;
use App\IATI\Services\Activity\ActivityPublishedService;
use App\IATI\Services\Activity\ActivityService;
use App\IATI\Services\Activity\BulkPublishingStatusService;
use App\IATI\Services\Workflow\ActivityWorkflowService;
use App\IATI\Services\Workflow\BulkPublishingService;
use App\Jobs\BulkPublishActivities;
use Auth;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Class BulkPublishingController.
 */
class BulkPublishingController extends Controller
{
    /**
     * @var BulkPublishingService
     */
    protected BulkPublishingService $bulkPublishingService;

    /**
     * @var ActivityService
     */
    protected ActivityService $activityService;

    /**
     * @var ActivityWorkflowService
     */
    protected ActivityWorkflowService  $activityWorkflowService;

    protected ActivityPublishedService $activityPublishedService;

    /**
     * @var BulkPublishingStatusService
     */
    protected BulkPublishingStatusService $publishingStatusService;

    /**
     * BulkPublishingController Constructor.
     *
     * @param BulkPublishingService                                $bulkPublishingService
     * @param ActivityService                                      $activityService
     * @param ActivityWorkflowService                              $activityWorkflowService
     * @param BulkPublishingStatusService                          $publishingStatusService
     * @param ActivityPublishedService $activityPublishedService
     */
    public function __construct(
        BulkPublishingService $bulkPublishingService,
        ActivityService $activityService,
        ActivityWorkflowService $activityWorkflowService,
        BulkPublishingStatusService $publishingStatusService,
        ActivityPublishedService $activityPublishedService,
    ) {
        $this->bulkPublishingService = $bulkPublishingService;
        $this->activityService = $activityService;
        $this->activityWorkflowService = $activityWorkflowService;
        $this->publishingStatusService = $publishingStatusService;
        $this->activityPublishedService = $activityPublishedService;
    }

    /**
     * Checks if core elements are completed or not.
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws Exception
     */
    public function checkCoreElementsCompleted(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();
            $message = $this->activityWorkflowService->getPublishErrorMessage(auth()->user()->organization);

            if (!empty($message)) {
                Session::put('error', $message);

                return response()->json(['success' => false, 'message' => $message]);
            }

            if ($this->publishingStatusService->ongoingBulkPublishing(auth()->user()->organization->id)) {
                $pubishingStatus = $this->bulkPublishingService->getOrganisationBulkPublishingStatus();

                return response()->json([
                    'success'     => false,
                    'message'     => 'Another bulk publishing is already in progress.',
                    'data'        => $pubishingStatus['publishingData'],
                    'in_progress' => $pubishingStatus['inProgress'],
                ]);
            }

            $activityIds = json_decode($request->get('activities'), true, 512, JSON_THROW_ON_ERROR);

            $batchFilesize = $this->getPublishBatchSize($activityIds);

            if ($batchFilesize >= Enums::MAX_BATCH_SIZE) {
                throw new MaxBatchSizeExceededException();
            }

            $mergedFilesize = $this->getPossibleMergeableFilesize($batchFilesize);

            if ($mergedFilesize >= Enums::MAX_MERGE_SIZE) {
                throw new MaxMergeSizeExceededException();
            }

            if (!empty($activityIds)) {
                $coreElementsCompletion = $this->bulkPublishingService->getCoreElementsCompletedArray($activityIds);
                $deprecationStatusMap = $this->bulkPublishingService->getActivitiesWithDeprecatedValueArray(
                    $activityIds,
                    $this->activityService
                );

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Retrieved data successfully.',
                    'data'    => [
                        'core_elements_completion' => $coreElementsCompletion,
                        'deprecation_status_map'   => $deprecationStatusMap,
                        'counts'                   => [
                            'deprecated_list' => count($deprecationStatusMap ?? []),
                            'complete_list'   => count(Arr::get($coreElementsCompletion, 'complete', [])),
                            'incomplete_list' => count(Arr::get($coreElementsCompletion, 'incomplete', [])),
                        ],
                    ],
                ]);
            }

            return response()->json(['success' => false, 'message' => 'No activities selected.']);
        } catch (MaxBatchSizeExceededException $e) {
            DB::rollBack();
            logger()->error($e);

            return response()->json(
                [
                    'success'     => false,
                    'message'     => $e->getMessage(),
                    'in_progress' => false,
                    'error_type'  => 'batch_size_exception',
                ]
            );
        } catch (MaxMergeSizeExceededException $e) {
            DB::rollBack();
            logger()->error($e);

            return response()->json(
                [
                    'success'     => false,
                    'message'     => $e->getMessage(),
                    'in_progress' => false,
                    'error_type'  => 'max_size_exception',
                ]
            );
        } catch (Exception $e) {
            DB::rollBack();
            logger()->error($e->getMessage());

            return response()->json(
                ['success' => false, 'message' => 'Error has occurred while checking core elements completed.']
            );
        }
    }

    /**
     * Validates activities on IATI validator.
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws Exception
     */
    public function validateActivities(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();
            $hasUploadedCache = Cache::get('activity-validation-' . auth()->user()->id);
            $message = $this->activityWorkflowService->getPublishErrorMessage(auth()->user()->organization);

            if (!empty($message)) {
                Session::put('error', $message);

                return response()->json(['success' => false, 'message' => $message]);
            }

            $activityIds = json_decode($request->get('activities'), true, 512, JSON_THROW_ON_ERROR);

            if (!empty($activityIds)) {
                if ($hasUploadedCache || count($this->bulkPublishingService->checkOngoingValidationStatus())) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Another Activity Validation already in progress.',
                    ]);
                }

                if (!$this->activityService->checkNonPublishedActivities($activityIds)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'All activities are already published.',
                    ]);
                }

                Cache::forget('activity-validation-delete');
                Cache::put('activity-validation-' . auth()->user()->id, true, now()->addMinutes(2));
                $activityTitle = $this->bulkPublishingService->validateActivitiesOnIATI($activityIds);
                DB::commit();

                return response()->json(
                    [
                        'success'    => true,
                        'message'    => 'Validating Activities.',
                        'activities' => $activityTitle,
                        'total'      => count($activityTitle),
                    ]
                );
            }

            return response()->json(['success' => false, 'message' => 'No activities selected.']);
        } catch (Exception $e) {
            DB::rollBack();
            logger()->error($e->getMessage());
            logger()->error($e);

            return response()->json(
                ['success' => false, 'message' => 'Error has occurred while validating activities.']
            );
        }
    }

    /**
     * Starts bulk publishing job and sends initial response.
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws Exception
     */
    public function startBulkPublish(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();
            $organization = auth()->user()->organization;
            Cache::forget('activity-validation-' . auth()->user()->id);
            $message = $this->activityWorkflowService->getPublishErrorMessage($organization);

            if (!empty($message)) {
                Session::put('error', $message);

                return response()->json(['success' => false, 'message' => $message]);
            }

            if ($this->publishingStatusService->ongoingBulkPublishing($organization->id)) {
                $publishingStatus = $this->bulkPublishingService->getOrganisationBulkPublishingStatus();

                return response()->json([
                    'success'     => false,
                    'message'     => 'Another bulk publishing is already in progress.',
                    'data'        => $publishingStatus['publishingData'],
                    'in_progress' => $publishingStatus['inProgress'],
                ]);
            }

            $activityIds = json_decode($request->get('activities'), false, 512, JSON_THROW_ON_ERROR);

            if (!empty($activityIds)) {
                $filteredActivityIds = $this->bulkPublishingService->getPublishableActivityIds($activityIds);

                if (empty($filteredActivityIds)) {
                    return response()->json(
                        [
                            'success' => false,
                            'status'  => 'error',
                            'message' => 'All of the selected activities have critical error.',
                            'data'    => ['activity_ids' => $activityIds],
                        ]
                    );
                }

                $activities = $this->activityService->getActivitiesHavingIds($filteredActivityIds);

                if (!count($activities)) {
                    return response()->json(['success' => false, 'message' => 'No activities selected.']);
                }

                $response = $this->bulkPublishingService->generateInitialBulkPublishingResponse($activities);
                $this->publishingStatusService->storeProcessingActivities(
                    $activities,
                    $response['organization_id'],
                    $response['job_batch_uuid']
                );

                dispatch(
                    new BulkPublishActivities(
                        $activities,
                        $organization,
                        $organization->settings,
                        $response['organization_id'],
                        $response['job_batch_uuid']
                    )
                );

                $this->bulkPublishingService->deleteValidationResponses();

                DB::commit();

                return response()->json(
                    ['success' => true, 'message' => 'Bulk publishing started', 'data' => $response]
                );
            }

            return response()->json(['success' => false, 'message' => 'No activities selected.']);
        } catch (Exception $e) {
            DB::rollBack();
            logger()->error($e);

            return response()->json(['success' => false, 'message' => 'Bulk publishing failed.']);
        }
    }

    /**
     * Get status of bulk publishing.
     *
     * @return JsonResponse
     *
     * @throws Exception
     */
    public function getBulkPublishStatus(): JsonResponse
    {
        try {
            $organizationId = Auth::user()->organization_id;
            $uuid = $this->publishingStatusService->getPublishingUuid($organizationId);

            if ($uuid) {
                $publishStatus = $this->publishingStatusService->getActivityPublishingStatus($organizationId, $uuid);

                if ($publishStatus && count($publishStatus)) {
                    $response = $this->bulkPublishingService->getPublishingResponse($publishStatus);

                    if ($this->bulkPublishCompleted($response)) {
                        BulkPublishCacheHelper::clearBulkPublishCache($organizationId);
                    }

                    return response()->json(
                        [
                            'success'    => true,
                            'message'    => $response['message'],
                            'data'       => $response,
                            'publishing' => true,
                        ]
                    );
                }
            }

            return response()->json(
                ['success' => false, 'message' => 'No bulk publishing in progress', 'publishing' => false]
            );
        } catch (Exception $e) {
            logger()->error($e);

            return response()->json(['success' => false, 'message' => 'Status generation failed.']);
        } catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
            logger()->error($e);

            return response()->json(['success' => false, 'message' => 'Request error']);
        }
    }

    /**
     * Cancels Bulk Publishing.
     *
     * @return JsonResponse
     */
    public function cancelBulkPublishing(): JsonResponse
    {
        try {
            DB::beginTransaction();
            $orgId = auth()->user()->organization_id;
            $deletedIds = $this->bulkPublishingService->stopBulkPublishing($orgId);
            $numberOfDeletedRows = count($deletedIds);

            BulkPublishCacheHelper::clearBulkPublishCache($orgId);

            if ($deletedIds) {
                DB::commit();

                return response()->json(
                    [
                        'success' => true,
                        'message' => "Bulk publish of {$numberOfDeletedRows} activities canceled.",
                        'data'    => $deletedIds,
                    ]
                );
            }

            return response()->json(['success' => true, 'message' => 'No bulk publish were cancelled.']);
        } catch (Exception $e) {
            DB::rollBack();
            BulkPublishCacheHelper::clearBulkPublishCache(auth()->user()->organization_id);
            logger()->error($e->getMessage());
            logger()->error($e);

            return response()->json(['success' => false, 'message' => 'Failed to stop bulk publishing']);
        }
    }

    /**
     * Performs required checks for publishing activity.
     *
     * @return JsonResponse
     */
    public function checksForActivityBulkPublish(): JsonResponse
    {
        try {
            $message = $this->activityWorkflowService->getPublishErrorMessage(auth()->user()->organization);

            if (!empty($message)) {
                return response()->json(['success' => false, 'message' => $message]);
            }

            if ($this->publishingStatusService->ongoingBulkPublishing(auth()->user()->organization->id)) {
                $pubishingStatus = $this->bulkPublishingService->getOrganisationBulkPublishingStatus();

                return response()->json([
                    'success'     => false,
                    'message'     => 'Another bulk publishing is already in progress.',
                    'data'        => $pubishingStatus['publishingData'],
                    'in_progress' => $pubishingStatus['inProgress'],
                ]);
            }

            return response()->json(
                ['success' => true, 'message' => 'Activity is ready to be published.', 'status' => 'completed']
            );
        } catch (Exception $e) {
            logger()->error($e->getMessage());
            logger()->error($e);

            return response()->json(['success' => false, 'message' => 'Error has occurred while checking activity.']);
        }
    }

    /**
     * Performs required checks for validating activity.
     *
     * @return JsonResponse
     */
    public function checksForActivityBulkValidation(): JsonResponse
    {
        try {
            $hasUploadedCache = Cache::get('activity-validation-' . auth()->user()->id);
            $ongoingValidationActivities = $this->bulkPublishingService->checkOngoingValidationStatus();

            if ($hasUploadedCache || count($ongoingValidationActivities)) {
                return response()->json([
                    'success'    => false,
                    'message'    => 'Another bulk validation is already in progress.',
                    'activities' => count($ongoingValidationActivities) ? json_encode(
                        $ongoingValidationActivities
                    ) : null,
                ]);
            }

            return response()->json(
                ['success' => true, 'message' => 'Activities are ready to validate.', 'status' => 'completed']
            );
        } catch (Exception $e) {
            logger()->error($e->getMessage());

            return response()->json(['success' => false, 'message' => 'Error has occurred while checking activity.']);
        }
    }

    /**
     * Checks if activity with publishing in bulk publish status is published or draft in activities table
     * Updates it and returns msg.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function clearBulkPublishStatus(): JsonResponse
    {
        try {
            DB::beginTransaction();
            $this->publishingStatusService->deleteBulkPublishingStatus(Auth::user()->organization->id);
            DB::commit();

            return response()->json(['success' => true, 'message' => 'Bulk publishing status successfully deleted.']);
        } catch (Exception $e) {
            logger()->error($e->getMessage());
            logger()->error($e);
            DB::rollBack();

            return response()->json(['success' => false, 'message' => 'Failed to delete bulk publishing status']);
        }
    }

    /**
     * Returns activity validation status.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getValidationStatus(Request $request): JsonResponse
    {
        try {
            $activityIds = json_decode($request->get('activities'), true, 512, JSON_THROW_ON_ERROR);

            if (!empty($activityIds)) {
                $activities = $this->activityService->getActivitiesHavingIds($activityIds);
                $filteredActivityIds = $this->filterOutPublishedStateActivityIds($activityIds, $activities->toArray());
                $response = $this->bulkPublishingService->getActivityValidationStatus($filteredActivityIds);

                if ($this->validationCompleted($response)) {
                    BulkPublishCacheHelper::setInitialBulkPublishCache(auth()->user()->organization_id);
                }

                $hasFailedStatus = $response['failed_count'] > 0;

                return response()->json(['success' => !$hasFailedStatus, 'data' => $response]);
            }

            return response()->json(['success' => false, 'message' => 'Activity not selected.']);
        } catch (Exception|\Throwable $e) {
            logger()->error($e);

            return response()->json(
                ['success' => false, 'message' => 'Error has occurred while fetching validation status.']
            );
        }
    }

    /**
     * Get Validation responses of activities.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getValidationResponse(Request $request): JsonResponse
    {
        try {
            $activityIds = json_decode($request->get('activities'), true, 512, JSON_THROW_ON_ERROR);

            if (!empty($activityIds)) {
                $response = $this->bulkPublishingService->getValidationResponses($activityIds);

                return response()->json(['success' => true, 'data' => $response]);
            }

            return response()->json(['success' => false, 'message' => 'Activity not selected.']);
        } catch (Exception|\Throwable $exception) {
            logger()->error($exception);

            return response()->json(
                ['success' => false, 'message' => 'Error has occurred while fetching validation response.']
            );
        }
    }

    /**
     * Deletes validation status.
     *
     * @return JsonResponse
     */
    public function deleteValidationStatus(): JsonResponse
    {
        try {
            $this->bulkPublishingService->deleteValidationResponses();
            Cache::put('activity-validation-delete', true);
            Cache::forget('activity-validation-' . auth()->user()->id);
            BulkPublishCacheHelper::clearBulkPublishCache(auth()->user()->organization_id);

            return response()->json(['success' => true, 'message' => 'Successfully Deleted.']);
        } catch (Exception|\Throwable $exception) {
            logger()->error($exception);

            return response()->json(
                ['success' => false, 'message' => 'Error has occurred while deleting validation status.']
            );
        }
    }

    /**
     * @return JsonResponse
     */
    public function detectChange(): JsonResponse
    {
        try {
            $orgId = auth()->user()->organization_id;
            $hasChanged = BulkPublishCacheHelper::activitiesHaveChanged($orgId);
            $affectedActivities = BulkPublishCacheHelper::getActivityIdsInCache($orgId);

            return response()->json([
                'success' => true,
                'message' => $hasChanged ? 'Change detected.' : 'No change detected.',
                'data'    => [
                    'has_changed'         => $hasChanged,
                    'affected_activities' => $affectedActivities,
                ],
            ]);
        } catch (Exception $e) {
            logger()->error($e);

            return response()->json([
                'success' => false,
                'message' => 'Error has occurred while detecting change.',
            ]);
        }
    }

    /**
     * Filter out published state activity id's.
     *
     * @param array $activityIds
     * @param array $activities
     *
     * @return array
     */
    public function filterOutPublishedStateActivityIds(array $activityIds, array $activities): array
    {
        $activityLookup = [];

        foreach ($activities as $activity) {
            $activityLookup[$activity['id']] = $activity;
        }

        return array_filter(
            $activityIds,
            fn ($activityId) => isset($activityLookup[$activityId]) && $activityLookup[$activityId]['status']
        );
    }

    /**
     * @param int|float $publishBatchSize
     *
     * @return int|float
     */
    private function getPossibleMergeableFilesize(int|float $publishBatchSize): float|int
    {
        $organisation = auth()->user()->organization;
        $publishedFileSize = $this->activityPublishedService->getPublisherFileSize($organisation->id);
        $mergedFileSize = $publishedFileSize;

        return $mergedFileSize + $publishBatchSize;
    }

    /**
     * @param $activityIds
     *
     * @return int|float
     */
    private function getPublishBatchSize($activityIds): float|int
    {
        $organisation = auth()->user()->organization;
        $settings = $organisation->settings;
        $activities = $this->activityService->getActivitiesHavingIds($activityIds);
        $batchSize = 0;

        /** @var $xmlGenerator XmlGenerator::class */
        $xmlGenerator = app(XmlGenerator::class);

        foreach ($activities as $activity) {
            if ($activity && $activity->status === 'draft') {
                $transactions = $activity->transactions ?? [];
                $results = $activity->results ?? [];
                $tmpXml = $xmlGenerator->getXml($activity, $transactions, $results, $settings, $organisation);
                $batchSize = $batchSize + calculateStringSizeInMb($tmpXml->saveXML());
            }
        }

        return $batchSize;
    }

    private function validationCompleted(array $response): bool
    {
        return Arr::get($response, 'status') === 'completed';
    }

    private function bulkPublishCompleted(array $response): bool
    {
        return Arr::get($response, 'status') === 'completed';
    }
}
