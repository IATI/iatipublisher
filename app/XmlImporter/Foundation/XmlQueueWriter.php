<?php

declare(strict_types=1);

namespace App\XmlImporter\Foundation;

use App\IATI\Repositories\Activity\ActivityRepository;
use App\IATI\Repositories\Activity\DocumentLinkRepository;
use App\IATI\Repositories\Activity\ResultRepository;
use App\IATI\Repositories\Activity\TransactionRepository;
use App\XmlImporter\Foundation\Support\Factory\XmlValidator;
use Illuminate\Support\Arr;

/**
 * Class XmlQueueWriter.
 */
class XmlQueueWriter
{
    /**
     * @var ActivityRepository
     */
    protected ActivityRepository $activityRepo;

    /**
     * @var TransactionRepository
     */
    protected TransactionRepository $transactionRepo;

    /**
     * @var ResultRepository
     */
    protected ResultRepository $resultRepo;

    /**
     * @var DocumentLinkRepository
     */
    protected DocumentLinkRepository $documentLinkRepo;

    /**
     *  Path to xml importer.
     */
    public string $xml_data_storage_path;

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
    protected $xmlActivityIdentifiers;

    /**
     * @var
     */
    protected $jsonData;

    /**
     * @var int
     */
    protected int $success = 0;
    /**
     * @var int
     */
    protected int $failed = 0;

    /**
     * @var array
     */
    protected $existing;

    /**
     * @var
     */
    protected $dbIatiIdentifiers;

    /**
     * @var
     */
    protected $organizationReportingOrg;

    private $authUser;

    /**
     * XmlQueueWriter constructor.
     *
     * @param                        $authUser
     * @param                        $orgId
     * @param                        $orgRef
     * @param                        $dbIatiIdentifiers
     * @param                        $xmlActivityIdentifiers
     * @param                        $organizationReportingOrg
     * @param ActivityRepository     $activityRepo
     * @param TransactionRepository  $transactionRepo
     * @param ResultRepository       $resultRepo
     * @param DocumentLinkRepository $documentLinkRepo
     */
    public function __construct(
        $authUser,
        $orgId,
        $orgRef,
        $dbIatiIdentifiers,
        $xmlActivityIdentifiers,
        $organizationReportingOrg,
        ActivityRepository $activityRepo,
        TransactionRepository $transactionRepo,
        ResultRepository $resultRepo,
        DocumentLinkRepository $documentLinkRepo
    ) {
        $this->authUser = $authUser;
        $this->activityRepo = $activityRepo;
        $this->transactionRepo = $transactionRepo;
        $this->resultRepo = $resultRepo;
        $this->documentLinkRepo = $documentLinkRepo;
        $this->userId = $authUser->id;
        $this->orgId = $orgId;
        $this->orgRef = $orgRef;
        $this->dbIatiIdentifiers = $dbIatiIdentifiers;
        $this->xmlActivityIdentifiers = $xmlActivityIdentifiers;
        $this->organizationReportingOrg = $organizationReportingOrg;
        $this->xml_data_storage_path = env('XML_DATA_STORAGE_PATH ', 'XmlImporter/tmp');
    }

    /**
     * Store mapped activity in database.
     *
     * @param $mappedActivity
     *
     * @return bool
     * @throws \JsonException
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function save($mappedActivity): bool
    {
        $activityIdentifier = Arr::get($mappedActivity, 'iati_identifier.activity_identifier');

        /** @var $xmlValidator XmlValidator */
        $xmlValidator = app(XmlValidator::class);
        $existence = $this->activityAlreadyExists($activityIdentifier);
        $duplicate = $this->activityIsDuplicate(Arr::get($mappedActivity, 'iati_identifier.iati_identifier_text'));
        $isIdentifierValid = $this->isIdentifierValid(Arr::get($mappedActivity, 'iati_identifier.iati_identifier_text'));

        $errors = $xmlValidator->init($mappedActivity)
            ->prepareDependencies($this->authUser, $this->organizationReportingOrg)
            ->validateActivity($duplicate, $isIdentifierValid);

        $mappedActivity['org_id'] = $this->orgId;
        $this->success++;

        $this->appendDataIntoFile($mappedActivity, $errors, $existence);

        return true;
    }

    /**
     * @param $currentActivityIdentifier
     *
     * @return bool
     */
    public function activityAlreadyExists($currentActivityIdentifier): bool
    {
        $currentActivityIdentifier = trim($currentActivityIdentifier);

        return in_array(trim($currentActivityIdentifier), trimStringValueInArray($this->dbIatiIdentifiers), true);
    }

    /**
     * Checks if xml contains duplicate activity.
     *
     * @param $identifier
     *
     * @return bool
     */
    public function activityIsDuplicate($identifier): bool
    {
        return Arr::get($this->xmlActivityIdentifiers, $identifier, 0) > 1 ? true : false;
    }

    /**
     * Checks if activity identifier is valid for the organization.
     *
     * @param $identifier
     *
     * @return bool
     */
    public function isIdentifierValid($identifier): bool
    {
        return str_starts_with($identifier, $this->orgRef . '-');
    }

    /**
     * Append data into the file containing previous data.
     * Stores validated data to valid.json file.
     * Basically the logic here if to fetch valid.json from s3 and append current data, then push to s3 again.
     * With an additional condition that checks if the current activity being processed already exists or not in s3's valid.json.
     * If already exists in valid.json, do not append. (To avoid duplication in the import listing page).
     *
     * @param $data
     * @param $errors
     * @param $existence
     *
     * @return void
     * @throws \JsonException
     */
    protected function appendDataIntoFile($data, $errors, $existence): void
    {
        $path = sprintf('%s/%s/%s/%s', $this->xml_data_storage_path, $this->orgId, $this->userId, 'valid.json');
        $validJsonFile = awsGetFile($path);

        if (!$validJsonFile) {
            $contentInValidDotJson = collect([
                [
                    'data'      => $data,
                    'errors'    => $errors,
                    'status'    => 'processed',
                    'existence' => $existence,
                ],
            ]);

            $this->uploadContent($path, $contentInValidDotJson->toJson(JSON_THROW_ON_ERROR));

            return;
        }

        $contentInValidDotJson = collect(json_decode($validJsonFile, true, 512, JSON_THROW_ON_ERROR));

        $appendableData = [
            'data'      => $data,
            'errors'    => $errors,
            'status'    => 'processed',
            'existence' => $existence,
        ];

        $contentInValidDotJson->push($appendableData);

        $this->uploadContent($path, $contentInValidDotJson->toJson(JSON_THROW_ON_ERROR));
    }

    /**
     * Upload content to aws.
     *
     * @param string $path
     * @param string $content
     *
     * @return void
     */
    private function uploadContent(string $path, string $content): void
    {
        try {
            awsUploadFile($path, $content);
        } catch (\Exception $e) {
            awsUploadFile('error-appendDataIntoFile.log', $e->getMessage());
        }
    }
}
