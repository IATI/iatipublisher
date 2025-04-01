<?php

declare(strict_types=1);

namespace App\IATI\Services\ImportActivity;

use App\IATI\Repositories\Activity\ActivityRepository;
use App\IATI\Repositories\Activity\IndicatorRepository;
use App\IATI\Repositories\Activity\PeriodRepository;
use App\IATI\Repositories\Activity\ResultRepository;
use App\IATI\Repositories\Activity\TransactionRepository;
use App\IATI\Repositories\Import\ImportActivityErrorRepository;
use App\IATI\Repositories\Import\ImportStatusRepository;
use App\IATI\Services\Activity\ActivityService;
use App\IATI\Services\ElementCompleteService;
use App\XlsImporter\Events\XlsWasUploaded;
use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class XmlImportManager.
 */
class ImportXlsService
{
    /**
     * Temporary Xml file storage location.
     */
    public string $xls_file_storage_path;

    /**
     * Temporary Xml data storage location.
     */
    public string $xls_data_storage_path;

    /**
     * @var ActivityRepository
     */
    protected ActivityRepository $activityRepository;

    /**
     * @var TransactionRepository
     */
    protected TransactionRepository $transactionRepository;

    /**
     * @var ResultRepository
     */
    protected ResultRepository $resultRepository;

    /**
     * @var PeriodRepository
     */
    protected PeriodRepository $periodRepository;

    /**
     * @var IndicatorRepository
     */
    protected IndicatorRepository $indicatorRepository;

    /**
     * @var ImportActivityErrorRepository
     */
    protected ImportActivityErrorRepository $importActivityErrorRepo;

    /**
     * @var ImportStatusRepository
     */
    protected ImportStatusRepository $importStatusRepo;

    private ElementCompleteService $elementCompleteService;

    /**
     * XmlImportManager constructor.
     *
     * @param ActivityRepository    $activityRepository
     * @param TransactionRepository $transactionRepository
     * @param ResultRepository      $resultRepository
     * @param PeriodRepository      $periodRepository
     * @param IndicatorRepository   $indicatorRepository
     * @param ImportActivityErrorRepository  $importActivityErrorRepo
     * @param ImportStatusRepository  $importStatusRepo
     * @param XmlService            $xmlService
     */
    public function __construct(
        ActivityRepository $activityRepository,
        TransactionRepository $transactionRepository,
        ResultRepository $resultRepository,
        PeriodRepository $periodRepository,
        IndicatorRepository $indicatorRepository,
        ImportActivityErrorRepository $importActivityErrorRepo,
        ImportStatusRepository $importStatusRepo,
        ElementCompleteService $elementCompleteService,
    ) {
        $this->transactionRepository = $transactionRepository;
        $this->activityRepository = $activityRepository;
        $this->resultRepository = $resultRepository;
        $this->indicatorRepository = $indicatorRepository;
        $this->importActivityErrorRepo = $importActivityErrorRepo;
        $this->importStatusRepo = $importStatusRepo;
        $this->periodRepository = $periodRepository;
        $this->elementCompleteService = $elementCompleteService;
        $this->xls_file_storage_path = env('XLS_FILE_STORAGE_PATH', 'XlsImporter/file');
        $this->xls_data_storage_path = env('XLS_DATA_STORAGE_PATH', 'XlsImporter/tmp');
    }

    /**
     * Temporarily store the uploaded Xml file.
     *
     * @param UploadedFile $file
     *
     * @return bool
     */
    public function store(UploadedFile $file): bool
    {
        try {
            awsDeleteDirectory(sprintf('%s/%s/%s', $this->xls_file_storage_path, Auth::user()->organization_id, Auth::user()->id));

            return awsUploadFile(sprintf('%s/%s/%s/%s', $this->xls_file_storage_path, Auth::user()->organization_id, Auth::user()->id, $file->getClientOriginalName()), $file->getContent());
        } catch (Exception $exception) {
            logger()->error(
                sprintf('Error uploading Xls file due to %s', $exception->getMessage()),
                [
                    'trace' => $exception->getTraceAsString(),
                    'user' => auth()->user()->id,
                ]
            );

            return false;
        }
    }

