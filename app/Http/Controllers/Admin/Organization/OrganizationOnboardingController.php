<?php

namespace App\Http\Controllers\Admin\Organization;

use App\Http\Controllers\Controller;
use App\IATI\Models\Organization\OrganizationOnboarding;
use App\IATI\Services\Organization\OrganizationOnboardingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Class OrganizationOnboardingController.
 */
class OrganizationOnboardingController extends Controller
{
    /**
     * @param  OrganizationOnboardingService  $organizationOnboardingService
     */
    public function __construct(
        protected OrganizationOnboardingService $organizationOnboardingService
    ) {
    }

    /**
     * Toggles don't show if required.
     *
     * @param  Request  $request
     *
     * @return JsonResponse
     */
    public function toggleDontShow(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $request->validate([
                'value' => 'required|boolean',
            ]);

            $this->organizationOnboardingService->updateDontShowAgain(Auth::user()->organization_id, $request->value);
            DB::commit();
            $translatedMessage = trans('common/common.updated_successfully');

            return response()->json(['success' => true, 'message' => $translatedMessage]);
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error($e->getMessage());
            $translatedMessage = trans('organisationDetail/organisation_onboarding_controller.error_occurred_while_updating_dont_show_again');

            return response()->json(['success' => false, 'message' => $translatedMessage]);
        }
    }

    /**
     * Updates activity status to complete.
     *
     * @return JsonResponse
     */
    public function completeActivityForOnboarding(): JsonResponse
    {
        try {
            DB::beginTransaction();
            $this->organizationOnboardingService->updateOrganizationOnboardingStepToComplete(Auth::user()->organization_id, OrganizationOnboarding::ACTIVITY, true);
            DB::commit();
            $translatedMessage = trans('common/common.updated_successfully');

            return response()->json(['success' => true, 'message' => $translatedMessage]);
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error($e->getMessage());
            $translatedMessage = trans('organisationDetail/organisation_onboarding_controller.error_occurred_while_storing_updating_activity_status');

            return response()->json(['success' => false, 'message' => $translatedMessage]);
        }
    }
}
