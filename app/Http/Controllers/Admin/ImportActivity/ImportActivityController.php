<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\ImportActivity;

use App\Helpers\ImportCacheHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Activity\UploadActivity\ImportActivityRequest;
use App\IATI\Services\Activity\ActivityService;
use App\IATI\Services\ImportActivity\ImportCsvService;
use App\IATI\Services\ImportActivity\ImportStatusService;
use App\IATI\Services\ImportActivity\ImportXmlService;
use App\IATI\Services\ImportActivityError\ImportActivityErrorService;
use Exception;
use Illuminate\Contracts\View\Factory;
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
class ImportActivityController extends Controller
{
    /**
     * @var ActivityService
     */
    protected ActivityService $activityService;

    /**
     * @var ImportCsvService
     */
    protected ImportCsvService $importCsvService;

    /**
     * @var ImportXmlService
     */
    protected ImportXmlService $importXmlService;

    /**
     * @var ImportActivityErrorService
     */
    protected ImportActivityErrorService $importActivityErrorService;

    /**
     * @var DatabaseManager
     */
    protected DatabaseManager $db;

    protected ImportStatusService $importStatusService;

    /**
     * @var string
     */
    public string $csv_data_storage_path;

    /**
     * @var string
     */
    public string $xml_data_storage_path;

    /**
     * ActivityController Constructor.
     *
     * @param ActivityService            $activityService
     * @param ImportCsvService           $importCsvService
     * @param ImportXmlService           $importXmlService
     * @param ImportActivityErrorService $importActivityErrorService
     * @param DatabaseManager            $db
     * @param ImportStatusService        $importStatusService
     */
    public function __construct(ActivityService $activityService, ImportCsvService $importCsvService, ImportXmlService $importXmlService, ImportActivityErrorService $importActivityErrorService, DatabaseManager $db, ImportStatusService $importStatusService)
    {
        $this->activityService = $activityService;
        $this->importCsvService = $importCsvService;
        $this->importXmlService = $importXmlService;
        $this->importActivityErrorService = $importActivityErrorService;
        $this->db = $db;
        $this->importStatusService = $importStatusService;
        $this->csv_data_storage_path = config('import.csv_data_storage_path');
        $this->xml_data_storage_path = config('import.xml_data_storage_path');
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

            return view('admin.import.index');
        } catch (Exception $e) {
            logger()->error($e->getMessage());
            $translatedMessage = trans('common/common.error_has_occurred_while_rendering_activity_import_page');

            return response()->json(['success' => false, 'error' => $translatedMessage]);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param ImportActivityRequest $request
     *
     * @return JsonResponse
     */
    public function store(ImportActivityRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $file = $request->file('activity');
            $filetype = $file->extension();
            $user = Auth::user();
            $orgId = $user->organization_id;

            $ongoingImportStatus = $this->importStatusService->getOrganisationImportStatus($orgId);

            if (ImportCacheHelper::isAnotherImportInProgressForOrganisation($orgId) || Arr::get($ongoingImportStatus, 'status') === 'processing') {
                $translatedMessage = trans('common/common.import_is_currently_on_progress_please_cancel_the_current_import_to_continue');

                return response()->json([
                    'success' => false,
                    'status'  => 'error',
                    'errors'  => [
                        'has_ongoing_import' => true,
                        'import_type'        => Arr::get($ongoingImportStatus, 'type', 'xml'),
                    ],
                    'message' => $translatedMessage,
                ]);
            }

            Session::put('import_filetype', $filetype);
            ImportCacheHelper::setSessionConsistentFiletype($orgId, $filetype);

            if ($filetype === 'xml') {
                if ($this->importXmlService->store($file)) {
                    $this->importStatusService->setOrganisationImportStatus($orgId, $user->id, 'xml');
                    $this->importXmlService->startImport($file->getClientOriginalName(), $user->id, $orgId);
                }
            } else {
                if ($this->importCsvService->isCsvFileEmpty($file)) {
                    $translatedMessage = trans('workflow_backend/import_activity_controller.the_file_is_empty_please_upload_file_with_activities');

                    $response = ['success' => false, 'errors' =>['activity' => $translatedMessage]];

                    return response()->json($response);
                }

                $this->importStatusService->setOrganisationImportStatus($orgId, $user->id, 'csv');

                $this->importCsvService->clearOldImport();

                if ($this->importCsvService->storeCsv($file)) {
                    $filename = str_replace(' ', '', $file->getClientOriginalName());
                    $this->importCsvService->startImport($filename)->fireCsvUploadEvent($filename);
                }
            }

            DB::commit();

            ImportCacheHelper::markValidationStepComplete($orgId);
            $translatedMessage = trans('common/common.uploaded_successfully');

            return response()->json(['success' => true, 'message' => $translatedMessage, 'type' => $filetype]);
        } catch (Exception $e) {
            DB::rollback();

            ImportCacheHelper::clearImportCache(Auth::user()->organization_id);

            logger()->error($e->getMessage());

            $translatedMessage = trans('common/common.error_has_occurred_while_rendering_activity_import_page');

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
            $activities = $request->get('activities');

            $orgId = Auth::user()->organization_id;
            $filetype = Session::get('import_filetype') ?? ImportCacheHelper::getSessionConsistentFiletype($orgId);

            if (!ImportCacheHelper::hasOrganisationFinishedValidationStep($orgId)) {
                ImportCacheHelper::clearImportCache(Auth::user()->organization_id);

                $translatedMessage = trans('workflow_backend/import_activity_controller.no_data_to_import');
                Session::flash('error', $translatedMessage);

                return response()->json(['success' => false, 'message' => $translatedMessage, 'type' => $filetype]);
            }

            if ($activities) {
                if ($filetype === 'xml') {
                    $this->importXmlService->create($activities);
                    $this->importStatusService->completeOrganisationImportStatus($orgId, 'xml');
                } else {
                    $this->importCsvService->create($activities);
                    $this->importCsvService->endImport();
                    $this->importStatusService->completeOrganisationImportStatus($orgId, 'csv');
                }
            }

            $this->db->commit();
            ImportCacheHelper::markImportStepComplete($orgId);

            Session::forget('import_filetype');
            Session::forget('error');

            ImportCacheHelper::clearImportCache($orgId);

            $translatedMessage = trans('workflow_backend/import_activity_controller.imported_data_successfully');
            Session::flash('success', $translatedMessage);

            return response()->json(['success' => true, 'message' => $translatedMessage, 'type' => $filetype]);
        } catch (Exception $e) {
            $this->db->rollBack();
            $this->deleteOngoingImports();

            logger()->error($e);

            ImportCacheHelper::clearImportCache(Auth::user()->organization_id);

            $translatedMessage = trans('common/common.error_has_occurred_while_importing_activity');
            Session::flash('error', $translatedMessage);

            return response()->json(['success' => false, 'message' => $translatedMessage]);
        }
    }

