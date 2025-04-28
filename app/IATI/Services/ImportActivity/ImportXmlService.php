<?php

declare(strict_types=1);

namespace App\IATI\Services\ImportActivity;

use App\IATI\Models\Activity\Activity;
use App\IATI\Repositories\Activity\ActivityRepository;
use App\IATI\Repositories\Activity\IndicatorRepository;
use App\IATI\Repositories\Activity\PeriodRepository;
use App\IATI\Repositories\Activity\ResultRepository;
use App\IATI\Repositories\Activity\TransactionRepository;
use App\IATI\Repositories\Import\ImportActivityErrorRepository;
use App\IATI\Services\ElementCompleteService;
use App\IATI\Traits\FillDefaultValuesTrait;
use App\XmlImporter\Events\XmlWasUploaded;
use App\XmlImporter\Foundation\Support\Providers\XmlServiceProvider;
use App\XmlImporter\Foundation\XmlProcessor;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class XmlImportManager.
 */
class ImportXmlService
{
    use FillDefaultValuesTrait;

    /**
     * Temporary Xml file storage location.
     */
    public string $xml_file_storage_path;

    /**
     * Temporary Xml data storage location.
     */
    public string $xml_data_storage_path;

    /**
     * Temporary Csv data storage location.
     */
    public string $csv_data_storage_path;

    /**
     * @var XmlServiceProvider
     */
    protected XmlServiceProvider $xmlServiceProvider;

    /**
     * @var XmlProcessor
     */
    protected XmlProcessor $xmlProcessor;

    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * @var Filesystem
     */
    protected Filesystem $filesystem;

    /**
     * @var XmlService
     */
    protected XmlService $xmlService;

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
     * @var ElementCompleteService
     */
    protected ElementCompleteService $elementCompleteService;

