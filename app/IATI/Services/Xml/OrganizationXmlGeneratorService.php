<?php

declare(strict_types=1);

namespace App\IATI\Services\Xml;

use App\IATI\Elements\Xml\OrganizationXmlGenerator;
use App\IATI\Traits\XmlServiceTrait;

/**
 * Class OrganizationOrganizationXmlGeneratorService.
 */
class OrganizationXmlGeneratorService
{
    use XmlServiceTrait;

    /**
     * @var OrganizationXmlGenerator
     */
    protected OrganizationXmlGenerator $organizationXmlGenerator;

    /**
     * @var XmlSchemaErrorParser
     */
    protected XmlSchemaErrorParser $xmlErrorParser;

    /**
     * OrganizationOrganizationXmlGeneratorService Constructor.
     *
     * @param OrganizationXmlGenerator $organizationXmlGenerator
     * @param XmlSchemaErrorParser $xmlErrorParser
     */
    public function __construct(OrganizationXmlGenerator $organizationXmlGenerator, XmlSchemaErrorParser $xmlErrorParser)
    {
        $this->organizationXmlGenerator = $organizationXmlGenerator;
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
     * @return void
     */
    public function generateOrganizationXml($settings, $organization): void
    {
        $this->organizationXmlGenerator->generateOrganizationXml($settings, $organization);
    }

    /**
     * Deletes the unpublished file from server.
     *
     * @param $filename
     *
     * @return void
     */
    public function deleteUnpublishedFile($filename)
    {
        $this->organizationXmlGenerator->deleteUnpublishedFile($filename);
    }
}
