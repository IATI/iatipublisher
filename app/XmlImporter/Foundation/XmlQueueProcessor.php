<?php

declare(strict_types=1);

namespace App\XmlImporter\Foundation;

use App\Exceptions\InvalidTag;
use App\IATI\Repositories\Activity\ActivityRepository;
use App\XmlImporter\Foundation\Support\Providers\XmlServiceProvider;
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
    private string $xml_data_storage_path;

    /**
     * XmlQueueProcessor constructor.
     *
     * @param XmlServiceProvider $xmlServiceProvider
     * @param XmlProcessor       $xmlProcessor
     * @param ActivityRepository $activityRepo
     * @param DatabaseManager    $databaseManager
     */
    public function __construct(XmlServiceProvider $xmlServiceProvider, XmlProcessor $xmlProcessor, ActivityRepository $activityRepo, DatabaseManager $databaseManager)
    {
        $this->xmlServiceProvider = $xmlServiceProvider;
        $this->xmlProcessor = $xmlProcessor;
        $this->activityRepo = $activityRepo;
        $this->databaseManager = $databaseManager;
        $this->xml_file_storage_path = env('XML_FILE_STORAGE_PATH ', 'XmlImporter/file');
        $this->xml_data_storage_path = env('XML_DATA_STORAGE_PATH ', 'XmlImporter/tmp');
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
        try {
            $userId = $authUser->id;
            $this->orgId = $orgId;
            $this->orgRef = $orgRef;
            $this->userId = $userId;
            $this->filename = $filename;

            $contents = awsGetFile(sprintf('%s/%s/%s/%s', $this->xml_file_storage_path, $this->orgId, $this->userId, $filename));

            awsUploadFile(sprintf('%s/%s/%s/%s', $this->xml_data_storage_path, $this->orgId, $this->userId, 'status.json'), json_encode(['success' => true, 'message' => 'Processing'], JSON_THROW_ON_ERROR));

            if ($this->xmlServiceProvider->isValidAgainstSchema($contents)) {
                $xmlData = $this->xmlServiceProvider->load($contents);
                $this->xmlProcessor->process($xmlData, $authUser, $orgId, $orgRef, $dbIatiIdentifiers, $organizationReportingOrg);

                awsUploadFile(sprintf('%s/%s/%s/%s', $this->xml_data_storage_path, $this->orgId, $this->userId, 'status.json'), json_encode(['success' => true, 'message' => 'Complete'], JSON_THROW_ON_ERROR));

                return true;
            }

            awsUploadFile(sprintf('%s/%s/%s/%s', $this->xml_data_storage_path, $this->orgId, $this->userId, 'schema_error.log'), json_encode(libxml_get_errors(), JSON_THROW_ON_ERROR));

            awsUploadFile(sprintf('%s/%s/%s/%s', $this->xml_data_storage_path, $orgId, $userId, 'status.json'), json_encode(['success' => false, 'message' => 'Invalid XML or Header mismatched'], JSON_THROW_ON_ERROR));

            $this->databaseManager->rollback();

            return false;
        } catch (InvalidTag $e) {
            logger($e);
            awsUploadFile(sprintf('%s/%s/%s/%s', $this->xml_data_storage_path, $orgId, $userId, 'status.json'), json_encode(['success' => false, 'message' => $e->getMessage()], JSON_THROW_ON_ERROR));

            throw $e;
        } catch (\Exception $e) {
            logger($e);
            awsUploadFile(sprintf('%s/%s/%s/%s', $this->xml_data_storage_path, $orgId, $userId, 'status.json'), json_encode(['success' => false, 'message' => 'Error has occurred while importing the file.'], JSON_THROW_ON_ERROR));

            throw  $e;
        }
    }
}