    /**
     * XmlImportManager constructor.
     *
     * @param XmlServiceProvider            $xmlServiceProvider
     * @param ActivityRepository            $activityRepository
     * @param TransactionRepository         $transactionRepository
     * @param ResultRepository              $resultRepository
     * @param PeriodRepository              $periodRepository
     * @param IndicatorRepository           $indicatorRepository
     * @param ImportActivityErrorRepository $importActivityErrorRepo
     * @param XmlProcessor                  $xmlProcessor
     * @param LoggerInterface               $logger
     * @param Filesystem                    $filesystem
     * @param XmlService                    $xmlService
     * @param ElementCompleteService        $elementCompleteService
     */
    public function __construct(
        XmlServiceProvider $xmlServiceProvider,
        ActivityRepository $activityRepository,
        TransactionRepository $transactionRepository,
        ResultRepository $resultRepository,
        PeriodRepository $periodRepository,
        IndicatorRepository $indicatorRepository,
        ImportActivityErrorRepository $importActivityErrorRepo,
        XmlProcessor $xmlProcessor,
        LoggerInterface $logger,
        Filesystem $filesystem,
        XmlService $xmlService,
        ElementCompleteService $elementCompleteService,
    ) {
        $this->xmlServiceProvider = $xmlServiceProvider;
        $this->xmlProcessor = $xmlProcessor;
        $this->logger = $logger;
        $this->filesystem = $filesystem;
        $this->xmlService = $xmlService;
        $this->transactionRepository = $transactionRepository;
        $this->activityRepository = $activityRepository;
        $this->resultRepository = $resultRepository;
        $this->indicatorRepository = $indicatorRepository;
        $this->importActivityErrorRepo = $importActivityErrorRepo;
        $this->periodRepository = $periodRepository;
        $this->xml_file_storage_path = config('import.xml_file_storage_path');
        $this->xml_data_storage_path = config('import.xml_data_storage_path');
        $this->elementCompleteService = $elementCompleteService;
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
            awsDeleteDirectory(
                sprintf('%s/%s/%s', $this->xml_file_storage_path, Auth::user()->organization_id, Auth::user()->id)
            );

            return awsUploadFile(
                sprintf(
                    '%s/%s/%s/%s',
                    $this->xml_file_storage_path,
                    Auth::user()->organization_id,
                    Auth::user()->id,
                    $file->getClientOriginalName()
                ),
                $file->getContent()
            );
        } catch (Exception $exception) {
            $this->logger->error(
                sprintf('Error uploading Xml file due to %s', $exception->getMessage()),
                [
                    'trace' => $exception->getTraceAsString(),
                    'user'  => auth()->user()->id,
                ]
            );

            return false;
        }
    }

    public function isExistingActivity(int $orgId, array $activityInfo): bool
    {
        return Arr::get($activityInfo, 'existence', false)
            && $this->activityRepository->getActivityWithIdentifier(
                $orgId,
                Arr::get($activityInfo, 'data.iati_identifier.activity_identifier')
            );
    }

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @throws \JsonException
     */
    public function create($activities): bool
    {
        $contents = $this->loadJsonFile('valid.json');

        $orgId = Auth::user()->organization->id;
        $organizationIdentifier = Auth::user()->organization->identifier;

        $activitiesToUpsert = [];
        $allActivityIdentifiers = [];
        $defaultFieldValuesMappedToActivityIdentifier = [];

        $dateTimeString = now()->toDateTimeString();

        foreach ($activities as $value) {
            $activityInfo = $this->formatActivityInfo($contents[$value]);
            $activityData = Arr::get($activityInfo, 'data', []);
            $defaultFieldValues = Arr::get($activityData, 'default_field_values.0', []);
            $activityIdentifier = Arr::get($activityData, 'iati_identifier.activity_identifier');

            if ($this->isExistingActivity($orgId, $activityInfo)) {
                $activityData = $this->handleExistingActivity($orgId, $organizationIdentifier, $activityData, $dateTimeString);
            } else {
                $activityData = $this->handleNewActivity($orgId, $organizationIdentifier, $activityData, $dateTimeString);
            }

            $activityData['transactions'] = Arr::get($activityInfo, 'data.transactions', []);
            $activityData['result'] = Arr::get($activityInfo, 'data.result', []);
            $activityData['errors'] = Arr::get($activityInfo, 'data.errors', []);
            $activityData['upload_medium'] = 'xml';
            $activityData['status'] = 'draft';
            $activityData['element_status'] = Arr::get($activityInfo, 'data.element_status', []);
            $activityData['complete_percentage'] = Arr::get($activityInfo, 'data.complete_percentage', 0.0);
            $activityData['deprecation_status_map'] = Arr::get($activityInfo, 'data.deprecation_status_map', []);

            $allActivityIdentifiers[] = $activityIdentifier;
            $activitiesToUpsert[$activityIdentifier] = $activityData;
            $defaultFieldValuesMappedToActivityIdentifier[$activityIdentifier] = $defaultFieldValues;
        }

        return $this->performAllDatabaseWriteOperations($orgId, $activitiesToUpsert, $allActivityIdentifiers, $defaultFieldValuesMappedToActivityIdentifier);
    }

    /**
     * @throws \JsonException
     */
    private function formatActivityInfo(array|\stdClass $activityInfo): array
    {
        $activityInfo = unsetErrorFields($activityInfo);
        $activityInfo['data'] = unsetDeprecatedFieldValues(Arr::get($activityInfo, 'data', []));

        return $activityInfo;
    }

    /**
     * Save transaction of mapped activity in database.
     *
     * @param $transactions
     * @param $activityId
     * @param $defaultValues
     *
     * @return $this
     */
    protected function saveTransactions($transactions, $activityId, $defaultValues): static
    {
        $transactionList = [];

        if ($transactions) {
            $transactions = $this->populateDefaultFields($transactions, $defaultValues);

            foreach ($transactions as $transaction) {
                $transactionList[] = [
                    'activity_id'            => $activityId,
                    'transaction'            => json_encode($transaction),
                    'deprecation_status_map' => json_encode(refreshTransactionDeprecationStatusMap($transaction)),
                ];
            }

            $this->transactionRepository->upsert($transactionList, 'id');
        }

        return $this;
    }

    /**
     * Save result of mapped activity in database.
     *
     * @param $results
     * @param $activityId
     * @param $defaultValues
     *
     * @return $this
     */
    protected function saveResults($results, $activityId, $defaultValues): static
    {
        if ($results) {
            $resultWithoutIndicator = [];

            foreach ($results as $result) {
                $result = (array) $result;
                $indicators = Arr::get($result, 'indicator', []);
                unset($result['indicator']);

                if (!empty($indicators)) {
                    $savedResult = $this->resultRepository->store([
                        'activity_id'            => $activityId,
                        'result'                 => $result,
                        'default_field_values'   => $defaultValues,
                        'deprecation_status_map' => refreshResultDeprecationStatusMap($result),
                    ]);

                    foreach ($indicators as $indicator) {
                        $indicator = (array) $indicator;
                        $periods = Arr::get($indicator, 'period', []);
                        $tempPeriod = [];
                        unset($indicator['period']);

                        $savedIndicator = $this->indicatorRepository->store([
                            'result_id'              => $savedResult['id'],
                            'indicator'              => $indicator,
                            'default_field_values'   => $defaultValues,
                            'deprecation_status_map' => refreshIndicatorDeprecationStatusMap($indicator),
                        ]);

                        if (!empty($periods)) {
                            foreach ($periods as $period) {
                                $tempPeriod[] = [
                                    'period'                 => $period,
                                    'deprecation_status_map' => refreshPeriodDeprecationStatusMap($period),
                                ];
                            }

                            $savedIndicator->periods()->createMany($tempPeriod);
                        }
                    }
                } else {
                    $resultWithoutIndicator[] = [
                        'activity_id'            => $activityId,
                        'result'                 => json_encode($result),
                        'deprecation_status_map' => json_encode(refreshResultDeprecationStatusMap($result)),
                    ];
                }
            }

            $this->resultRepository->upsert($resultWithoutIndicator, 'id');
        }

        return $this;
    }

    /**
     * @param $filename
     * @param $userId
     * @param $orgId
     *
     * @return void
     * @throws \JsonException
     */
    public function startImport($filename, $userId, $orgId): void
    {
        awsDeleteDirectory(sprintf('%s/%s/%s', $this->xml_data_storage_path, $orgId, $userId));
        awsUploadFile(
            sprintf('%s/%s/%s/%s', $this->xml_data_storage_path, $orgId, $userId, 'status.json'),
            json_encode(['success' => true, 'message' => 'Started'], JSON_THROW_ON_ERROR)
        );

        $this->fireXmlUploadEvent($filename, $userId, $orgId);
    }

    /**
     * Fire the XmlWasUploaded event.
     *
     * @param $filename
     * @param $userId
     * @param $organizationId
     *
     * @return void
     */
    protected function fireXmlUploadEvent($filename, $userId, $organizationId): void
    {
        $iatiIdentifiers = $this->dbIatiIdentifiers($organizationId);
        $orgRef = Auth::user()->organization->identifier;

        Event::dispatch(new XmlWasUploaded($filename, $userId, $organizationId, $orgRef, $iatiIdentifiers));
    }

    /**
     * Load a json file with a specific filename.
     *
     * @param $filename
     *
     * @return mixed|null
     */
    public function loadJsonFile($filename): mixed
    {
        try {
            $filePath = sprintf('%s/%s/%s/%s', $this->xml_data_storage_path, Auth::user()->organization_id, Auth::user()->id, $filename);

            $contents = awsGetFile($filePath);

            if ($contents) {
                return json_decode($contents, false, 512, JSON_THROW_ON_ERROR);
            }

            return null;
        } catch (Exception $exception) {
            $this->logger->error(
                sprintf('Error due to %s', $exception->getMessage()),
                [
                    'trace'    => $exception->getTraceAsString(),
                    'user_id'  => auth()->user()->id,
                    'filename' => $filename,
                ]
            );

            return null;
        }
    }

    /**
     * Returns array of iati identifiers present in the activities of the organisation.
     *
     * @param $org_id
     *
     * @return array
     */
    protected function dbIatiIdentifiers($org_id): array
    {
        return Arr::flatten($this->activityRepository->getActivityIdentifiers($org_id)->toArray());
    }

    /**
     * Since we are doing upsert on results and transaction for both creation and update,
     * We need to manually check if result and transaction is complete.
     *
     * @throws \JsonException
     */
    private function refreshActivityElementStatusForResultAndTransaction(Activity $activity): void
    {
        $elementStatus = $activity->element_status;
        $resultStatus = $this->elementCompleteService->isResultElementCompleted($activity);
        $transactionsStatus = $this->elementCompleteService->isTransactionsElementCompleted($activity);
        $elementStatus['result'] = $resultStatus;
        $elementStatus['transactions'] = $transactionsStatus;

        $this->activityRepository->update($activity->id, ['element_status' => $elementStatus]);
    }

    /**
     * @param int    $orgId
     * @param string $organizationIdentifier
     * @param array  $activityData
     * @param string $dateTimeString
     *
     * @return array
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    private function handleExistingActivity(int $orgId, string $organizationIdentifier, array $activityData, string $dateTimeString): array
    {
        $oldActivity = $this->activityRepository->getActivityWithIdentifier($orgId, Arr::get($activityData, 'iati_identifier.activity_identifier'));

        if ($oldActivity['has_ever_been_published']) {
            $activityData['iati_identifier']['iati_identifier_text'] = $oldActivity['iati_identifier']['iati_identifier_text'];
            $activityData['iati_identifier']['present_organization_identifier'] = $oldActivity['iati_identifier']['present_organization_identifier'];
            $activityData['linked_to_iati'] = $oldActivity['linked_to_iati'] ?? false;
            $activityData['has_ever_been_published'] = true;
        } else {
            $activityData['iati_identifier']['iati_identifier_text'] = $organizationIdentifier . '-' . Arr::get($activityData, 'identifier.activity_identifier');
            $activityData['iati_identifier']['present_organization_identifier'] = $organizationIdentifier;
            $activityData['linked_to_iati'] = false;
            $activityData['has_ever_been_published'] = false;
        }

        $activityData['created_at'] = $oldActivity['created_at'];
        $activityData['updated_at'] = $dateTimeString;
        $activityData['created_by'] = $oldActivity['created_by'];
        $activityData['updated_by'] = Auth::user()->id;

        $activityData = $this->activityRepository->formatActivityDataForXmlImport($orgId, $activityData);

        return trimStringValueInArray($activityData);
    }

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function handleNewActivity(int $orgId, string $organizationIdentifier, array $activityData, string $dateTimeString): array
    {
        $activityData['iati_identifier']['iati_identifier_text'] = $organizationIdentifier . '-' . $activityData['iati_identifier']['activity_identifier'];
        $activityData['iati_identifier']['present_organization_identifier'] = $organizationIdentifier;

        $activityData['created_at'] = $dateTimeString;
        $activityData['updated_at'] = $dateTimeString;
        $activityData['created_by'] = Auth::user()->id;
        $activityData['updated_by'] = Auth::user()->id;
        $activityData['linked_to_iati'] = false;
        $activityData['has_ever_been_published'] = false;

        $activityData = $this->activityRepository->formatActivityDataForXmlImport($orgId, $activityData);

        return trimStringValueInArray($activityData);
    }

    /**
     * @throws \JsonException
     */
    private function performAllDatabaseWriteOperations(int $orgId, array $activitiesToUpsert, array $allActivityIdentifiers, array $defaultFieldValuesMappedToActivityIdentifier): bool
    {
        $importActivityErrorsToInsert = [];

        /** Save all activities, all at once. */
        $activityDataForUpsert = $this->activityRepository->prepareAllActivityDataToUpsert($orgId, $activitiesToUpsert);
        $this->activityRepository->createOrUpdateActivities($orgId, $activityDataForUpsert);

        $activityIdsMapped = $this->activityRepository->getActivityIdsByIdentifier($orgId, $allActivityIdentifiers);
        $storedActivityIds = array_values($activityIdsMapped);

        /* Delete all transactions and create new transactions, all at once.*/
        $this->transactionRepository->bulkDeleteTransactionsByActivityIds($storedActivityIds);
        $this->transactionRepository->createTransactions(
            $activitiesToUpsert,
            $activityIdsMapped,
            $defaultFieldValuesMappedToActivityIdentifier
        );

        /* Delete all results and then create result->indicator->period, for each activity. */
        $this->resultRepository->bulkDeleteResultsByActivityIds($storedActivityIds);

        foreach ($activitiesToUpsert as $activityData) {
            $activityIdentifier = Arr::get($activityData, 'iati_identifier.activity_identifier');
            $activityId = Arr::get($activityIdsMapped, $activityIdentifier);

            $this->saveResults(
                Arr::get($activityData, 'result', []),
                $activityId,
                Arr::get($defaultFieldValuesMappedToActivityIdentifier, $activityIdentifier, [])
            );

            /* Prepare data for either deleting OR update/insert import_activity_error record */
            if (!empty($activityData['errors'])) {
                $importActivityErrorsToInsert[] = [
                    'activity_id' => $activityId,
                    'error'       => json_encode($activityData['errors'], JSON_THROW_ON_ERROR),
                ];
            }
        }

        /* Delete some and upsert some import_activity_error. */
        $this->importActivityErrorRepo->deleteByActivityIds(array_values($activityIdsMapped));

        return $this->importActivityErrorRepo->updateOrCreateErrorByActivityIds($importActivityErrorsToInsert);
    }

    /**
     * @param Collection<\App\IATI\Models\Activity\Activity> $activities
     *
     * @return array
     * @throws \JsonException
     */
    private function prepareActivityDataWithElementStatus(Collection $activities): array
    {
        return $this->elementCompleteService->refreshElementStatusForMultipleActivities($activities);
    }
}