    /**
     * Create Valid activities.
     *
     * @param $activities
     * @param $xlsType
     *
     * @return bool
     * @throws \JsonException|BindingResolutionException
     */
    public function create($activities, $xlsType = 'activity'): bool
    {
        switch ($xlsType) {
            case 'activity':
                $this->saveActivities($activities);
                $this->importStatusRepo->completeOrganisationImportStatus(Auth::user()->organization_id, 'xls');
                break;
            case 'result':
                $this->saveResults($activities);
                break;
            case 'indicator':
                $this->saveIndicator($activities);
                break;
            case 'period':
                $this->savePeriod($activities);
                break;
            default:
                return false;
        }

        return true;
    }

    /**
     * Save transaction of mapped activity in database.
     *
     * @param $transactions
     * @param $activityId
     *
     * @return $this
     * @throws \JsonException
     * @throws \ReflectionException
     */
    protected function saveTransactions($transactions, $activityId): static
    {
        $transactionList = [];

        if (!empty($transactions)) {
            foreach ($transactions as $transaction) {
                $transactionList[] = [
                    'activity_id' => $activityId,
                    'transaction' => json_encode($transaction),
                    'deprecation_status_map'=>json_encode(refreshTransactionDeprecationStatusMap($transaction)),
                ];
            }

            $this->transactionRepository->upsert($transactionList, 'id');
        }

        return $this;
    }

