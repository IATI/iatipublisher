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
        $this->csv_data_storage_path = env('CSV_DATA_STORAGE_PATH', 'CsvImporter/tmp');
        $this->xml_data_storage_path = env('XML_DATA_STORAGE_PATH', 'XmlImporter/tmp');
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
                Session::put('error', 'User is not associated with any organization.');

                return redirect()->route('admin.activities.index');
            }

            return view('admin.import.index');
        } catch (Exception $e) {
            logger()->error($e->getMessage());

            return response()->json(['success' => false, 'error' => 'Error has occurred while rendering activity import page.']);
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

            if (ImportCacheHelper::hasOngoingImport($orgId) || Arr::get($ongoingImportStatus, 'status') === 'processing') {
                return response()->json([
                    'success' => false,
                    'status'  => 'error',
                    'errors'  => [
                        'has_ongoing_import' => true,
                        'import_type'        => Arr::get($ongoingImportStatus, 'type', 'xml'),
                    ],
                    'message' => 'Import is currently on progress. Please cancel the current import to continue.',
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
                    $response = ['success' => false, 'errors' =>['activity' => 'The file is empty. Please upload file with activities.']];

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

            ImportCacheHelper::setImportStepToValidating($orgId);

            return response()->json(['success' => true, 'message' => 'Uploaded successfully', 'type' => $filetype]);
        } catch (Exception $e) {
            DB::rollback();

            ImportCacheHelper::clearImportCache(Auth::user()->organization_id);

            logger()->error($e->getMessage());

            return response()->json(['success' => false, 'error' => 'Error has occurred while rendering activity import page.']);
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
            logger('activities');
            logger($activities);
            $orgId = Auth::user()->organization_id;
            $filetype = Session::get('import_filetype') ?? ImportCacheHelper::getSessionConsistentFiletype($orgId);

            if (!ImportCacheHelper::organisationHasCompletedValidatingData($orgId)) {
                return response()->json(['success' => false, 'message' => 'No data to import.', 'type' => $filetype]);
            }

            ImportCacheHelper::setImportStepToImported($orgId);

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

            Session::forget('import_filetype');
            Session::forget('error');
            Session::put('success', 'Imported data successfully.');

            ImportCacheHelper::clearImportCache($orgId);

            return response()->json(['success' => true, 'message' => 'Imported successfully', 'type' => $filetype]);
        } catch (Exception $e) {
            Session::put('error', 'Error occurred while importing activity');
            logger()->error($e);
            ImportCacheHelper::clearImportCache(Auth::user()->organization_id);

            return redirect()->back()->withResponse(['success' => false, 'message' => 'Error has occurred while importing activity.']);
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
                Session::put('error', 'User is not associated with any organization.');

                return redirect()->route('admin.activities.index');
            }

            if (!$filetype) {
                return redirect()->route('admin.activities.index');
            }

            $status = awsGetFile(sprintf('%s/%s/%s/%s', $filetype === 'xml' ? $this->xml_data_storage_path : $this->csv_data_storage_path, $orgId, $userId, 'status.json'));
            $schema_error = awsGetFile(sprintf('%s/%s/%s/%s', $filetype === 'xml' ? $this->xml_data_storage_path : $this->csv_data_storage_path, $orgId, $userId, 'schema_error.log'));

            if (!$status) {
                Session::put('error', 'status.json file not present in AWS. Please try again.');
                $this->importStatusService->deleteOngoingImports($orgId);

                return redirect()->route('admin.activities.index');
            }

            $status = json_decode($status, true, 512, JSON_THROW_ON_ERROR);

            if ($schema_error) {
                Session::put('error', $status['message'] . '. To see errors <b>
                <a href=' . awsUrl(sprintf('%s/%s/%s/%s', $filetype === 'xml' ? $this->xml_data_storage_path : $this->csv_data_storage_path, $orgId, $userId, 'schema_error.log')) . ' target="_blank">Open Error File</a></b>');

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

            return redirect()->route('admin.activities.index')->withResponse(['success' => false, 'error' => 'Error has occurred while checking the status.']);
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
            $filetype = Session::get('import_filetype') ?? ImportCacheHelper::getSessionConsistentFiletype($orgId);

            if (!$filetype) {
                Session::put('error', 'Please upload csv or xml file to import activity.');

                return response()->json(['status' => 'error', 'message' => 'Please upload csv or xml file to import activity.']);
            }

            if ($filetype === 'xml') {
                $result = $this->importXmlService->loadJsonFile('status.json');
                $data = $this->importXmlService->loadJsonFile('valid.json');
            } else {
                $result = $this->importCsvService->getAwsCsvData('status.json');
                $data = $this->importCsvService->getAwsCsvData('valid.json');
            }

            $status = strcasecmp($result->message, 'Complete') === 0;

            if (!$data) {
                Session::put('error', 'Error has occurred while importing activities.');
            }

            return response()->json(['status' => $status, 'data' => $data]);
        } catch (Exception $e) {
            logger()->error($e->getMessage());

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

            return response()->json(['success' => true, 'message' => 'Import error for activity has been successfully deleted.']);
        } catch (Exception $e) {
            logger()->error($e->getMessage());

            return response()->json(['success' => false, 'message' => 'Error has occurred while trying to delete import error.']);
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
            ? 'Ongoing Import in progress for this organisation.'
            : 'No Import in progress for this organisation.';

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

            return response()->json([
                'success' => true,
                'message' => 'Deleted ongoing imports.',
                'data'    => [
                    'has_ongoing_import' => false,
                    'import_type'        => '',
                ],
            ]);
        } catch (Exception $e) {
            logger($e);
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete  ongoing imports.',
                'data'    => [
                    'has_ongoing_import' => false,
                    'import_type'        => '',
                ],
            ]);
        }
    }
}
