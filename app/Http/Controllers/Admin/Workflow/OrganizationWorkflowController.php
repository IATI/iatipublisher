<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Workflow;

use App\Exceptions\PublisherNotFound;
use App\Http\Controllers\Controller;
use App\IATI\Models\Organization\OrganizationOnboarding;
use App\IATI\Services\Organization\OrganizationOnboardingService;
use App\IATI\Services\Workflow\ActivityWorkflowService;
use App\IATI\Services\Workflow\OrganizationWorkflowService;
use Exception;
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
     * OrganizationWorkflowController Constructor.
     */
    public function __construct(
        protected OrganizationWorkflowService $organizationWorkflowService,
        protected ActivityWorkflowService $activityWorkflowService,
        protected OrganizationOnboardingService $organizationOnboardingService
    ) {
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
            $organization = Auth::user()->organization;

            if (!$organization->registry_approved) {
                $message = $this->activityWorkflowService->getPublishErrorMessage($organization, 'organization');

                return response()->json(['success' => false, 'message' => $message]);
            }

            DB::beginTransaction();

            $this->organizationWorkflowService->publishOrganization($organization, session('oidc_access_token'));

            $this->organizationOnboardingService->updateOrganizationOnboardingStepToComplete(
                $organization->id,
                OrganizationOnboarding::ORGANIZATION_DATA,
            );

            DB::commit();

            $translatedMessage = trans(
                'workflow_backend/organization_workflow_controller.organization_has_been_published_successfully'
            );

            return response()->json(['success' => true, 'message' => $translatedMessage]);
        } catch (PublisherNotFound $message) {
            DB::rollBack();
            logger()->error($message->getMessage());

            return response()->json(['success' => false, 'message' => $message->getMessage()]);
        } catch (Exception $e) {
            DB::rollBack();
            logger()->error($e);
            $translatedMessage = trans(
                'workflow_backend/organization_workflow_controller.error_has_occurred_while_publishing_organization'
            );

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
            $organization = Auth::user()->organization;

            if (!$organization->is_published && $organization->status === 'draft') {
                $translatedMessage = trans(
                    'workflow_backend/organization_workflow_controller.this_organization_has_not_been_published_to_un_publish'
                );

                return redirect()->route('admin.activities.index')->with('error', $translatedMessage);
            }

            DB::beginTransaction();

            $this->organizationWorkflowService->unpublishOrganization($organization, session('oidc_access_token'));
            $this->organizationOnboardingService->updateOrganizationOnboardingStepToComplete($organization->id, OrganizationOnboarding::ORGANIZATION_DATA, false);

            DB::commit();

            $translatedMessage = trans(
                'workflow_backend/organization_workflow_controller.organization_has_been_un_published_successfully'
            );

            return response()->json(['success' => true, 'message' => $translatedMessage]);
        } catch (Exception $e) {
            DB::rollBack();
            logger()->error($e);
            $translatedMessage = trans(
                'workflow_backend/organization_workflow_controller.error_has_occurred_while_un_publishing_organization'
            );

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
        $organization = auth()->user()->organization;
        $message = $this->activityWorkflowService->getPublishErrorMessage($organization, 'organization');
        $translatedMessage = trans(
            'workflow_backend/organization_workflow_controller.organization_is_ready_to_be_published'
        );

        return !empty($message) ? response()->json(['success' => false, 'message' => $message]) : response()->json(
            ['success' => true, 'message' => $translatedMessage]
        );
    }
}