    /**
     * Save result of mapped activity in database.
     *
     * @param $activities
     *
     * @return void
     *
     * @throws BindingResolutionException
     * @throws \JsonException
     * @throws \ReflectionException
     */
    protected function saveActivities($activities): void
    {
        $organizationId = Auth::user()->organization->id;
        $userId = Auth::user()->id;
        $contents = json_decode(awsGetFile(sprintf('%s/%s/%s/%s', $this->xls_data_storage_path, $organizationId, $userId, 'valid.json')), false, 512, JSON_THROW_ON_ERROR | 0);

        foreach ($activities as $value) {
            $storeActivity = null;
            $activity = unsetErrorFields($contents[$value]);
            $activity['data'] = unsetDeprecatedFieldValues(Arr::get($activity, 'data', []));

            $activityData = Arr::get($activity, 'data', []);

            $organizationId = Auth::user()->organization->id;
            $existingId = Arr::get($activity, 'existing', false);

            if ($existingId || $this->activityRepository->getActivityWithIdentifier($organizationId, Arr::get($activityData, 'iati_identifier.activity_identifier'))) {
                $oldActivity = $this->activityRepository->find($existingId);
                $activityData = $this->fillActivityData($activityData);
                $activityData['upload_medium'] = 'xls';
                $activityData['deprecation_status_map'] = refreshActivityDeprecationStatusMap($activityData);

                if ($oldActivity['has_ever_been_published']) {
                    $activityData['iati_identifier']['iati_identifier_text'] = $oldActivity['iati_identifier']['iati_identifier_text'];
                    $activityData['iati_identifier']['present_organization_identifier'] = $oldActivity['iati_identifier']['present_organization_identifier'];
                } else {
                    $organizationIdentifier = Auth::user()->organization->identifier;
                    $activityData['iati_identifier']['iati_identifier_text'] = $organizationIdentifier . '-' . Arr::get($activityData, 'iati_identifier.activity_identifier');
                    $activityData['iati_identifier']['present_organization_identifier'] = $organizationIdentifier;
                }

                $this->activityRepository->update($existingId, $activityData);
                $this->transactionRepository->deleteTransaction($existingId);
                $this->saveTransactions(Arr::get($activityData, 'transactions', []), $existingId);

                $storeActivity = $this->activityRepository->find($existingId);

                if (!empty($activity['errors'])) {
                    $this->importActivityErrorRepo->updateOrCreateError($existingId, $activity['errors']);
                } else {
                    $this->importActivityErrorRepo->deleteImportError($existingId);
                }
            } else {
                /** @var $activityService ActivityService */
                $activityService = app(ActivityService::class);
                $organizationIdentifier = Auth::user()->organization->identifier;
                $defaultValues = $activityService->getDefaultValues();

                $activityData['org_id'] = $organizationId;
                $activityData['upload_medium'] = 'xls';

                $activityData['collaboration_type'] = $this->getIntOrNullFromActivityDataOrDefaultValues(
                    activityData         : $activityData,
                    activityDataAccessKey: 'collaboration_type',
                    defaultValues        : $defaultValues,
                    defaultDataKey       : 'default_collaboration_type'
                );
                $activityData['flow_type'] = $this->getIntOrNullFromActivityDataOrDefaultValues(
                    activityData         : $activityData,
                    activityDataAccessKey: 'flow_type',
                    defaultValues        : $defaultValues,
                    defaultDataKey       : 'default_flow_type'
                );
                $activityData['finance_type'] = $this->getIntOrNullFromActivityDataOrDefaultValues(
                    activityData         : $activityData,
                    activityDataAccessKey: 'finance_type',
                    defaultValues        : $defaultValues,
                    defaultDataKey       : 'default_finance_type'
                );
                $activityData['tied_status'] = $this->getIntOrNullFromActivityDataOrDefaultValues(
                    activityData         : $activityData,
                    activityDataAccessKey: 'tied_status',
                    defaultValues        : $defaultValues,
                    defaultDataKey       : 'default_tied_status'
                );

                $activityData['iati_identifier']['iati_identifier_text'] = $organizationIdentifier . '-' . $activityData['iati_identifier']['activity_identifier'];
                $activityData['iati_identifier']['present_organization_identifier'] = $organizationIdentifier;
                $activityData['iati_identifier'] = trimStringValueInArray($activityData['iati_identifier']);

                if (isset($defaultValues['default_aid_type']) && !empty($defaultValues['default_aid_type'])) {
                    $activityData['default_aid_type'] = [
                        [
                            'default_aid_type_vocabulary' => '1',
                            'default_aid_type' => $defaultValues['default_aid_type'],
                        ],
                    ];
                }

                $storeActivity = $this->activityRepository->store($activityData);
                $this->saveTransactions(Arr::get($activityData, 'transactions', []), $storeActivity->id);

                if (!empty($activity['errors'])) {
                    $this->importActivityErrorRepo->updateOrCreateError($storeActivity->id, $activity['errors']);
                }
            }

            $this->elementCompleteService->refreshElementStatus(
                $this->activityRepository->getActivitityWithRelationsById($storeActivity->id)
            );
        }
    }

    public function getIntOrNullFromActivityDataOrDefaultValues(
        array $activityData,
        string $activityDataAccessKey,
        array $defaultValues,
        string $defaultDataKey
    ): ?int {
        $value = Arr::get($activityData, $activityDataAccessKey);

        if (!empty($value)) {
            return (int) $value;
        }

        $defaultValue = Arr::get($defaultValues, $defaultDataKey);

        return !empty($defaultValue) ? (int) $defaultValue : null;
    }

    /**
     * Fill elements for activity for update.
     *
     * @param $activityData
     *
     * @return array
     */
    protected function fillActivityData($activityData): array
    {
        $activityElements = [
            'iati_identifier',
            'other_identifier',
            'title',
            'description',
            'activity_status',
            'activity_date',
            'contact_info',
            'activity_scope',
            'participating_org',
            'recipient_country',
            'recipient_region',
            'location',
            'sector',
            'country_budget_items',
            'humanitarian_scope',
            'policy_marker',
            'collaboration_type',
            'default_flow_type',
            'default_finance_type',
            'default_aid_type',
            'default_tied_status',
            'budget',
            'planned_disbursement',
            'capital_spend',
            'document_link',
            'related_activity',
            'legacy_data',
            'conditions',
            'default_field_values',
            'tag',
            'reporting_org',
            'transactions',
        ];

        $filledData = [];

        foreach ($activityElements as $element) {
            $filledData[$element] = Arr::get($activityData, $element, null);
        }

        return $filledData;
    }

