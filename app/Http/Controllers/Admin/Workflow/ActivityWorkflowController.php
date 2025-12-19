<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Workflow;

use App\Http\Controllers\Controller;
use App\IATI\Services\Validator\ActivityValidatorResponseService;
use App\IATI\Services\Workflow\ActivityWorkflowService;
use App\IATI\Traits\IatiValidatorResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

/**
 * Class ActivityWorkflowController.
 */
class ActivityWorkflowController extends Controller
{
    use IatiValidatorResponseTrait;

    public function __construct(
        protected ActivityWorkflowService $activityWorkflowService,
        protected ActivityValidatorResponseService $validatorService
    ) {
    }

    /**
     * Unpublish an activity from the IATI registry.
     *
     * @param $id
     *
     * @return JsonResponse
     */
    public function unpublish($id): JsonResponse
    {
        try {
            DB::beginTransaction();
            $activity = $this->activityWorkflowService->findActivity($id);

            if (!$activity->linked_to_iati) {
                $translatedMessage = trans('workflow_backend/activity_workflow_controller.this_activity_has_not_been_published_to_un_publish');

                Session::put('error', $translatedMessage);

                return response()->json(['success' => false, 'message' => $translatedMessage]);
            }

            $this->activityWorkflowService->unpublishActivity($activity, session('oidc_access_token'));
            DB::commit();
            $this->activityWorkflowService->deletePublishedFile($activity);
            $translatedMessage = trans('workflow_backend/activity_workflow_controller.activity_has_been_un_published_successfully');
            Session::put('success', $translatedMessage);

            return response()->json(['success' => true, 'message' => $translatedMessage]);
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error($e);
            $translatedMessage = trans('workflow_backend/activity_workflow_controller.error_has_occurred_while_un_publishing_activity');
            Session::put('error', $translatedMessage);

            return response()->json(['success' => false, 'message' => $translatedMessage]);
        }
    }
}
