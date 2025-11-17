<?php

declare(strict_types=1);

namespace App\IATI\Services\Xml;

use App\IATI\Elements\Xml\XmlGenerator;
use App\IATI\Models\Activity\Activity;
use App\IATI\Models\Organization\Organization;
use App\IATI\Models\Setting\Setting;
use App\IATI\Traits\XmlServiceTrait;
use DOMDocument;
use Exception;

/**
 * Class XmlGeneratorService.
 */
class XmlGeneratorService
{
    use XmlServiceTrait;

    /**
     * @var XmlGenerator
     */
    protected XmlGenerator $xmlGenerator;

    /**
     * @var XmlSchemaErrorParser
     */
    protected XmlSchemaErrorParser $xmlErrorParser;

    /**
     * Change Source: https://github.com/iati/iatipublisher/issues/1423
     * Bug: Cancel bulk publishing not working.
     *
     * We can, should and must only cancel bulk publish for activity where 'bulk_publishing_status' table  is status 'created'.
     * The publishing process is synchronous in nature.
     * In the previous code, the status of all activity would change to 'processing' when the first activity is processed.
     * So basically the status was preemptively changing to 'processing' for all activity, that didn't accurately represent the actual status.
     *
     * The change I'm making, will provide UUID to XmlGenerator so that I can call BulkPublishingStatusRepository::updateActivityStatus() method.
     *
     * @var string|bool
     */
    private string|bool  $uuid;

    /**
     * @param bool|string $uuid
     *
     * @return void
     */
    public function setUuid(bool|string $uuid): void
    {
        $this->uuid = $uuid;
    }

    /**
     * XmlGeneratorService Constructor.
     *
     * @param XmlGenerator $xmlGenerator
     * @param XmlSchemaErrorParser $xmlErrorParser
     */
    public function __construct(XmlGenerator $xmlGenerator, XmlSchemaErrorParser $xmlErrorParser)
    {
        $this->xmlGenerator = $xmlGenerator;
        $this->xmlErrorParser = $xmlErrorParser;
    }

    /**
     * Generates combines activities xml file and publishes to IATI.
     *
     * @param $activity
     * @param $transaction
     * @param $result
     * @param $settings
     * @param $organization
     *
     * @return DOMDocument|null
     */
    public function generateActivityXml($activity, $transaction, $result, $settings, $organization): ?DOMDocument
    {
        return $this->xmlGenerator->generateActivityXml($activity, $transaction, $result, $settings, $organization);
    }

    /**
     * Generates combines activities xml file and publishes to IATI.
     *
     * @param $activities
     * @param $settings
     * @param $organization
     *
     * @return array
     *
     * @throws \JsonException
     */
    public function generateActivitiesXml($activities, $settings, $organization): array
    {
        if ($this->uuid) {
            $this->xmlGenerator->setUuid($this->uuid);
        }

        return $this->xmlGenerator->generateActivitiesXml($activities, $settings, $organization);
    }

    /**
     * Deletes the unpublished file from server.
     *
     * @param $filename
     *
     * @return void
     */
    public function deleteUnpublishedFile($filename): void
    {
        $this->xmlGenerator->deleteUnpublishedFile($filename);
    }

    /**
     * Returns xml data of activity.
     *
     * @param $activity
     * @param $transaction
     * @param $result
     * @param $settings
     * @param $organization
     *
     * @return string
     *
     * @throws \JsonException
     */
    public function getActivityXmlData($activity, $transaction, $result, $settings, $organization): string
    {
        $xmlDom = $this->xmlGenerator->getXml($activity, $transaction, $result, $settings, $organization);

        return $xmlDom->saveXML();
    }

    /**
     * Appends generated/new XML content to merged xml and uploads to S3.
     *
     * @param DOMDocument $generatedXmlContent
     * @param $settings
     * @param $activity
     * @param $organization
     * @return void
     *
     * @throws Exception
     */
    public function appendCompleteActivityXmlToMergedXml(DOMDocument $generatedXmlContent, $settings, $activity, $organization): void
    {
        $this->xmlGenerator->appendCompleteActivityXmlToMergedXml($generatedXmlContent, $settings, $activity, $organization);
    }

    /**
     * Removes given activity from merged xml and re-uploads it to s3.
     *
     * @throws Exception
     */
    public function removeActivityXmlFromMergedXmlInS3(Activity $activity, Organization $organization, Setting $settings): void
    {
        $this->xmlGenerator->removeActivityXmlFromMergedXmlInS3($activity, $organization, $settings);
    }
}