    /**
     * Save result of mapped activity in database.
     *
     * @param $results
     *
     * @return bool
     */
    protected function saveResults($results): bool
    {
        $organizationId = Auth::user()->organization->id;
        $userId = Auth::user()->id;
        $identifiers = $this->dbIatiIdentifiers($organizationId, 'activity');
        $contents = json_decode(awsGetFile(sprintf('%s/%s/%s/%s', $this->xls_data_storage_path, $organizationId, $userId, 'valid.json')), false, 512, 0);

        foreach ($results as $value) {
            $result = unsetErrorFields($contents[$value]);
            $resultData = Arr::get($result, 'data', []);
            $organizationId = Auth::user()->organization->id;
            $existenceId = Arr::get($result, 'existing', false);
            $parentIdentifier = Arr::get($result, 'parentIdentifier', false);
            $code = Arr::get($result, 'code', false);
            $activityId = $identifiers[$parentIdentifier];
            $this->storeImportErrors($activityId, $result['errors'], 'result>' . $code);
            $deprecationStatusMap = refreshResultDeprecationStatusMap($resultData);

            if ($existenceId) {
                $this->resultRepository->update($existenceId, ['result_code' => $code, 'result' => $resultData, 'deprecation_status_map'=>$deprecationStatusMap]);
            } else {
                $this->resultRepository->store(['result' => $resultData, 'result_code' => $code, 'activity_id' => $activityId, 'deprecation_status_map'=>$deprecationStatusMap]);
            }
        }

        return true;
    }

    /**
     * Save indicator of mapped activity in database.
     *
     * @param $results
     *
     * @return void
     */
    protected function saveIndicator($indicators): void
    {
        $organizationId = Auth::user()->organization->id;
        $userId = Auth::user()->id;
        $identifiers = $this->dbIatiIdentifiers($organizationId, 'result');
        $contents = json_decode(awsGetFile(sprintf('%s/%s/%s/%s', $this->xls_data_storage_path, $organizationId, $userId, 'valid.json')), false, 512, 0);

        foreach ($indicators as $value) {
            $indicator = unsetErrorFields($contents[$value]);
            $indicatorData = Arr::get($indicator, 'data', []);
            $organizationId = Auth::user()->organization->id;
            $existenceId = Arr::get($indicator, 'existing', false);
            $parentIdentifier = Arr::get($indicator, 'parentIdentifier', false);
            $code = Arr::get($indicator, 'code', false);
            $resultId = $identifiers['result'][$parentIdentifier];
            $result = $this->resultRepository->find($resultId);
            $activityId = $result->activity_id;
            $this->storeImportErrors($activityId, $indicator['errors'], "result > $parentIdentifier > indicator > $code");
            $deprecationStatusMap = refreshIndicatorDeprecationStatusMap($indicatorData);

            if ($existenceId) {
                $this->indicatorRepository->update($existenceId, ['indicator' => $indicatorData, 'deprecation_status_map' => $deprecationStatusMap]);
            } else {
                $this->indicatorRepository->store(['indicator' => $indicatorData, 'indicator_code' => $code, 'result_id' => $resultId, 'deprecation_status_map' => $deprecationStatusMap]);
            }
        }
    }