    /**
     * Show the status page for the Csv Import process.
     *
     * @return Factory|\Illuminate\View\View
     */
    public function status(): View|RedirectResponse
    {
        try {
            $orgId = Auth::user()->organization_id;
            $userId = Auth::user()->id;
            $filetype = Session::get('import_filetype') ?? ImportCacheHelper::getSessionConsistentFiletype($orgId);

            if (!$orgId) {
                $translatedMessage = trans('common/common.user_is_not_associated_with_any_organization');

                Session::flash('error', $translatedMessage);

                return redirect()->route('admin.activities.index');
            }

            if (!$filetype) {
                $translatedMessage = 'Something went wrong. Please try again.';

                Session::flash('error', $translatedMessage);

                $this->importStatusService->deleteOngoingImports($orgId);

                return redirect()->route('admin.activities.index');
            }

            $status = awsGetFile(sprintf('%s/%s/%s/%s', $filetype === 'xml' ? $this->xml_data_storage_path : $this->csv_data_storage_path, $orgId, $userId, 'status.json'));
            $schema_error = awsGetFile(sprintf('%s/%s/%s/%s', $filetype === 'xml' ? $this->xml_data_storage_path : $this->csv_data_storage_path, $orgId, $userId, 'schema_error.log'));

            if (!$status) {
                $translatedMessage = 'status.json file not present in AWS. Please try again.';

                Session::put('error', $translatedMessage);
                $this->importStatusService->deleteOngoingImports($orgId);

                return redirect()->route('admin.activities.index');
            }

            $status = json_decode($status, true, 512, JSON_THROW_ON_ERROR);

            if ($schema_error) {
                Session::put('error', $status['message'] . '. To see errors <b>
                <a
                  href=' . awsUrl(sprintf('%s/%s/%s/%s', $filetype === 'xml' ? $this->xml_data_storage_path : $this->csv_data_storage_path, $orgId, $userId, 'schema_error.log')) .
                    ' target="_blank">
                    ' . trans('workflow_backend/import_activity_controller.open_error_file') .
                    '</a>
                </b>');
                $this->importStatusService->deleteOngoingImports($orgId);

                return redirect()->route('admin.activities.index');
            }

            if (!$status['success']) {
                Session::put('error', $status['message']);

                return redirect()->route('admin.activities.index');
            }

            return view('admin.import.list');
        } catch (Exception $e) {
            logger()->error($e->getMessage());
            $translatedMessage = trans('workflow_backend/import_activity_controller.error_has_occurred_while_checking_the_status');

            return redirect()->route('admin.activities.index')->withResponse(['success' => false, 'error' => $translatedMessage]);
        }
    }

    /**
     * Check Import Status.
     *
     * @return JsonResponse
     */
    public function getImportListData(): JsonResponse
    {
        try {
            $orgId = Auth::user()->organization_id;
            $userId = Auth::user()->id;

            $filetype = Session::get('import_filetype') ?? ImportCacheHelper::getSessionConsistentFiletype($orgId);

            $status = awsGetFile(sprintf('%s/%s/%s/%s', $filetype === 'xml' ? $this->xml_data_storage_path : $this->csv_data_storage_path, $orgId, $userId, 'status.json'));
            $schema_error = awsGetFile(sprintf('%s/%s/%s/%s', $filetype === 'xml' ? $this->xml_data_storage_path : $this->csv_data_storage_path, $orgId, $userId, 'schema_error.log'));

            if (!$status) {
                $translatedMessage = 'status.json file not present in AWS. Please try again.';

                Session::put('error', $translatedMessage);
                $this->importStatusService->deleteOngoingImports($orgId);

                return response()->json(['status' => 'error', 'message' => $translatedMessage]);
            }

            $status = json_decode($status, true, 512, JSON_THROW_ON_ERROR);

            if ($schema_error) {
                $translatedMessage = $status['message'] . '. To see errors <b><a href=' . awsUrl(sprintf('%s/%s/%s/%s', $filetype === 'xml' ? $this->xml_data_storage_path : $this->csv_data_storage_path, $orgId, $userId, 'schema_error.log')) . ' target="_blank">Open Error File</a></b>';

                Session::put('error', $translatedMessage);
                $this->importStatusService->deleteOngoingImports($orgId);

                return response()->json(['status' => 'error', 'message' => $translatedMessage]);
            }

            if (!$filetype) {
                $translatedMessage = trans('workflow_backend/import_activity_controller.please_upload_csv_or_xml_file_to_import_activity');

                Session::put('error', $translatedMessage);

                return response()->json(['status' => 'error', 'message' => $translatedMessage]);
            }

            if ($filetype === 'xml') {
                $result = $this->importXmlService->loadJsonFile('status.json');
                $data = $this->importXmlService->loadJsonFile('valid.json');
            } else {
                $result = $this->importCsvService->getAwsCsvData('status.json');
                $data = $this->importCsvService->getAwsCsvData('valid.json');
            }

            $status = strcasecmp($result->message, 'Complete') === 0;

            if ($status && !$data) {
                throw new Exception();
            }

            $returnVal = ['status' => $status, 'data' => $data];

            return response()->json($returnVal);
        } catch (Exception $e) {
            $this->deleteOngoingImports();

            $translatedMessage = trans('workflow_backend/import_activity_controller.error_has_occurred_while_importing_activities');

            Session::flash('error', $translatedMessage);

            logger()->error($e);

            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * Get the remaining valid data.
     *
     * @return array
     * @throws \JsonException
     */
    public function getValidData(): array
    {
        $filepath = $this->importCsvService->getFilePath();
        $activities = [];

        if (file_exists($filepath)) {
            $activities = json_decode(file_get_contents($filepath), true, 512, JSON_THROW_ON_ERROR);
        }

        return $activities;
    }

    /**
     * Returns csv file import template.
     *
     * @return bool|JsonResponse|string
     */
    public function downloadTemplate(): bool|JsonResponse|string
    {
        try {
            return file_get_contents(app_path(sprintf('CsvImporter/Templates/%s/%s.csv', 'Activity', 'other_fields_transaction')));
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
     * @return JsonResponse
     */
    public function checkOngoingImport(): JsonResponse
    {
        $orgId = Auth::user()->organization_id;
        $status = $this->importStatusService->getOrganisationImportStatus($orgId);

        $hasOngoingImport = !empty($status);
        $message = $hasOngoingImport
            ? trans('workflow_backend/import_activity_controller.ongoing_import_in_progress_for_this_organisation')
            : trans('workflow_backend/import_activity_controller.no_import_in_progress_for_this_organisation');

        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => [
                'has_ongoing_import' => $hasOngoingImport,
                'import_type'        => $hasOngoingImport ? $status['type'] : '',
            ],
        ]);
    }

    /**
     * @return JsonResponse
     */
    public function deleteOngoingImports(): JsonResponse
    {
        try {
            DB::beginTransaction();

            $this->importStatusService->deleteOngoingImports(Auth::user()->organization_id);

            DB::commit();
            $translatedMessage = trans('workflow_backend/import_activity_controller.deleted_ongoing_imports');

            return response()->json([
                'success' => true,
                'message' => $translatedMessage,
                'data'    => [
                    'has_ongoing_import' => false,
                    'import_type'        => '',
                ],
            ]);
        } catch (Exception $e) {
            logger($e);
            DB::rollBack();
            $translatedMessage = trans('workflow_backend/import_activity_controller.failed_to_delete_ongoing_imports');

            return response()->json([
                'success' => false,
                'message' => $translatedMessage,
                'data'    => [
                    'has_ongoing_import' => false,
                    'import_type'        => '',
                ],
            ]);
        }
    }
}
