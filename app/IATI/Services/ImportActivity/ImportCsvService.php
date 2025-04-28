<?php

declare(strict_types=1);

namespace App\IATI\Services\ImportActivity;

use App\CsvImporter\Events\ActivityCsvWasUploaded;
use App\CsvImporter\Queue\Processor;
use App\IATI\Repositories\Activity\ActivityRepository;
use App\IATI\Repositories\Activity\TransactionRepository;
use App\IATI\Repositories\Import\ImportActivityErrorRepository;
use App\IATI\Repositories\Organization\OrganizationRepository;
use App\IATI\Services\ElementCompleteService;
use App\IATI\Traits\FillDefaultValuesTrait;
use App\Imports\CsvToArray;
use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Session\SessionManager;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Excel;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class ImportManager.
 */
class ImportCsvService
{
    use FillDefaultValuesTrait;

    /**
     * Directory where the validated Csv data is written before import.
     */
    public string $csv_data_storage_path;

    /**
     * File in which the valida Csv data is written before import.
     */
    public const VALID_CSV_FILE = 'valid.json';

    /**
     * Directory where the uploaded Csv file is stored temporarily before import.
     */
    public string $csv_file_storage_path;

    /**
     * Default encoding for csv file.
     */
    public const DEFAULT_ENCODING = 'UTF-8';

    /**
     * @var Excel
     */
    protected Excel $excel;

    /**
     * @var Processor
     */
    protected Processor $processor;

    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * @var SessionManager
     */
    protected SessionManager $sessionManager;

    /**
     * @var ActivityRepository
     */
    protected ActivityRepository $activityRepo;

    /**
     * @var OrganizationRepository
     */
    protected OrganizationRepository $organizationRepo;

    /**
     * @var TransactionRepository
     */
    protected TransactionRepository $transactionRepo;

    /**
     * @var ImportActivityErrorRepository
     */
    protected ImportActivityErrorRepository $importActivityErrorRepo;

    /**
     * @var Filesystem
     */
    protected Filesystem $filesystem;

    /**
     * @var ElementCompleteService
     */
    private ElementCompleteService $elementCompleteService;

    /**
     * ImportManager constructor.
     *
     * @param Excel                  $excel
     * @param Processor              $processor
     * @param LoggerInterface        $logger
     * @param SessionManager         $sessionManager
     * @param ActivityRepository     $activityRepo
     * @param OrganizationRepository $organizationRepo
     * @param TransactionRepository  $transactionRepo
     * @param ImportActivityErrorRepository  $importActivityErrorRepo
     * @param Filesystem             $filesystem
     */
    public function __construct(
        Excel $excel,
        Processor $processor,
        LoggerInterface $logger,
        SessionManager $sessionManager,
        ActivityRepository $activityRepo,
        OrganizationRepository $organizationRepo,
        TransactionRepository $transactionRepo,
        ImportActivityErrorRepository $importActivityErrorRepo,
        Filesystem $filesystem,
        ElementCompleteService $elementCompleteService
    ) {
        $this->excel = $excel;
        $this->processor = $processor;
        $this->logger = $logger;
        $this->sessionManager = $sessionManager;
        $this->activityRepo = $activityRepo;
        $this->organizationRepo = $organizationRepo;
        $this->transactionRepo = $transactionRepo;
        $this->importActivityErrorRepo = $importActivityErrorRepo;
        $this->filesystem = $filesystem;
        $this->elementCompleteService = $elementCompleteService;
        $this->csv_data_storage_path = config('import.csv_data_storage_path');
        $this->csv_file_storage_path = config('import.csv_file_storage_path');
    }

    /**
     * Process the uploaded CSV file.
     *
     * @param $filename
     *
     * @return void
     */
    public function process($filename): void
    {
        try {
            $uploadedFile = awsGetFile(sprintf('%s/%s/%s/%s', $this->csv_file_storage_path, Auth::user()->organization->id, Auth::user()->id, $filename));
            awsUploadFile(sprintf('%s/%s/%s/%s', $this->csv_data_storage_path, Auth::user()->organization->id, Auth::user()->id, 'valid.json'), '');
            $localStorageFile = $this->localStorageFile($uploadedFile, $filename);
            Session::put('user_id', Auth::user()->id);
            Session::put('org_id', Auth::user()->organization->id);

            $this->processor->pushIntoQueue($localStorageFile, $filename, $this->getIdentifiers(), Auth::user()->organization->reporting_org);
        } catch (Exception $e) {
            $this->logger->error(
                $e->getMessage(),
                [
                    'user' => auth()->user(),
                    'trace' => $e->getTraceAsString(),
                ]
            );
        }
    }

