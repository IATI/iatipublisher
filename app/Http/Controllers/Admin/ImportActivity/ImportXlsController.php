<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\ImportActivity;

use App\Http\Controllers\Controller;
use App\Http\Requests\Activity\UploadActivity\ImportXlsRequest;
use App\IATI\Services\Activity\ActivityService;
use App\IATI\Services\ImportActivity\ImportStatusService;
use App\IATI\Services\ImportActivity\ImportXlsService;
use App\IATI\Services\ImportActivityError\ImportActivityErrorService;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Database\DatabaseManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

/**
 * Class ActivityController.
 */
class ImportXlsController extends Controller
{
    /**
     * @var ActivityService
     */
    protected ActivityService $activityService;

    /**
     * @var ImportXlsService
     */
    protected ImportXlsService $importXlsService;

    /**
     * @var ImportActivityErrorService
     */
    protected ImportActivityErrorService $importActivityErrorService;

    /**
     * @var DatabaseManager
     */
    protected DatabaseManager $db;

    /**
     * @var string
     */
    public string $xls_data_storage_path;

    /**
     * @var ImportStatusService
     */
    private ImportStatusService $importStatusService;

    /**
     * ActivityController Constructor.
     *
     * @param ActivityService                                       $activityService
     * @param ImportXlsService                                      $importXlsService
     * @param ImportActivityErrorService                            $importActivityErrorService
     * @param DatabaseManager                                       $db
     * @param ImportStatusService $importStatusService
     */
    public function __construct(ActivityService $activityService, ImportXlsService $importXlsService, ImportActivityErrorService $importActivityErrorService, DatabaseManager $db, ImportStatusService $importStatusService)
    {
        $this->activityService = $activityService;
        $this->importXlsService = $importXlsService;
        $this->importActivityErrorService = $importActivityErrorService;
        $this->importStatusService = $importStatusService;
        $this->db = $db;
        $this->xls_data_storage_path = config('import.xls_data_storage_path');
    }

