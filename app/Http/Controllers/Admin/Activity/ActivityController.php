<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Activity;

use App\Console\Commands\DuplicateActivities;
use App\Http\Controllers\Controller;
use App\Http\Requests\Activity\ActivityCreateRequest;
use App\IATI\Models\Activity\Activity;
use App\IATI\Services\Activity\ActivityService;
use App\IATI\Services\Activity\ResultService;
use App\IATI\Services\Activity\TransactionService;
use App\IATI\Services\ImportActivityError\ImportActivityErrorService;
use App\IATI\Services\Organization\OrganizationOnboardingService;
use App\IATI\Services\Organization\OrganizationService;
use App\IATI\Services\Setting\SettingService;
use App\IATI\Services\Validator\ActivityValidatorResponseService;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Database\DatabaseManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use JsonException;
use Throwable;

/**
 * Class ActivityController.
 */
class ActivityController extends Controller
{
    /**
     * @var ActivityService
     */
    protected ActivityService $activityService;

    /**
     * @var DatabaseManager
     */
    protected DatabaseManager $db;

    /**
     * @var ResultService
     */
    protected ResultService $resultService;

    /**
     * @var TransactionService
     */
    protected TransactionService $transactionService;

    /**
     * @var ActivityValidatorResponseService
     */
    protected ActivityValidatorResponseService $activityValidatorResponseService;

    /**
     * @var ImportActivityErrorService
     */
    protected ImportActivityErrorService $importActivityErrorService;

    /**
     * @var OrganizationService
     */
    private OrganizationService $organizationService;

    /**
     * @var SettingService
     */
    protected SettingService $settingService;

