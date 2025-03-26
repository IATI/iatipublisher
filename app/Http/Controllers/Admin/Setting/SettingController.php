<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Setting;

use App\Constants\Enums;
use App\Exceptions\PublisherIdChangeByIatiAdminException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Setting\DefaultFormRequest;
use App\Http\Requests\Setting\PublisherFormRequest;
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
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use JsonException;
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
            logger()->error($e->getMessage());
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

            $publisherData = [
                'publisher_id' => $setting->publishing_info['publisher_id'],
                'api_token'    => Arr::get($setting->publishing_info, 'api_token', false),
            ];

            $verifyPublisherInfo = $this->verifyPublisher($publisherData);
            $verifyApiInfo = $this->verifyApi($publisherData);
            [$tokenStatus] = $this->getTokenStatusAndMessage($verifyPublisherInfo, $verifyApiInfo);

            $publishing_info = $setting->publishing_info;
            $publishing_info['token_status'] = $tokenStatus;

            $setting->publishing_info = $publishing_info;
            $setting->save();
            $translatedMessage = 'Settings fetched successfully.';

            return response()->json(['success' => true, 'message' => $translatedMessage, 'data' => $setting]);
        } catch (Exception $e) {
            logger()->error($e->getMessage());

            if ($e instanceof GuzzleException && $e->getCode() === 404) {
                $translatedMessage = trans('settings/setting_controller.publisher_does_not_exist_in_registry');

                return response()->json([
                    'success' => false,
                    'message' => $translatedMessage,
                    'errors' => [
                        'publisher_id' => $translatedMessage,
                    ],
                    'data' => $setting,
                ]);
            }

            $translatedMessage = 'Error occurred while fetching the data.';

            return response()->json(['success' => false, 'message' => $translatedMessage]);
        }
    }

    /**
     * Store publishing info a valid registration.
     *
     * @param PublisherFormRequest $request
     *
     * @return JsonResponse
     * @throws GuzzleException
     */
    public function verify(PublisherFormRequest $request): JsonResponse
    {
        try {
            $publisherData = $request->all();
            $verifyPublisherInfo = $this->verifyPublisher($publisherData);
            $verifyApiInfo = $this->verifyApi($publisherData);

            $publisherData['publisher_verification'] = $verifyPublisherInfo['validation'];
            $publisherData['token_verification'] = $verifyApiInfo['validation'];

            [$tokenStatus, $message] = $this->getTokenStatusAndMessage($verifyPublisherInfo, $verifyApiInfo);
            $publisherData['token_status'] = $tokenStatus;

            return response()->json(['success' => true, 'message' => $message, 'data' => $publisherData]);
        } catch (Exception $e) {
            logger()->error($e);

            $publisherData['publisher_verification'] = false;
            $publisherData['token_verification'] = false;

            if ($e instanceof GuzzleException && $e->getCode() === 404) {
                $translatedMessage = trans('settings/setting_controller.publisher_does_not_exist_in_registry');

                return response()->json([
                    'success' => false,
                    'message' => $translatedMessage,
                    'data' => $publisherData,
                     'errors' => [
                        'publisher_id' => $translatedMessage,
                     ],
                ]);
            }
            $translatedMessage = trans('settings/setting_controller.error_occurred_while_verifying_publisher');

            return response()->json([
                'success' => false,
                'message' => $translatedMessage,
                'data' => $publisherData,
            ]);
        }
    }

    /**
     * Store publishing info a valid registration.
     *
     * @param PublisherFormRequest $request
     *
     * @return JsonResponse
     * @throws Throwable
     */
    public function storePublishingInfo(PublisherFormRequest $request): JsonResponse
    {
        $this->db->beginTransaction();

        try {
            $settingData = $request->all();

            $verifyPublisherInfo = $this->verifyPublisher($settingData);
            $verifyApiInfo = $this->verifyApi($settingData);

            if (isset($verifyApiInfo['success'])) {
                [$tokenStatus] = $this->getTokenStatusAndMessage($verifyPublisherInfo, $verifyApiInfo);
                $settingData['publisher_verification'] = $verifyPublisherInfo['validation'];
                $settingData['token_status'] = $tokenStatus;
                $settingData['token_verification'] = $tokenStatus === Enums::TOKEN_CORRECT;

                if ($tokenStatus === Enums::TOKEN_INCORRECT) {
                    $translatedMessage = trans('settings/setting_controller.your_api_token_is_invalid');

                    return response()->json([
                        'success' => false,
                        'message' => $translatedMessage,
                        'data'    => $settingData,
                        'error'   => ['token' => $verifyApiInfo, 'publisher_verification' => $verifyPublisherInfo],
                    ]);
                }

                if (isSuperAdmin()) {
                    $this->organizationService->updateBySuperadmin($settingData);
                } else {
                    $this->settingService->storePublishingInfo($settingData);
                }

                if ($this->organizationOnboardingService->checkPublishingSettingsComplete($settingData)) {
                    $this->organizationOnboardingService->updateOrganizationOnboardingStepToComplete(Auth::user()->organization_id, OrganizationOnboarding::PUBLISHING_SETTINGS, true);
                }

                $this->db->commit();

                $translatedMessage = trans('settings/setting_controller.publisher_setting_stored_successfully');

                return response()->json([
                    'success' => true,
                    'message' => $translatedMessage,
                    'data'    => $settingData,
                ]);
            }

            $translatedMessage = trans('settings/setting_controller.error_occurred_while_verifying_data');

            return response()->json([
                'success' => false,
                'message' => $translatedMessage,
                'data'    => $settingData,
                'error'   => ['token' => $verifyApiInfo, 'publisher_verification' => $verifyPublisherInfo],
            ]);
        } catch (Exception $e) {
            $this->db->rollBack();

            logger()->error($e);

            $settingData['publisher_verification'] = false;
            $settingData['token_verification'] = false;

            if ($e instanceof PublisherIdChangeByIatiAdminException) {
                $translatedMessage = trans('settings/setting_controller.publisher_id_or_api_token_incorrect');

                return response()->json(['success' => false, 'message' => $translatedMessage, 'data' => $settingData]);
            }

            if ($e instanceof GuzzleException && $e->getCode() === 404) {
                $translatedMessage = trans('settings/setting_controller.publisher_does_not_exist_in_registry');

                return response()->json(['success' => false, 'message' => $translatedMessage, 'data' => $settingData]);
            }
            $translatedMessage = trans('settings/setting_controller.error_occurred_while_verifying_publisher');

            return response()->json(['success' => false, 'message' => $translatedMessage, 'data' => $settingData]);
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
            logger()->error($e->getMessage());
            $translatedMessage = trans('settings/setting_controller.error_occurred_while_storing_setting');

            return response()->json(['success' => false, 'message' => $translatedMessage]);
        }
    }

    /**
     * Verify publisher.
     *
     * @param array $data
     *
     * @return array
     * @throws GuzzleException
     * @throws JsonException
     */
    public function verifyPublisher(array $data): array
    {
        $result = $this->settingService->verifyPublisher($data);
        $state = $result ? $result->state : 'approval_needed';

        return ['success' => true, 'validation' => (bool) $result, 'state' => $state];
    }

    /**
     * Verify publisher.
     *
     * @param array $data
     *
     * @return array
     * @throws GuzzleException
     * @throws JsonException
     */
    public function verifyApi(array $data): array
    {
        if ($data['api_token']) {
            $response = $this->settingService->verifyApi($data);

            return ['success' => true, 'validation' => in_array($data['publisher_id'], array_column($response, 'name'), true)];
        }

        return ['success' => true, 'validation' => false];
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
            logger()->error($e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Returns token status and message.
     *
     * @param array $verifyPublisherInfo
     * @param array $verifyApiInfo
     *
     * @return string[]
     */
    private function getTokenStatusAndMessage(array $verifyPublisherInfo, array $verifyApiInfo): array
    {
        $translatedMessage = trans('settings/setting_controller.api_token_incorrect_please_enter_valid_api_token');

        $message = $translatedMessage;
        $tokenStatus = Enums::TOKEN_INCORRECT;

        if ($verifyPublisherInfo['success'] && $verifyPublisherInfo['validation']) {
            if ($verifyApiInfo['success'] && $verifyApiInfo['validation']) {
                $translatedMessage = trans('settings/setting_controller.api_token_verified_successfully');

                $message = $translatedMessage;
                $tokenStatus = Enums::TOKEN_CORRECT;
            } elseif ($verifyPublisherInfo['state'] === 'approval_needed') {
                $translatedMessage = trans('common/common.your_account_is_pending_approval_by_the_iati_team');

                $message = $translatedMessage;
                $tokenStatus = Enums::TOKEN_PENDING;
            }
        }

        return [$tokenStatus, $message];
    }
}
