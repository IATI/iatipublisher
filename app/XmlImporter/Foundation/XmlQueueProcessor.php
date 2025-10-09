<?php

declare(strict_types=1);

namespace App\XmlImporter\Foundation;

use App\Exceptions\InvalidTag;
use App\IATI\Repositories\Activity\ActivityRepository;
use App\IATI\Repositories\Import\ImportStatusRepository;
use App\XmlImporter\Foundation\Support\Providers\XmlServiceProvider;
use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\DatabaseManager;
use Psr\Log\LoggerInterface;
use Sabre\Xml\ParseException;

/**
 * Class XmlQueueProcessor.
 */
class XmlQueueProcessor
{
    /**
     * @var XmlServiceProvider
     */
    protected XmlServiceProvider $xmlServiceProvider;

    /**
     * @var XmlProcessor
     */
    protected XmlProcessor $xmlProcessor;

    /**
     * @var
     */
    protected $userId;

    /**
     * @var
     */
    protected $orgId;

    /**
     * @var
     */
    protected $orgRef;

    /**
     * @var
     */
    protected $filename;

    /**
     * @var ActivityRepository
     */
    protected ActivityRepository $activityRepo;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var DatabaseManager
     */
    private DatabaseManager $databaseManager;

    /**
     * @var string
     */
    private string $xml_file_storage_path;

    /**
     * @var string
     */
    private string                 $xml_data_storage_path;

    private ImportStatusRepository $importStatusRepository;

    /**
     * XmlQueueProcessor constructor.
     *
     * @param XmlServiceProvider     $xmlServiceProvider
     * @param XmlProcessor           $xmlProcessor
     * @param ActivityRepository     $activityRepo
     * @param DatabaseManager        $databaseManager
     * @param ImportStatusRepository $importStatusRepository
     */
    public function __construct(
        XmlServiceProvider $xmlServiceProvider,
        XmlProcessor $xmlProcessor,
        ActivityRepository $activityRepo,
        DatabaseManager $databaseManager,
        ImportStatusRepository $importStatusRepository
    ) {
        $this->xmlServiceProvider = $xmlServiceProvider;
        $this->xmlProcessor = $xmlProcessor;
        $this->activityRepo = $activityRepo;
        $this->databaseManager = $databaseManager;
        $this->importStatusRepository = $importStatusRepository;
        $this->xml_file_storage_path = config('import.xml_file_storage_path');
        $this->xml_data_storage_path = config('import.xml_data_storage_path');
    }

