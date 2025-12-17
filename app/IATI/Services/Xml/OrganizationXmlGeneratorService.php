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
     * OrganizationOrganizationXmlGeneratorService Constructor.
     */
    public function __construct(
        protected OrganizationXmlGenerator $organizationXmlGenerator,
        protected XmlSchemaErrorParser $xmlErrorParser
    ) {
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
    public function generateOrganizationXml($settings, $organization): bool
    {
        return $this->organizationXmlGenerator->generateOrganizationXml($settings, $organization);
    }

    /**
     * Deletes the unpublished file from server.
     *
     * @param $filename
     *
     * @return void
     */
    public function deleteUnpublishedFile($filename): bool
    {
        return $this->organizationXmlGenerator->deleteUnpublishedFile($filename);
    }
}
