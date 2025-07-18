<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Setting;

use App\Constants\Enums;
use App\Http\Controllers\Controller;
use App\Http\Requests\Setting\DefaultFormRequest;
use App\IATI\Models\Organization\OrganizationOnboarding;
use App\IATI\Services\Organization\OrganizationOnboardingService;
use App\IATI\Services\Organization\OrganizationService;
use App\IATI\Services\Setting\SettingService;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\DatabaseManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Throwable;

/**
 * Class SettingController.
 */
class SettingController extends Controller
{
    protected OrganizationService $organizationService;
    protected SettingService $settingService;

    protected DatabaseManager $db;

    /**
     * Create a new controller instance.
     *
     * @param  OrganizationService  $organizationService
     * @param  SettingService  $settingService
     * @param  DatabaseManager  $db
     * @param  OrganizationOnboardingService  $organizationOnboardingService
     */
    public function __construct(OrganizationService $organizationService, SettingService $settingService, DatabaseManager $db, protected OrganizationOnboardingService $organizationOnboardingService)
    {
        $this->organizationService = $organizationService;
        $this->settingService = $settingService;
        $this->db = $db;
    }

    /**
     * Show the organization setting page.
     *
     * @return Factory|View|Application|RedirectResponse
     */
    public function index(): Factory|View|Application|RedirectResponse
    {
        try {
            $currencies = getCodeList('Currency', 'Organization', filterDeprecated: true);
            $languages = getCodeList('Language', 'Organization', filterDeprecated: true);
            $humanitarian = trans('setting.humanitarian_types');
            $budgetNotProvided = getCodeList('BudgetNotProvided', 'Activity', filterDeprecated: true);
            $defaultCollaborationType = getCodeList('CollaborationType', 'Activity', filterDeprecated: true);
            $defaultFlowType = getCodeList('FlowType', 'Activity', filterDeprecated: true);
            $defaultFinanceType = getCodeList('FinanceType', 'Activity', filterDeprecated: true);
            $defaultAidType = getCodeList('AidType', 'Activity', filterDeprecated: true);
            $defaultTiedStatus = getCodeList('TiedStatus', 'Activity', filterDeprecated: true);
            $userRole = Auth::user()->role->role;

            return view('admin.settings.index', compact('currencies', 'languages', 'humanitarian', 'budgetNotProvided', 'userRole', 'defaultCollaborationType', 'defaultFlowType', 'defaultFinanceType', 'defaultAidType', 'defaultTiedStatus'));
        } catch (Exception $e) {
            logger()->error($e);
            $translatedMessage = trans('common/common.error_while_rendering_setting_page');

            return redirect()->route('admin.activities.index')->with('error', $translatedMessage);
        }
    }

    /**
     * @return JsonResponse
     *
     * @throws GuzzleException
     */
    public function getSetting(): JsonResponse
    {
        $setting = null;

        try {
            $setting = $this->settingService->getSetting();

            $tokenStatus = Enums::TOKEN_CORRECT;

            $publishing_info = $setting->publishing_info;
            $publishing_info['token_status'] = $tokenStatus;

            $setting->publishing_info = $publishing_info;
            $setting->save();
            $translatedMessage = 'Settings fetched successfully.';

            return response()->json(['success' => true, 'message' => $translatedMessage, 'data' => $setting]);
        } catch (Exception $e) {
            logger()->error($e);

            if ($e instanceof GuzzleException && $e->getCode() === 404) {
                $translatedMessage = trans('settings/setting_controller.publisher_does_not_exist_in_registry');

                return response()->json([
                    'success' => false,
                    'message' => $translatedMessage,
                    'errors' => [],
                    'data' => $setting,
                ]);
            }

            $translatedMessage = 'Error occurred while fetching the data.';

            return response()->json(['success' => false, 'message' => $translatedMessage]);
        }
    }

    /**
     * Store default data of organization.
     *
     * @param DefaultFormRequest $request
     *
     * @return JsonResponse
     * @throws Throwable
     */
    public function storeDefaultForm(DefaultFormRequest $request): JsonResponse
    {
        try {
            $this->db->beginTransaction();

            $setting = $this->settingService->storeDefaultValues($request->all());

            $defaultValuesCompleted = $this->organizationOnboardingService->checkDefaultValuesComplete($setting->default_values);
            $this->organizationOnboardingService->updateOrganizationOnboardingStepToComplete(Auth::user()->organization_id, OrganizationOnboarding::DEFAULT_VALUES, $defaultValuesCompleted);

            $this->db->commit();
            $translatedMessage = trans('settings/setting_controller.default_setting_stored_successfully');

            return response()->json(['success' => true, 'message' => $translatedMessage, 'data' => $setting]);
        } catch (Exception $e) {
            $this->db->rollBack();
            logger()->error($e);
            $translatedMessage = trans('settings/setting_controller.error_occurred_while_storing_setting');

            return response()->json(['success' => false, 'message' => $translatedMessage]);
        }
    }

    /**
     * Get setting status.
     *
     * @return JsonResponse
     */
    public function getSettingStatus(): JsonResponse
    {
        try {
            $status = $this->settingService->getStatus();
            $translatedMessage = trans('settings/setting_controller.setting_status_successfully_retrieved');

            return response()->json([
                'success' => true,
                'message' => $translatedMessage,
                'data' => $status,
            ]);
        } catch (Exception $e) {
            logger()->error($e);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
