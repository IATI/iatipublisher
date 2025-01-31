<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Workflow;

use App\Exceptions\PublisherNotFound;
use App\Http\Controllers\Controller;
use App\IATI\Models\Organization\OrganizationOnboarding;
use App\IATI\Services\Organization\OrganizationOnboardingService;
use App\IATI\Services\Workflow\ActivityWorkflowService;
use App\IATI\Services\Workflow\OrganizationWorkflowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Class OrganizationWorkflowController.
 */
class OrganizationWorkflowController extends Controller
{
    /**
     * @var OrganizationWorkflowService
     */
    protected OrganizationWorkflowService $organizationWorkflowService;

    /**
     * @var ActivityWorkflowService
     */
    protected ActivityWorkflowService $activityWorkflowService;

    /**
     * OrganizationWorkflowController Constructor.
     *
     * @param OrganizationWorkflowService $organizationWorkflowService
     * @param ActivityWorkflowService $activityWorkflowService
     */
    public function __construct(OrganizationWorkflowService $organizationWorkflowService, ActivityWorkflowService $activityWorkflowService, protected OrganizationOnboardingService $organizationOnboardingService)
    {
        $this->organizationWorkflowService = $organizationWorkflowService;
        $this->activityWorkflowService = $activityWorkflowService;
    }

    /**
     * Publish an organization.
     *
     *
     * @return JsonResponse
     */
    public function publish(): JsonResponse
    {
        try {
            DB::beginTransaction();
            $organization = Auth::user()->organization;

            if ($this->activityWorkflowService->hasNoPublisherInfo($organization->settings) || !$this->activityWorkflowService->isUserVerified()) {
                $message = $this->activityWorkflowService->getPublishErrorMessage($organization, 'organization');

                return response()->json(['success' => false, 'message' => $message]);
            }

            $this->organizationWorkflowService->publishOrganization($organization);
            $this->organizationOnboardingService->updateOrganizationOnboardingStepToComplete($organization->id, OrganizationOnboarding::ORGANIZATION_DATA, true);
            DB::commit();
            $translatedMessage = trans('workflow_backend/organization_workflow_controller.organization_has_been_published_successfully');

            return response()->json(['success' => true, 'message' => $translatedMessage]);
        } catch (PublisherNotFound $message) {
            DB::rollBack();
            logger()->error($message->getMessage());

            return response()->json(['success' => false, 'message' => $message->getMessage()]);
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error($e->getMessage());
            $translatedMessage = trans('workflow_backend/organization_workflow_controller.error_has_occurred_while_publishing_organization');

            return response()->json(['success' => false, 'message' => $translatedMessage]);
        }
    }

    /**
     * UnPublish an organization from the IATI registry.
     *
     * @return JsonResponse|RedirectResponse
     */
    public function unPublish(): JsonResponse|RedirectResponse
    {
        try {
            DB::beginTransaction();
            $organization = Auth::user()->organization;

            if (!$organization->is_published && $organization->status === 'draft') {
                $translatedMessage = trans('workflow_backend/organization_workflow_controller.this_organization_has_not_been_published_to_un_publish');

                return redirect()->route('admin.activities.index')->with('error', $translatedMessage);
            }

            $this->organizationWorkflowService->unpublishOrganization($organization);
            $this->organizationOnboardingService->updateOrganizationOnboardingStepToComplete($organization->id, OrganizationOnboarding::ORGANIZATION_DATA, false);
            DB::commit();
            $translatedMessage = trans('workflow_backend/organization_workflow_controller.organization_has_been_un_published_successfully');

            return response()->json(['success' => true, 'message' => $translatedMessage]);
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error($e->getMessage());
            $translatedMessage = trans('workflow_backend/organization_workflow_controller.error_has_occurred_while_un_publishing_organization');

            return response()->json(['success' => false, 'message' => $translatedMessage]);
        }
    }

    /**
     * Performs required checks for publishing organization.
     *
     * @return JsonResponse
     */
    public function checksForOrganizationPublish(): JsonResponse
    {
        $message = $this->activityWorkflowService->getPublishErrorMessage(auth()->user()->organization, 'organization');
        $translatedMessage = trans('workflow_backend/organization_workflow_controller.organization_is_ready_to_be_published');

        return !empty($message) ? response()->json(['success' => false, 'message' => $message]) : response()->json(['success' => true, 'message' => $translatedMessage]);
    }
}