    /**
     * Import the Xml data.
     *
     * @param $filename
     * @param $orgId
     * @param $orgRef
     * @param $authUser
     * @param $dbIatiIdentifiers
     * @param $organizationReportingOrg
     *
     * @return bool
     * @throws InvalidTag
     * @throws BindingResolutionException
     * @throws \JsonException
     * @throws ParseException
     * @throws \Throwable
     */
    public function import($filename, $orgId, $orgRef, $authUser, $dbIatiIdentifiers, $organizationReportingOrg): bool
    {
        $importStartTime = microtime(true);
        logger()->info('XML Import started', [
            'filename' => $filename,
            'org_id'   => $orgId,
            'user_id'  => $authUser->id,
        ]);

        try {
            $userId = $authUser->id;
            $this->orgId = $orgId;
            $this->orgRef = $orgRef;
            $this->userId = $userId;
            $this->filename = $filename;

            $fetchStartTime = microtime(true);
            $contents = awsGetFile(
                sprintf('%s/%s/%s/%s', $this->xml_file_storage_path, $this->orgId, $this->userId, $filename)
            );
            $fetchDuration = microtime(true) - $fetchStartTime;
            logger()->info('AWS file fetch completed', [
                'duration_seconds' => round($fetchDuration, 2),
                'file_size_bytes'  => strlen($contents),
                'file_size_mb'     => round(strlen($contents) / 1024 / 1024, 2),
            ]);

            $statusUploadStart = microtime(true);
            awsUploadFile(
                sprintf('%s/%s/%s/%s', $this->xml_data_storage_path, $this->orgId, $this->userId, 'status.json'),
                json_encode(['success' => true, 'message' => 'Processing'], JSON_THROW_ON_ERROR)
            );
            $statusUploadDuration = microtime(true) - $statusUploadStart;
            logger()->info('Status file uploaded', [
                'duration_seconds' => round($statusUploadDuration, 2),
            ]);

            // Validate XML against schema
            $validationStartTime = microtime(true);
            $isValid = $this->xmlServiceProvider->isValidAgainstSchema($contents);
            $validationDuration = microtime(true) - $validationStartTime;
            logger()->info('XML schema validation completed', [
                'duration_seconds' => round($validationDuration, 2),
                'is_valid'         => $isValid,
            ]);

            if ($isValid) {
                // Load/Parse XML
                $loadStartTime = microtime(true);
                $xmlData = $this->xmlServiceProvider->load($contents);
                $loadDuration = microtime(true) - $loadStartTime;
                logger()->info('XML parsing completed', [
                    'duration_seconds' => round($loadDuration, 2),
                ]);

                // Process XML data
                $processStartTime = microtime(true);
                $this->xmlProcessor->process(
                    $xmlData,
                    $authUser,
                    $orgId,
                    $orgRef,
                    $dbIatiIdentifiers,
                    $organizationReportingOrg
                );
                $processDuration = microtime(true) - $processStartTime;
                logger()->info('XML processing completed', [
                    'duration_seconds' => round($processDuration, 2),
                    'duration_minutes' => round($processDuration / 60, 2),
                ]);

                // Upload completion status
                $completionUploadStart = microtime(true);
                awsUploadFile(
                    sprintf('%s/%s/%s/%s', $this->xml_data_storage_path, $this->orgId, $this->userId, 'status.json'),
                    json_encode(['success' => true, 'message' => 'Complete'], JSON_THROW_ON_ERROR)
                );
                $completionUploadDuration = microtime(true) - $completionUploadStart;
                logger()->info('Completion status uploaded', [
                    'duration_seconds' => round($completionUploadDuration, 2),
                ]);

                $totalDuration = microtime(true) - $importStartTime;
                logger()->info('XML Import completed successfully', [
                    'total_duration_seconds' => round($totalDuration, 2),
                    'total_duration_minutes' => round($totalDuration / 60, 2),
                    'breakdown'              => [
                        'fetch_seconds'         => round($fetchDuration, 2),
                        'validation_seconds'    => round($validationDuration, 2),
                        'parsing_seconds'       => round($loadDuration, 2),
                        'processing_seconds'    => round($processDuration, 2),
                        'status_upload_seconds' => round($statusUploadDuration + $completionUploadDuration, 2),
                    ],
                    'percentage_breakdown'   => [
                        'fetch_percent'      => round(($fetchDuration / $totalDuration) * 100, 1),
                        'validation_percent' => round(($validationDuration / $totalDuration) * 100, 1),
                        'parsing_percent'    => round(($loadDuration / $totalDuration) * 100, 1),
                        'processing_percent' => round(($processDuration / $totalDuration) * 100, 1),
                    ],
                ]);

                return true;
            }

            // Handle schema validation failure
            $errorLogStart = microtime(true);
            awsUploadFile(
                sprintf('%s/%s/%s/%s', $this->xml_data_storage_path, $this->orgId, $this->userId, 'schema_error.log'),
                json_encode(libxml_get_errors(), JSON_THROW_ON_ERROR)
            );

            awsUploadFile(
                sprintf('%s/%s/%s/%s', $this->xml_data_storage_path, $orgId, $userId, 'status.json'),
                json_encode(
                    [
                        'success'    => false,
                        'message'    => 'Invalid XML or Header mismatched',
                        'error_type' => 'header_mismatch',
                    ],
                    JSON_THROW_ON_ERROR
                )
            );
            $errorLogDuration = microtime(true) - $errorLogStart;

            $this->databaseManager->rollback();

            $totalDuration = microtime(true) - $importStartTime;
            logger()->warning('XML Import failed - Schema validation error', [
                'total_duration_seconds' => round($totalDuration, 2),
                'error_logging_seconds'  => round($errorLogDuration, 2),
            ]);

            return false;
        } catch (InvalidTag $e) {
            $totalDuration = microtime(true) - $importStartTime;
            logger()->error('XML Import failed - InvalidTag exception', [
                'exception'        => $e->getMessage(),
                'duration_seconds' => round($totalDuration, 2),
                'filename'         => $filename,
            ]);
            logger()->error($e);

            awsUploadFile(
                sprintf('%s/%s/%s/%s', $this->xml_data_storage_path, $orgId, $userId, 'status.json'),
                json_encode(['success' => false, 'message' => $e->getMessage()], JSON_THROW_ON_ERROR)
            );

            throw $e;
        } catch (Exception $e) {
            $totalDuration = microtime(true) - $importStartTime;
            logger()->error('XML Import failed - General exception', [
                'exception'        => $e->getMessage(),
                'exception_class'  => get_class($e),
                'duration_seconds' => round($totalDuration, 2),
                'filename'         => $filename,
                'trace'            => $e->getTraceAsString(),
            ]);
            logger()->error($e);

            $cleanupStart = microtime(true);
            $this->importStatusRepository->deleteOngoingImports($orgId);
            $cleanupDuration = microtime(true) - $cleanupStart;
            logger()->info('Import cleanup completed', [
                'duration_seconds' => round($cleanupDuration, 2),
            ]);

            awsUploadFile(
                sprintf('%s/%s/%s/%s', $this->xml_data_storage_path, $orgId, $userId, 'status.json'),
                json_encode(
                    [
                        'success' => false,
                        'message' => trans('common/common.error_has_occurred_while_importing_the_file'),
                    ],
                    JSON_THROW_ON_ERROR
                )
            );

            throw  $e;
        }
    }
}
