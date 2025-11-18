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
    public function getSingleActivityXmlDom($activity, $transaction, $result, $settings, $organization): ?DOMDocument
    {
        return $this->xmlGenerator->getSingleActivityXmlDom($activity, $transaction, $result, $settings, $organization);
    }

    public function saveSingleActivityFileToAws(string $xmlContent, string $publisherId, int $activityId): string
    {
        $publishedActivityFileName = sprintf('%s-%s.xml', $publisherId, $activityId);
        $path = sprintf('%s/%s/%s', 'xml', 'activityXmlFiles', $publishedActivityFileName);

        $result = awsUploadFile($path, $xmlContent);

        if (!$result) {
            throw new Exception("Failed to upload single activity file to AWS S3: {$publishedActivityFileName}");
        }

        return $publishedActivityFileName;
    }

    /**
     * Generates combines activities XML data structures and orchestrates bulk file upload.
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

        $generationData = $this->xmlGenerator->generateActivitiesXml($activities, $settings, $organization);

        $this->saveBulkActivityFilesToAws(
            $generationData['single_xml_contents']
        );

        return $generationData;
    }

    /**
     * Uploads all generated single activity files to AWS.
     * @param array $fileContents map of [filename => xml_string]
     * @throws \RuntimeException
     */
    public function saveBulkActivityFilesToAws(array $fileContents): void
    {
        foreach ($fileContents as $fileName => $content) {
            $path = sprintf('%s/%s/%s', 'xml', 'activityXmlFiles', $fileName);
            $result = awsUploadFile($path, $content);

            if (!$result) {
                throw new \RuntimeException("Failed to upload bulk activity file to AWS S3: {$fileName}");
            }
        }
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

    /**
     * @throws Exception
     */
    public function appendMultipleInnerActivityXmlToMergedXml(array $innerActivityXmlArray, $settings, $organization, array $activityMappedToActivityIdentifier, bool $refreshTimestamp = true): void
    {
        $this->xmlGenerator->appendMultipleInnerActivityXmlToMergedXml($innerActivityXmlArray, $settings, $organization, $activityMappedToActivityIdentifier, $refreshTimestamp);
    }
}