    /**
     * Save period of mapped activity in database.
     *
     * @param $periods
     *
     * @return $this
     */
    protected function savePeriod($periods): void
    {
        $organizationId = Auth::user()->organization->id;
        $userId = Auth::user()->id;
        $identifiers = $this->dbIatiIdentifiers($organizationId, 'indicator');
        $contents = json_decode(awsGetFile(sprintf('%s/%s/%s/%s', $this->xls_data_storage_path, $organizationId, $userId, 'valid.json')), false, 512, 0);

        foreach ($periods as $value) {
            $period = unsetErrorFields($contents[$value]);
            $periodData = Arr::get($period, 'data', []);
            $organizationId = Auth::user()->organization->id;
            $existenceId = Arr::get($period, 'existing', false);
            $parentIdentifier = Arr::get($period, 'parentIdentifier', false);
            $code = Arr::get($period, 'code', false);
            $indicatorId = $identifiers['indicator'][$parentIdentifier];
            $indicator = $this->indicatorRepository->find($indicatorId);
            $activityId = $indicator->result->activity_id;
            $this->storeImportErrors($activityId, $period['errors'], "indicator > $parentIdentifier > period > $code}");
            $deprecationStatusMap = refreshPeriodDeprecationStatusMap($periodData);

            if ($existenceId) {
                $this->periodRepository->update($existenceId, ['period' => $periodData, 'deprecation_status_map'=>$deprecationStatusMap]);
            } else {
                $this->periodRepository->store(['period' => $periodData, 'period_code' => $code, 'indicator_id' => $identifiers['indicator'][$parentIdentifier], 'deprecation_status_map'=>$deprecationStatusMap]);
            }
        }
    }

    /**
     * Store import errors.
     *
     * @param $activityId
     * @param $newErrors
     * @param $fieldName
     *
     * @return void
     */
    protected function storeImportErrors($activityId, $newErrors, $fieldName): void
    {
        $activityError = $this->importActivityErrorRepo->exists('activity_id', $activityId) ? $this->importActivityErrorRepo->findBy('activity_id', $activityId)->toArray() : [];
        $errorTypes = [
            'error',
            'warning',
        ];

        if (!empty($newErrors)) {
            foreach ($errorTypes as $type) {
                if (isset($newErrors[$type])) {
                    foreach ($newErrors[$type] as $index => $error) {
                        foreach ($error as $field => $message) {
                            $activityError['error'][$type][$fieldName][$field] = $message;
                        }
                    }
                }
            }

            $this->importActivityErrorRepo->updateOrCreateError($activityId, $activityError['error']);
        }
    }

    /**
     * Start import by updating status file.
     *
     * @param $filename
     * @param $userId
     * @param $orgId
     * @param $xlsType
     *
     * @return void
     * @throws \JsonException
     */
    public function startImport($filename, $userId, $orgId, $xlsType): void
    {
        awsDeleteDirectory(sprintf('%s/%s/%s', $this->xls_data_storage_path, $orgId, $userId));
        awsUploadFile(sprintf('%s/%s/%s/%s', $this->xls_data_storage_path, $orgId, $userId, 'status.json'), json_encode(['success' => true, 'message' => 'Started'], JSON_THROW_ON_ERROR));
        $this->importStatusRepo->storeStatus($orgId, $userId, 'xls', $xlsType);

        $this->fireXmlUploadEvent($filename, $userId, $orgId, $xlsType);
    }

    /**
     * Fire the XlsWasUploaded event.
     *
     * @param $filename
     * @param $userId
     * @param $organizationId
     * @param $xlsType
     *
     * @return void
     */
    protected function fireXmlUploadEvent($filename, $userId, $organizationId, $xlsType): void
    {
        $iatiIdentifiers = $this->dbIatiIdentifiers($organizationId, $xlsType);
        $reporting_org = Auth::user()->organization->reporting_org;

        Event::dispatch(new XlsWasUploaded($filename, $userId, $organizationId, $reporting_org, $iatiIdentifiers, $xlsType));
    }

