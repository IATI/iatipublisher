<?php

declare(strict_types=1);

namespace App\XmlImporter\Foundation\Queue;

use App\IATI\Repositories\Import\ImportStatusRepository;
use App\Jobs\Job;
use App\XmlImporter\Foundation\XmlQueueProcessor;

class ImportXml extends Job
{
    /**
     * @var
     */
    protected $organizationId;
    /**
     * @var
     */
    protected $orgRef;
    /**
     * @var
     */
    protected $filename;
    /**
     * @var
     */
    protected $authUser;
    /**
     * @var
     */
    protected $iatiIdentifiers;
    private $organizationReportingOrg;

    /**
     * @var \Illuminate\Contracts\Foundation\Application|mixed
     */
    private ImportStatusRepository $importStatusRepository;

    /**
     * ImportXml constructor.
     *
     * @param $organizationId
     * @param $orgRef
     * @param $authUser
     * @param $filename
     * @param $iatiIdentifiers
     * @param $organizationReportingOrg
     */
    public function __construct($organizationId, $orgRef, $authUser, $filename, $iatiIdentifiers, $organizationReportingOrg)
    {
        $this->organizationId = $organizationId;
        $this->orgRef = $orgRef;
        $this->filename = $filename;
        $this->authUser = $authUser;
        $this->iatiIdentifiers = $iatiIdentifiers;
        $this->organizationReportingOrg = $organizationReportingOrg;
        $this->importStatusRepository = app(ImportStatusRepository::class);
    }

    /**
     * @return void
     */
    public function handle(): void
    {
        try {
            $xmlImportQueue = app()->make(XmlQueueProcessor::class);
            $xmlImportQueue->import($this->filename, $this->organizationId, $this->orgRef, $this->authUser, $this->iatiIdentifiers, $this->organizationReportingOrg, $this->importStatusRepository);

            $this->delete();
        } catch (\Exception $e) {
            logger()->error($e);
            awsUploadFile('error.log', $e);
            $this->delete();
        }
    }
}
