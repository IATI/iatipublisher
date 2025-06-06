<?php

declare(strict_types=1);

namespace App\Xml\Providers;

/**
 * Class XmlServiceProvider.
 */
class XmlServiceProvider
{
    /**
     * @var
     */
    protected $validator;
    /**
     * @var
     */
    protected $generator;

    /**
     * Initialize an Xml Generator instance.
     * @return $this
     */
    public function initializeGenerator(): static
    {
        $this->generator = 'App\IATI\Elements\Xml\XmlGenerator';

        return $this;
    }

    /**
     * Initialize an Xml Validator instance.
     *
     * @return $this
     */
    public function initializeValidator(): static
    {
        $this->validator = 'App\IATI\Services\ImportActivity\XmlService';

        return $this;
    }

    /**
     * Generate Xml Files.
     * @param $includedActivities
     * @param $filename
     * @return $this
     */
    public function generateXmlFiles($includedActivities, $filename): static
    {
        $this->generator->getMergeXml($includedActivities, $filename);

        return $this;
    }

    /**
     * Save the published files records into the database.
     * @param $filename
     * @param $organizationId
     * @param $includedActivities
     * @return $this
     */
    public function save($filename, $organizationId, $includedActivities): static
    {
        $this->generator->savePublishedFiles($filename, $organizationId, $includedActivities);

        return $this;
    }

    /**
     * Validate an Xml file against the schema.
     * @param $activity
     * @param $organizationElement
     * @param $activityElement
     * @return mixed
     */
    public function validate($activity, $organizationElement, $activityElement): mixed
    {
        $organization = $activity->organization;

        return $this->validator->validateActivitySchema($activity, $activity->transactions, $activity->results, $organization->settings, $activityElement, $organizationElement, $organization);
    }

    /**
     * Generate an Activity Xml file.
     *
     * @param      $activity
     * @param      $organizationElement
     * @param      $activityElement
     * @param null $unpublish
     */
    public function generate($activity, $organizationElement, $activityElement, $unpublish = null): void
    {
        $organization = $activity->organization;
        $this->generator->generateActivityXml($activity, $activity->transactions, $activity->results, $organization->settings, $activityElement, $organizationElement, $organization, $unpublish);
    }
}