    /**
     * Returns local storage file.
     *
     * @param $file
     * @param $filename
     *
     * @return File
     */
    public function localStorageFile($file, $filename): File
    {
        $localStorage = Storage::disk('local');
        $localStoragePath = sprintf('%s/%s/%s', config('import.csv_file_local_storage_path'), Auth::user()->organization->id, $filename);
        $localStorage->put($localStoragePath, $file);

        return new File(storage_path(sprintf('%s/%s', 'app', $localStoragePath)));
    }

    /**
     * Create Valid activities.
     *
     * @param      $activities
     *
     * @return void
     *
     * @throws BindingResolutionException
     * @throws \JsonException
     * @throws \ReflectionException
     */
    public function create($activities): void
    {
        $authUser = Auth::user();
        $organizationId = $authUser->organization_id;
        $userId = $authUser->id;
        $file = awsGetFile(sprintf('%s/%s/%s/%s', $this->csv_data_storage_path, $organizationId, $userId, self::VALID_CSV_FILE));
        $contents = json_decode($file, true, 512, JSON_THROW_ON_ERROR);
        $organizationIdentifier = $authUser->organization->identifier;

        foreach ($activities as $value) {
            $activity = unsetErrorFields($contents[$value]);
            $activity['data'] = unsetDeprecatedFieldValues(Arr::get($activity, 'data', []));

            $iati_identifier_text = $organizationIdentifier . '-' . Arr::get($activity, 'data.identifier.activity_identifier');
            $activity['data']['organization_id'] = $organizationId;
            $activity['data']['identifier']['iati_identifier_text'] = $iati_identifier_text;
            $activity['data']['identifier']['present_organization_identifier'] = $organizationIdentifier;

            if (Arr::get($activity, 'existence', false) && $this->activityRepo->getActivityWithIdentifier($organizationId, Arr::get($activity, 'data.identifier.activity_identifier'))) {
                $oldActivity = $this->activityRepo->getActivityWithIdentifier($organizationId, Arr::get($activity, 'data.identifier.activity_identifier'));

                if ($oldActivity['has_ever_been_published']) {
                    $activity['data']['identifier']['iati_identifier_text'] = $oldActivity['iati_identifier']['iati_identifier_text'];
                    $activity['data']['identifier']['present_organization_identifier'] = $oldActivity['iati_identifier']['present_organization_identifier'];
                }

                $this->activityRepo->updateActivity($oldActivity->id, Arr::get($activity, 'data'));
                $this->transactionRepo->deleteTransaction($oldActivity->id);

                if (array_key_exists('transaction', $activity['data'])) {
                    $this->createTransaction(Arr::get($activity['data'], 'transaction', []), $oldActivity->id, Arr::get($activity['data'], 'default_field_values.0', []));
                }

                if (!empty($activity['errors'])) {
                    $this->importActivityErrorRepo->updateOrCreateError($oldActivity->id, $activity['errors']);
                } else {
                    $this->importActivityErrorRepo->deleteImportError($oldActivity->id);
                }

                $this->elementCompleteService->refreshElementStatus(
                    $this->activityRepo->getActivitityWithRelationsById($oldActivity->id)
                );
            } else {
                $createdActivity = $this->activityRepo->createActivity(Arr::get($activity, 'data'));

                if (array_key_exists('transaction', $activity['data'])) {
                    $this->createTransaction(Arr::get($activity['data'], 'transaction', []), $createdActivity->id, Arr::get($activity['data'], 'default_field_values.0', []));
                }

                if (!empty($activity['errors'])) {
                    $this->importActivityErrorRepo->updateOrCreateError($createdActivity->id, $activity['errors']);
                }

                $this->elementCompleteService->refreshElementStatus(
                    $this->activityRepo->getActivitityWithRelationsById($createdActivity->id)
                );
            }
        }
    }

    /**
     * Create Transaction of Valid Activities.
     *
     * @param $transactions
     * @param $activityId
     * @param $defaultValues
     *
     * @return void
     */
    public function createTransaction($transactions, $activityId, $defaultValues): void
    {
        foreach ($transactions as $transaction) {
            $this->transactionRepo->store([
                'transaction' => $transaction,
                'activity_id' => $activityId,
                'default_field_values'=>$defaultValues,
                'deprecation_status_map'=>refreshTransactionDeprecationStatusMap($transaction),
            ]);
        }
    }

    /**
     * Set the key to specify that import process has started for the current User.
     *
     * @param $filename
     *
     * @return $this
     */
    public function startImport($filename): static
    {
        Session::put('import-status', 'Processing');
        Session::put('filename', $filename);

        return $this;
    }