    /**
     * Display a listing of the resource.
     *
     * @return View|JsonResponse|RedirectResponse
     */
    public function index(): View|JsonResponse|RedirectResponse
    {
        try {
            if (!Auth::user()->organization_id) {
                $translatedMessage = trans('common/common.user_is_not_associated_with_any_organization');

                Session::put('error', $translatedMessage);

                return redirect()->route('admin.activities.index');
            }

            return view('admin.import.xls.index');
        } catch (Exception $e) {
            logger()->error($e->getMessage());
            $translatedMessage = trans('common/common.error_has_occurred_while_rendering_activity_import_page');

            return response()->json(['success' => false, 'error' => $translatedMessage]);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param ImportXlsRequest $request
     *
     * @return JsonResponse
     */
    public function store(ImportXlsRequest $request): JsonResponse
    {
        try {
            $file = $request->file('activity');
            $xlsType = $request->get('xlsType');
            $status = $this->importXlsService->getImportStatus();
            $orgId = Auth::user()->organization_id;
            $ongoingImportStatus = $this->importStatusService->getOrganisationImportStatus($orgId);

            if (!empty($status) || Arr::get($ongoingImportStatus, 'status') === 'processing') {
                $translatedMessage = trans('common/common.import_is_currently_on_progress_please_cancel_the_current_import_to_continue');

                return response()->json([
                    'success' => false,
                    'message' => $translatedMessage,
                    'data'    => [
                        'has_ongoing_import' => true,
                        'import_type'        => Arr::get($ongoingImportStatus, 'type', 'xls'),
                    ],
                ]);
            }

            if ($this->importXlsService->store($file)) {
                $user = Auth::user();
                $this->importXlsService->startImport($file->getClientOriginalName(), $user->id, $user->organization_id, $xlsType);
            }

            $translatedMessage = trans('common/common.uploaded_successfully');

            return response()->json(['success' => true, 'message' => $translatedMessage]);
        } catch (Exception $e) {
            logger()->error($e->getMessage());

            $translatedMessage = trans('common/common.error_has_occurred_while_rendering_activity_import_page');

            Session::flash('error', $translatedMessage);

            return response()->json(['success' => false, 'error' => $translatedMessage]);
        }
    }

    /**
     * Import validated activities into the database.
     *
     * @param Request $request
     *
     * @return mixed
     * @throws \Throwable
     */
    public function importValidatedActivities(Request $request): mixed
    {
        try {
            $this->db->beginTransaction();
            $status = $this->importXlsService->getImportStatus();
            $activities = $request->get('activities');

            if (empty($status)) {
                $translatedMessage = trans('workflow_backend/import_xls_controller.please_ensure_that_you_have_uploaded_xls_file');

                Session::flash('error', $translatedMessage);

                return response()->json(['success' => false, 'message' => $translatedMessage]);
            }
            if (empty($activities)) {
                $translatedMessage = trans('workflow_backend/import_xls_controller.please_select_the_data_you_want_to_add');

                Session::flash('error', $translatedMessage);

                return response()->json(['success' => false, 'message' => $translatedMessage]);
            }

            $xlsType = $status['template'];

            $this->importXlsService->create($activities, $xlsType);

            $this->importXlsService->deleteImportStatus();

            $this->db->commit();

            $translatedMessage = trans('workflow_backend/import_xls_controller.xls_file_with_xlstype_imported_successfully', ['xlsType'=>$xlsType]);

            Session::put('success', $translatedMessage);

            return response()->json(['success' => true, 'message' => $translatedMessage]);
        } catch (Exception $e) {
            $this->db->rollBack();
            $this->importXlsService->deleteImportStatus();

            $translatedMessage = trans('common/common.error_has_occurred_while_importing_activity');
            Session::flash('error', $translatedMessage);

            logger()->error($e);

            return response()->json(['success' => false, 'message' => $translatedMessage]);
        }
    }

    /**
     * Checks if import progress data is present for user in database.
     *
     * @return JsonResponse
     */
    public function checkXlsStatus(): JsonResponse
    {
        try {
            $status = $this->importXlsService->getImportStatus();

            if (isset($status['status']) && $status['status'] === 'failed') {
                $result = $this->importXlsService->getAwsXlsData('status.json');
                $status['message'] = $result->message;
            }

            $translatedMessage = trans('workflow_backend/import_xls_controller.import_status_accessed_successfully');

            return response()->json(['success' => true, 'message' => $translatedMessage, 'status' => $status]);
        } catch (Exception $e) {
            logger()->error($e->getMessage());
            $translatedMessage = trans('workflow_backend/import_xls_controller.error_has_occurred_while_trying_to_check_import_status');

            return response()->json(['success' => false, 'message' => $translatedMessage]);
        }
    }

    /**
     * Check Import Status based on status.json file present at AWS.
     *
     * @return JsonResponse
     */
    public function checkImportInProgressForPolling(): JsonResponse
    {
        try {
            $status = $this->importXlsService->getImportStatus();

            if (empty($status)) {
                $translatedMessage = trans('workflow_backend/import_xls_controller.please_ensure_that_you_have_uploaded_xls_file');

                return response()->json(['status' => 'error', 'message' => $translatedMessage]);
            }

            $result = $this->importXlsService->getAwsXlsData('status.json');

            return response()->json(['success' => true, 'data' => $result]);
        } catch (Exception $e) {
            logger()->error($e->getMessage());

            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Delete upload error with activityId.
     *
     * @param mixed $activityId
     *
     * @return JsonResponse
     */
    public function deleteImportError($activityId): JsonResponse
    {
        try {
            $this->importActivityErrorService->deleteImportError($activityId);
            $translatedMessage = trans('common/common.import_error_for_activity_has_been_successfully_deleted');

            return response()->json(['success' => true, 'message' => $translatedMessage]);
        } catch (Exception $e) {
            logger()->error($e->getMessage());
            $translatedMessage = trans('common/common.error_has_occurred_while_trying_to_delete_import_error');

            return response()->json(['success' => false, 'message' => $translatedMessage]);
        }
    }

    /**
     * Delete upload error with activityId.
     *
     * @return JsonResponse
     */
    public function deleteImportStatus(): JsonResponse
    {
        try {
            DB::beginTransaction();

            $this->importXlsService->deleteImportStatus();
            $this->importStatusService->deleteOngoingImports(Auth::user()->organization_id);

            DB::commit();
            $translatedMessage = trans('workflow_backend/import_xls_controller.import_status_for_organization_has_been_successfully_deleted');

            return response()->json(['success' => true, 'message' => $translatedMessage]);
        } catch (Exception $e) {
            DB::rollBack();
            logger()->error($e->getMessage());
            $translatedMessage = trans('common/common.error_has_occurred_while_trying_to_delete_import_error');

            return response()->json(['success' => false, 'message' => $translatedMessage]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @return View|JsonResponse|RedirectResponse
     */
    public function show(): View|JsonResponse|RedirectResponse
    {
        try {
            $status = $this->importXlsService->getImportStatus();

            if (empty($status)) {
                $translatedMessage = trans('workflow_backend/import_xls_controller.please_upload_xls_file_to_continue');

                return redirect()->route('admin.activities.index')->with('error', $translatedMessage);
            }

            $importData = $this->importXlsService->getAwsXlsData('valid.json');

            $globalError = $this->importXlsService->getAwsXlsData('globalError.json');

            $errors = Arr::get($globalError, 'errors');
            $errors = empty($errors) ? null : $errors;
            $errorCount = Arr::get($globalError, 'error_count', 0);

            return view(
                'admin.import.xls.list',
                compact('status', 'importData', 'errors', 'errorCount')
            );
        } catch (Exception $e) {
            logger()->error($e->getMessage());
            $translatedMessage = trans('common/common.error_has_occurred_while_rendering_activity_import_page');

            return redirect()->route('admin.activities.index')->with('error', $translatedMessage);
        }
    }
}