    /**
     * ActivityController Constructor.
     *
     * @param ActivityService $activityService
     * @param DatabaseManager $db
     * @param ResultService $resultService
     * @param TransactionService $transactionService
     * @param ActivityValidatorResponseService $activityValidatorResponseService
     * @param ImportActivityErrorService $importActivityErrorService
     * @param OrganizationService $organizationService
     * @param SettingService $settingService
     * @param OrganizationOnboardingService $organizationOnboardingService
     */
    public function __construct(
        ActivityService $activityService,
        DatabaseManager $db,
        ResultService $resultService,
        TransactionService $transactionService,
        ActivityValidatorResponseService $activityValidatorResponseService,
        ImportActivityErrorService $importActivityErrorService,
        OrganizationService $organizationService,
        SettingService $settingService,
        protected OrganizationOnboardingService $organizationOnboardingService,
    ) {
        $this->activityService = $activityService;
        $this->db = $db;
        $this->resultService = $resultService;
        $this->transactionService = $transactionService;
        $this->activityValidatorResponseService = $activityValidatorResponseService;
        $this->importActivityErrorService = $importActivityErrorService;
        $this->organizationService = $organizationService;
        $this->settingService = $settingService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return View|JsonResponse
     */
    public function index()
    {
        try {
            $languages = getCodeList('Language', 'Activity', filterDeprecated: true);
            $toast = generateToastData();
            $settingsDefaultValue = $this->settingService->getSetting()->default_values ?? [];
            $defaultLanguage = getDefaultValue($settingsDefaultValue, 'language', 'Activity/Language.json' ?? []);

            /** User onboarding part */
            $organization = Auth::user()->organization;
            $organizationOnboarding = $this->organizationOnboardingService->getOrganizationOnboarding($organization->id);
            $isFirstTime = false;

            if (!$organizationOnboarding) {
                DB::beginTransaction();
                $organizationOnboarding = $this->organizationOnboardingService->createOrganizationOnboarding($organization);
                $isFirstTime = true;
                DB::commit();
            }

            $organizationOnboarding = $this->organizationOnboardingService->translateOrganisationOnboardingTitles($organizationOnboarding);

            // Data for user onboarding
            $currencies = getCodeList('Currency', 'Organization', filterDeprecated: true);
            $humanitarian = trans('setting.humanitarian_types');
            $defaultFlowType = getCodeList('FlowType', 'Activity', filterDeprecated: true);
            $defaultFinanceType = getCodeList('FinanceType', 'Activity', filterDeprecated: true);
            $defaultAidType = getCodeList('AidType', 'Activity', filterDeprecated: true);
            $defaultTiedStatus = getCodeList('TiedStatus', 'Activity', filterDeprecated: true);
            $organizationType = getCodeList('OrganizationType', 'Organization', filterDeprecated: true);

            return view(
                'admin.activity.index',
                compact(
                    'languages',
                    'toast',
                    'defaultLanguage',
                    'organizationOnboarding',
                    'organization',
                    'currencies',
                    'humanitarian',
                    'defaultFlowType',
                    'defaultFinanceType',
                    'defaultAidType',
                    'defaultTiedStatus',
                    'organizationType',
                    'isFirstTime',
                )
            );
        } catch (Exception $e) {
            DB::rollBack();
            logger()->error($e);

            return response()->json(['success' => false, 'error' => 'Error has occurred while fetching activities']);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return void
     */
    public function create(): void
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param ActivityCreateRequest $request
     *
     * @return JsonResponse
     * @throws Throwable
     */
    public function store(ActivityCreateRequest $request): JsonResponse
    {
        try {
            $input = $request->all();

            $this->db->beginTransaction();
            $activity = $this->activityService->store($input);
            $this->db->commit();

            $translatedMessage = trans('activity_detail/activity_controller.activity_created_successfully');
            Session::put('success', $translatedMessage);

            return response()->json([
                'success' => true,
                'message' => $translatedMessage,
                'data'    => $activity,
            ]);
        } catch (Exception $e) {
            logger()->error($e);
            $translatedMessage = trans('activity_detail/activity_controller.error_has_occurred_while_saving_activity');

            return response()->json(['success' => false, 'message' => $translatedMessage, 'data' => []]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param $id
     *
     * @return View|JsonResponse|RedirectResponse
     */
    public function show($id): View|JsonResponse|RedirectResponse
    {
        try {
            $toast = generateToastData();
            $activity = $this->activityService->getActivity($id);
            $elements = $this->getElementJsonSchema($activity);
            $elementGroups = readElementGroup();
            $types = $this->getActivityDetailDataType();
            $results = $this->resultService->getActivityResultsWithIndicatorsAndPeriods($activity->id);
            $hasIndicatorPeriod = $this->resultService->checkResultIndicatorPeriod($results);
            $transactions = $this->transactionService->getActivityTransactions($activity->id);
            $status = $activity->element_status;
            $status['transactions'] = $transactions->count() === 0 ? false : Arr::get($status, 'transactions', false);
            $status['result'] = $results->count() === 0 ? false : Arr::get($status, 'result', false);
            $status['reporting_org'] = $activity->organization->element_status['reporting_org'] ?? false;
            $activity->element_status = $status;
            $progress = $this->activityService->activityPublishingProgress($activity);
            $coreCompleted = isCoreElementCompleted($activity->element_status);
            $validatorResponse = $this->activityValidatorResponseService->getValidatorResponse($id);
            $importActivityError = $this->importActivityErrorService->getImportActivityError($id);
            $organization_identifier = $activity->organization->identifier;
            $iatiValidatorResponse = $validatorResponse->response ?? null;
            $importActivityError = $importActivityError->error ?? null;
            $deprecationStatusMap = $activity->deprecation_status_map;

            return view(
                'admin.activity.show',
                compact(
                    'elements',
                    'elementGroups',
                    'progress',
                    'activity',
                    'toast',
                    'types',
                    'status',
                    'results',
                    'hasIndicatorPeriod',
                    'transactions',
                    'coreCompleted',
                    'iatiValidatorResponse',
                    'importActivityError',
                    'organization_identifier',
                    'deprecationStatusMap'
                )
            );
        } catch (Exception $e) {
            logger()->error($e);
            $translatedMessage = trans('common/common.error_opening_data_entry_form');

            return redirect()->route('admin.activities.index')->with('error', $translatedMessage);
        }
    }

    /**
     * manipulate element json schema and adds warning info text.
     *
     * @param $activity
     *
     * @return array
     *
     * @throws JsonException
     */
    public function getElementJsonSchema($activity): array
    {
        $element = readElementJsonSchema();
        $hasDefinedInTransaction = $this->transactionService->hasRecipientRegionOrCountryDefinedInTransaction($activity->id);
        $hasSectorDefinedInTransaction = $this->transactionService->hasSectorDefinedInTransaction($activity->id);
        $emptyRecipientRegionOrCountryTransactionCount = 0;
        $emptySectorTransactionCount = 0;

        if ($hasDefinedInTransaction) {
            $emptyRecipientRegionOrCountryTransactionCount = $activity->transactions->filter(function ($item) {
                $recipientRegion = $item->transaction['recipient_region'];
                $recipientCountry = $item->transaction['recipient_country'];

                return is_array_value_empty($recipientRegion) && is_array_value_empty($recipientCountry);
            })->count();
        }

        if ($hasSectorDefinedInTransaction) {
            $emptySectorTransactionCount = $activity->transactions->filter(function ($item) {
                $sector = $item->transaction['sector'];

                return is_array_value_empty($sector);
            })->count();
        }

        $element['transactions']['warning_info_text'] = match (true) {
            $emptyRecipientRegionOrCountryTransactionCount > 0 && $emptySectorTransactionCount > 0   => trans('activity_detail/activity_controller.recipient_region_recipient_country_and_sector_are_declared_at_transaction_level'),
            $emptyRecipientRegionOrCountryTransactionCount > 0 && $emptySectorTransactionCount === 0 => trans('activity_detail/activity_controller.recipient_region_and_recipient_country_is_declared_at_transaction_level'),
            $emptySectorTransactionCount > 0 && $emptyRecipientRegionOrCountryTransactionCount === 0 => trans('activity_detail/activity_controller.sector_is_declared_at_transaction_level'),
            default => ''
        };

        return $element;
    }

    /**
     * Show the form for editing the specified resource.
     *
     *
     * @param Activity $activity
     *
     * @return void
     */
    public function edit(Activity $activity): void
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request  $request
     * @param Activity $activity
     *
     * @return void
     */
    public function update(Request $request, Activity $activity): void
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $activityId
     *
     * @return JsonResponse
     */
    public function destroy($activityId): JsonResponse
    {
        try {
            $activity = $this->activityService->getActivity($activityId);

            if ($activity->linked_to_iati) {
                Session::put('error', 'Activity must be un-published before deleting.');

                return response()->json(['success' => false, 'message' => 'Activity must be un-published before deleting.']);
            }

            if ($this->activityService->deleteActivity($activity)) {
                $translatedData = trans('common/common.deleted_successfully');

                Session::put('success', $translatedData);

                return response()->json(['success' => true, 'message' => $translatedData]);
            }

            return response()->json(['success' => false, 'message' => 'Activity delete failed.']);
        } catch (Exception $e) {
            logger()->error($e);

            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Sanitizes the request for removing code injections.
     *
     * @param $request
     *
     * @return array
     */
    public function sanitizeRequest($request): array
    {
        $tableConfig = getTableConfig('activity');
        $queryParams = [];

        if (!empty($request->get('q')) || $request->get('q') === '0') {
            $queryParams['query'] = $request->get('q');
        }

        if (!empty($request->get('limit'))) {
            $queryParams['limit'] = $request->get('limit');
        }

        if (in_array($request->get('orderBy'), $tableConfig['orderBy'], true)) {
            $queryParams['orderBy'] = $request->get('orderBy');

            if (in_array($request->get('direction'), $tableConfig['direction'], true)) {
                $queryParams['direction'] = $request->get('direction');
            }
        }

        if (in_array($request->get('filterBy'), $tableConfig['filterBy'], true)) {
            $queryParams['filterBy'] = $request->get('filterBy');
        }

        return $queryParams;
    }

    /**
     * Returns paginated activities for vue component.
     *
     * @param Request $request
     * @param int     $page
     *
     * @return JsonResponse
     */
    public function getPaginatedActivities(Request $request, int $page = 1): JsonResponse
    {
        try {
            $activities = $this->activityService->getPaginatedActivities($page, $this->sanitizeRequest($request));

            return response()->json([
                'success' => true,
                'message' => 'Activities fetched successfully',
                'data' => $activities,
            ]);
        } catch (Exception $e) {
            logger()->error($e);

            return response()->json(['success' => false, 'message' => 'Error occurred while fetching the data']);
        }
    }

    /*
     * Get languages
     *
     * @return JsonResponse
     */
    public function getLanguagesOrganization(): JsonResponse
    {
        try {
            $languages = getCodeList('Language', 'Activity', filterDeprecated: false);
            $organization = Auth::user()->organization;

            return response()->json([
                'success' => true,
                'message' => 'Languages fetched successfully',
                'data' => [
                    'languages' => $languages,
                    'organization' => $organization,
                ],
            ]);
        } catch (Exception $e) {
            logger()->error($e);

            return response()->json(['success' => false, 'message' => 'Error occurred while fetching the data']);
        }
    }

    /**
     * Get activity detail data type.
     *
     * @return array
     * @throws JsonException
     */
    public function getActivityDetailDataType(): array
    {
        return [
            'languages'                   => getCodeList('Language', 'Activity', code: false),
            'activityDate'                => getCodeList('ActivityDateType', 'Activity', code: false),
            'activityScope'               => getCodeList('ActivityScope', 'Activity', code: false),
            'activityStatus'              => getCodeList('ActivityStatus', 'Activity', code: false),
            'aidType'                     => getCodeList('AidType', 'Activity', code: false),
            'aidTypeVocabulary'           => getCodeList('AidTypeVocabulary', 'Activity', code: false),
            'collaborationType'           => getCodeList('CollaborationType', 'Activity', code: false),
            'conditionType'               => getCodeList('ConditionType', 'Activity', code: false),
            'financeType'                 => getCodeList('FinanceType', 'Activity', code: false),
            'flowType'                    => getCodeList('FlowType', 'Activity', code: false),
            'relatedActivityType'         => getCodeList('RelatedActivityType', 'Activity', code: false),
            'tiedStatus'                  => getCodeList('TiedStatus', 'Activity', code: false),
            'descriptionType'             => getCodeList('DescriptionType', 'Activity', code: false),
            'humanitarianScopeType'       => getCodeList('HumanitarianScopeType', 'Activity', code: false),
            'humanitarianScopeVocabulary' => getCodeList('HumanitarianScopeVocabulary', 'Activity', code: false),
            'earmarkingCategory'          => getCodeList('EarmarkingCategory', 'Activity', code: false),
            'earmarkingModality'          => getCodeList('EarmarkingModality', 'Activity', code: false),
            'cashandVoucherModalities'    => getCodeList('CashandVoucherModalities', 'Activity', code: false),
            'budgetIdentifierVocabulary'  => getCodeList('BudgetIdentifierVocabulary', 'Activity', code: false),
            'sectorVocabulary'            => getCodeList('SectorVocabulary', 'Activity', code: false),
            'sectorCode'                  => getCodeList('SectorCode', 'Activity', code: false),
            'sectorCategory'              => getCodeList('SectorCategory', 'Activity', code: false),
            'sdgGoals'                    => getCodeList('UNSDG-Goals', 'Activity', code: false),
            'sdgTarget'                   => getCodeList('UNSDG-Targets', 'Activity', code: false),
            'regionVocabulary'            => getCodeList('RegionVocabulary', 'Activity', code: false),
            'region'                      => getCodeList('Region', 'Activity', code: false),
            'policyMarkerVocabulary'      => getCodeList('PolicyMarkerVocabulary', 'Activity', code: false),
            'policySignificance'          => getCodeList('PolicySignificance', 'Activity', code: false),
            'policyMarker'                => getCodeList('PolicyMarker', 'Activity', code: false),
            'tagVocabulary'               => getCodeList('TagVocabulary', 'Activity', code: false),
            'budgetType'                  => getCodeList('BudgetType', 'Activity', code: false),
            'budgetStatus'                => getCodeList('BudgetStatus', 'Activity', code: false),
            'otherIdentifierType'         => getCodeList('OtherIdentifierType', 'Activity', code: false),
            'contactType'                 => getCodeList('ContactType', 'Activity', code: false),
            'country'                     => getCodeList('Country', 'Activity', code: false),
            'locationType'                => getCodeList('LocationType', 'Activity', code: false),
            'currency'                    => getCodeList('Currency', 'Activity', code: false),
            'geographicVocabulary'        => getCodeList('GeographicVocabulary', 'Activity', code: false),
            'budgetIdentifier'            => getCodeList('BudgetIdentifier', 'Activity', code: false),
            'organizationType'            => getCodeList('OrganizationType', 'Organization', code: false),
            'geographicLocationReach'     => getCodeList('GeographicLocationReach', 'Activity', code: false),
            'organisationRole'            => getCodeList('OrganisationRole', 'Organization', code: false),
            'documentCategory'            => getCodeList('DocumentCategory', 'Activity', code: false),
            'geographicExactness'         => getCodeList('GeographicExactness', 'Activity', code: false),
            'geographicLocationClass'     => getCodeList('GeographicLocationClass', 'Activity', code: false),
            'resultType'                  => getCodeList('ResultType', 'Activity', code: false),
            'transactionType'             => getCodeList('TransactionType', 'Activity', code: false),
            'crsChannelCode'              => getCodeList('CRSChannelCode', 'Activity', code: false),
        ];
    }

    /*
     * Get languages
     *
     * @return JsonResponse
     */
    public function getActivitiesCountByPublishedStatus(): JsonResponse
    {
        try {
            $translatedMessage = 'Fetched Activities Count By Published Status';

            return response()->json(
                [
                    'success' => true,
                    'message' => $translatedMessage,
                    'data'    => $this->activityService->getActivitiesCountByPublishedStatus((int) Auth::user()->organization_id),
                ]
            );
        } catch (Exception $e) {
            logger()->error($e);

            $translatedMessage = 'Failed to fetch activities count error';

            return response()->json(
                [
                    'success' => false,
                    'message' => $translatedMessage . $e->getMessage(),
                    'data'    => [],
                ],
                500
            );
        }
    }

    /**
     * Trigger the duplicate activity command via API.
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws Exception
     */
    public function duplicateActivity(Request $request): JsonResponse
    {
        if (env('APP_ENV') === 'production') {
            $translatedMessage = 'This Operation Is Not Allowed In The Production Environment.';

            abort(403, $translatedMessage);
        }

        $validated = $request->validate([
            'activity_id'      => 'required|integer',
            'no_of_iterations' => 'required|integer|min:1',
        ]);

        Artisan::call(DuplicateActivities::class, [
            'activity_id'      => $validated['activity_id'],
            'no_of_iterations' => $validated['no_of_iterations'],
        ]);

        $translatedMessage = 'Activity Duplication Completed Successfully';

        return response()->json([
            'message' => $translatedMessage,
        ]);
    }

    private function userOrgNotFound()
    {
        $rolesThatNeedOrg = ['admin'];
        $user = \auth()->user();

        $userRole = $user->role?->role;
        $userOrgId = $user->organization_id;

        if (in_array($userRole, $rolesThatNeedOrg) && is_null($userOrgId)) {
            return true;
        }

        return false;
    }
}
