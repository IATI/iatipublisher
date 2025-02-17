<?php

declare(strict_types=1);

namespace App\XmlImporter\Listeners;

use App\IATI\Services\ImportActivity\ImportXmlService;
use App\XmlImporter\Events\XmlWasUploaded;
use App\XmlImporter\Foundation\Queue\ImportXml;
use App\XmlImporter\Foundation\XmlQueueProcessor;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Auth;

/**
 * Class XmlUpload.
 */
class XmlUpload
{
    use DispatchesJobs;

    /**
     * @var ImportXmlService
     */
    protected ImportXmlService $importXmlService;

    /**
     * @var XmlQueueProcessor
     */
    protected XmlQueueProcessor $xmlQueueProcessor;

    /**
     * XmlUpload constructor.
     * @param ImportXmlService  $importXmlService
     * @param XmlQueueProcessor $xmlQueueProcessor
     */
    public function __construct(ImportXmlService $importXmlService, XmlQueueProcessor $xmlQueueProcessor)
    {
        $this->importXmlService = $importXmlService;
        $this->xmlQueueProcessor = $xmlQueueProcessor;
    }

    /**
     * Handle the XmlWasUploadedEvent.
     *
     * @param XmlWasUploaded $event
     * @return bool
     */
    public function handle(XmlWasUploaded $event): bool
    {
        $this->dispatch(new ImportXml($event->organizationId, $event->orgRef, Auth::user(), $event->filename, $event->iatiIdentifiers, Auth::user()->organization->reporting_org));

        return true;
    }
}
