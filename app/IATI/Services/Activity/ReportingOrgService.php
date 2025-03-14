<?php

declare(strict_types=1);

namespace App\IATI\Services\Activity;

use App\IATI\Elements\Builder\ParentCollectionFormCreator;
use App\IATI\Models\Activity\Activity;
use App\IATI\Repositories\Activity\ActivityRepository;
use App\IATI\Traits\XmlBaseElement;
use Kris\LaravelFormBuilder\Form;

/**
 * Class ReportingOrgService.
 */
class ReportingOrgService
{
    use XmlBaseElement;

    /**
     * @var ActivityRepository
     */
    protected ActivityRepository $activityRepository;

    /**
     * @var ParentCollectionFormCreator
     */
    protected ParentCollectionFormCreator $parentCollectionFormCreator;

    /**
     * ReportingOrgService constructor.
     *
     * @param ActivityRepository $activityRepository
     * @param ParentCollectionFormCreator $parentCollectionFormCreator
     */
    public function __construct(ActivityRepository $activityRepository, ParentCollectionFormCreator $parentCollectionFormCreator)
    {
        $this->activityRepository = $activityRepository;
        $this->parentCollectionFormCreator = $parentCollectionFormCreator;
    }

    /**
     * Returns reporting org data of an Activity.
     *
     * @param int $activity_id
     *
     * @return array|null
     */
    public function getReportingOrgData(int $activity_id): ?array
    {
        return $this->activityRepository->find($activity_id)->reporting_org;
    }

    /**
     * Returns Activity object.
     *
     * @param $id
     *
     * @return object|null
     */
    public function getActivityData($id): ?object
    {
        return $this->activityRepository->find($id);
    }

    /**
     * Updates Activity identifier.
     *
     * @param $id
     * @param $reportingOrg
     *
     * @return int
     */
    public function update($id, $reportingOrg): int
    {
        $secondaryReporter = $reportingOrg['reporting_org'][0]['secondary_reporter'];

        return $this->activityRepository->updateReportingOrg($id, 'secondary_reporter', $secondaryReporter);
    }

    /**
     * Generates name form.
     *
     * @param $id
     *
     * @return Form
     * @throws \JsonException
     */
    public function formGenerator($id, $activityDefaultFieldValues, $deprecationStatusMap = []): Form
    {
        $element = readElementJsonSchema();
        $model['reporting_org'] = $this->getReportingOrgData($id) ?? [];
        $this->parentCollectionFormCreator->url = route('admin.activity.reporting-org.update', [$id]);

        return $this->parentCollectionFormCreator->editForm($model, $element['reporting_org'], 'PUT', '/activity/' . $id, $activityDefaultFieldValues, deprecationStatusMap: $deprecationStatusMap);
    }

    /**
     * Generates xml data for reporting org.
     *
     * @param Activity $activity
     *
     * @return array
     */
    public function getXmlData(Activity $activity): array
    {
        $activityData = [];
        $activityReportingOrg = (array) $activity->reporting_org;

        foreach ($activityReportingOrg as $reportingOrg) {
            $activityData[] = [
                '@attributes' => [
                    'type'                  => $reportingOrg['type'],
                    'ref'                   => $reportingOrg['ref'],
                    'secondary-reporter'    => $reportingOrg['secondary_reporter'],
                ],
                'narrative'   => $this->buildNarrative($reportingOrg['narrative']),
            ];
        }

        return $activityData;
    }
}