    /**
     * Remove the import-status key from the User's current session.
     *
     * @return void
     */
    public function endImport(): void
    {
        Session::forget('import-status');
        Session::forget('filename');
    }

    /**
     * Get the filepath to the file in which the Csv data is written after processing for import.
     *
     * @param $filename
     *
     * @return string
     */
    public function getFilePath($filename = null): string
    {
        if ($filename) {
            return storage_path(sprintf('%s/%s/%s', $this->csv_data_storage_path, Auth::user()->organization_id, $filename));
        }

        return storage_path(sprintf('%s/%s/%s', $this->csv_data_storage_path, Auth::user()->organization_id, self::VALID_CSV_FILE));
    }

    /**
     * Set import-status key when the processing is complete.
     *
     * @return void
     */
    protected function completeImport(): void
    {
        Session::put('import-status', 'Complete');
    }

    /**
     * Uploads Csv file to bucket before import.
     *
     * @param UploadedFile $file
     *
     * @return bool|null
     */
    public function storeCsv(UploadedFile $file): ?bool
    {
        try {
            return awsUploadFile(sprintf('%s/%s/%s/%s', $this->csv_file_storage_path, Auth::user()->organization_id, Auth::user()->id, str_replace(' ', '', $file->getClientOriginalName())), $file->getContent());
        } catch (Exception $e) {
            $this->logger->error(
                sprintf('Error uploading Activity CSV file due to [%s]', $e->getMessage()),
                [
                    'trace' => $e->getTraceAsString(),
                    'user_id' => Auth::user()->organization_id,
                ]
            );

            return null;
        }
    }

    /**
     * Clear keys from the current session.
     *
     * @param array $keys
     */
    public function clearSession(array $keys): void
    {
        foreach ($keys as $key) {
            Session::forget($key);
        }
    }

    /**
     * Fire Csv Upload event on Csv File upload.
     *
     * @param $filename
     */
    public function fireCsvUploadEvent($filename): void
    {
        Event::dispatch(new ActivityCsvWasUploaded($filename));
    }

    /**
     * Check if an old import is on going.
     *
     * @return bool
     */
    protected function hasOldData(): bool
    {
        return Session::has('import-status') || Session::has('filename');
    }

    /**
     * Clear old import data before another.
     */
    public function clearOldImport(): void
    {
        awsDeleteDirectory(sprintf('%s/%s/%s', $this->csv_data_storage_path, Session::get('org_id'), Session::get('user_id')));
        awsDeleteDirectory(sprintf('%s/%s/%s', $this->csv_file_storage_path, Session::get('org_id'), Session::get('user_id')));

        if ($this->hasOldData()) {
            $this->clearSession(['import-status', 'filename']);
        }
    }

    /**
     * Checks if the file is empty or not.
     *
     * @param $file
     *
     * @return bool
     */
    public function isCsvFileEmpty($file): bool
    {
        return !((($this->excel->toCollection(new CsvToArray, $file)->first()->count() > 1)));
    }

    /**
     * Provides all the activity identifiers.
     *
     * @return array
     */
    protected function getIdentifiers(): array
    {
        $org_id = Auth::user()->organization_id;

        return Arr::flatten($this->activityRepo->getActivityIdentifiers($org_id)->toArray());
    }

    /**
     * Checks if the file is in UTF8Encoding.
     *
     * @param $filename
     *
     * @return bool
     */
    public function isInUTF8Encoding($filename): bool
    {
        $uploadedFile = awsGetFile(sprintf('%s/%s/%s/%s', $this->csv_file_storage_path, Auth::user()->organization->id, Auth::user()->id, $filename));
        $s3 = Storage::disk('local');
        $localPath = sprintf('%s/%s/%s/%s', 'CsvImporter/file', Auth::user()->organization->id, Auth::user()->id, $filename);
        $s3->put($localPath, $uploadedFile);

        $file = new File(storage_path(sprintf('%s/%s', 'app', $localPath)));

        return getEncodingType($file) === self::DEFAULT_ENCODING;
    }

    public function getAwsCsvData($filename)
    {
        try {
            $contents = awsGetFile(sprintf('%s/%s/%s/%s', $this->csv_data_storage_path, Auth::user()->organization_id, Auth::user()->id, $filename));

            if ($contents) {
                return json_decode($contents, false, 512, JSON_THROW_ON_ERROR);
            }

            return false;
        } catch (Exception $exception) {
            $this->logger->error(
                sprintf('Error due to %s', $exception->getMessage()),
                [
                    'trace' => $exception->getTraceAsString(),
                    'user_id' => auth()->user()->id,
                    'filename' => $filename,
                ]
            );

            return null;
        }
    }
}