    /**
     * Returns array of iati identifiers and codes present in the activities, result, indicator and period of the organisation.
     *
     * @param $org_id
     * @param $xlsType
     *
     * @return array
     */
    public function dbIatiIdentifiers($org_id, $type): array
    {
        $identifier = [];

        switch ($type) {
            case 'activity':
                $identifier = $this->getActivityIdentifier($org_id, $type);
                break;
            case 'result':
                $identifier['parent'] = $this->getActivityIdentifier($org_id, $type);
                $identifier['result'] = $this->getResultIdentifier($org_id, $type);
                break;
            case 'indicator':
                $identifier['parent'] = $this->getResultIdentifier($org_id, $type);
                $identifier['indicator'] = $this->getIndicatorIdentifier($org_id, $type);
                break;
            case 'period':
                $identifier['parent'] = $this->getIndicatorIdentifier($org_id, $type);
                $identifier['period'] = $this->getPeriodIdentifier($org_id, $type);
                break;
        }

        return $identifier;
    }

    /**
     * Return activity identifier.
     *
     * @param $org_id
     * @param $type
     *
     * @return array
     */
    public function getActivityIdentifier($org_id, $type): array
    {
        $identifier = [];
        $activities = $this->activityRepository->getActivityIdentifiers($org_id);

        foreach ($activities as $activity) {
            $identifier[$activity->identifier] = $activity->id;
        }

        return $identifier;
    }

    /**
     * Return result identifier.
     *
     * @param $org_id
     * @param $type
     *
     * @return array
     */
    public function getResultIdentifier($org_id, $type): array
    {
        $identifier = [];
        $activities = $this->activityRepository->getActivityIdentifiers($org_id);

        foreach ($activities as $activity) {
            $results = $activity->results;

            foreach ($results as $result) {
                $identifier[sprintf('%s_%s', $activity->identifier, $result->result_code)] = $result->id;
            }
        }

        return $identifier;
    }

    /**
     * Return indicator identifier.
     *
     * @param $org_id
     * @param $type
     *
     * @return array
     */
    public function getIndicatorIdentifier($org_id, $type): array
    {
        $identifier = [];
        $activities = $this->activityRepository->getActivityIdentifiers($org_id);

        foreach ($activities as $activity) {
            $results = $activity->results;

            foreach ($results as $result) {
                $indicators = $result->indicators;

                foreach ($indicators as $indicator) {
                    $identifier[sprintf('%s_%s_%s', $activity->identifier, $result->result_code, $indicator->indicator_code)] = $indicator->id;
                }
            }
        }

        return $identifier;
    }

    /**
     * Return period identifier.
     *
     * @param $org_id
     * @param $type
     *
     * @return array
     */
    public function getPeriodIdentifier($org_id, $type): array
    {
        $identifier = [];
        $activities = $this->activityRepository->getActivityIdentifiers($org_id);

        foreach ($activities as $activity) {
            $results = $activity->results;

            foreach ($results as $result) {
                $indicators = $result->indicators;

                foreach ($indicators as $indicator) {
                    $periods = $indicator->periods;

                    foreach ($periods as $period) {
                        $identifier[sprintf('%s_%s_%s_%s', $activity->identifier, $result->result_code, $indicator->indicator_code, $period->period_code)] = $period->id;
                    }
                }
            }
        }

        return $identifier;
    }

    /**
     * Returns import status.
     *
     * @return array
     */
    public function getImportStatus(): array
    {
        return $this->importStatusRepo->getImportStatus(Auth::user()->organization->id, Auth::user()->id);
    }

    /**
     * Deletes import status.
     *
     * @return bool
     */
    public function deleteImportStatus(): bool
    {
        return $this->importStatusRepo->deleteImportStatus(Auth::user()->organization->id, Auth::user()->id);
    }

    /**
     * Returns data from aws file with $filename.
     *
     * @param $filename
     *
     * @return object|array
     * @throws \JsonException
     */
    public function getAwsXlsData($filename): object|array
    {
        $contents = awsGetFile(sprintf('%s/%s/%s/%s', $this->xls_data_storage_path, Auth::user()->organization_id, Auth::user()->id, $filename));

        if ($contents) {
            return json_decode($contents, false, 512, JSON_THROW_ON_ERROR);
        }

        return [];
    }
}
