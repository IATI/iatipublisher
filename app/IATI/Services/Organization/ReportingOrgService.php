<?php

declare(strict_types=1);

namespace App\IATI\Services\Organization;

use App\IATI\Elements\Builder\ParentCollectionFormCreator;
use App\IATI\Models\Organization\Organization;
use App\IATI\Repositories\Activity\ActivityRepository;
use App\IATI\Repositories\Organization\OrganizationRepository;
use App\IATI\Traits\OrganizationXmlBaseElements;
use Illuminate\Database\Eloquent\Model;
use Kris\LaravelFormBuilder\Form;

/**
 * Class ReportingOrgService.
 */
class ReportingOrgService
{
    use OrganizationXmlBaseElements;

    /**
     * @var OrganizationRepository
     */
    protected OrganizationRepository $organizationRepository;

    /**
     * @var ActivityRepository
     */
    protected ActivityRepository        $activityRepository;

    /**
     * @var ParentCollectionFormCreator
     */
    protected ParentCollectionFormCreator $parentCollectionFormCreator;

    /**
     * ReportingOrgService constructor.
     *
     * @param OrganizationRepository $organizationRepository
     * @param ActivityRepository $activityRepository
     * @param ParentCollectionFormCreator $parentCollectionFormCreator
     */
    public function __construct(OrganizationRepository $organizationRepository, ActivityRepository $activityRepository, ParentCollectionFormCreator $parentCollectionFormCreator)
    {
        $this->organizationRepository = $organizationRepository;
        $this->activityRepository = $activityRepository;
        $this->parentCollectionFormCreator = $parentCollectionFormCreator;
    }

    /**
     * Returns reporting org data of an organization.
     *
     * @param int $organization_id
     *
     * @return array|null
     */
    public function getReportingOrgData(int $organization_id): ?array
    {
        return $this->organizationRepository->find($organization_id)->reporting_org;
    }

    /**
     * Returns Organization object.
     *
     * @param $id
     *
     * @return Model
     */
    public function getOrganizationData($id): Model
    {
        return $this->organizationRepository->find($id);
    }

    /**
     * Updates Organization identifier.
     *
     * @param $id
     * @param $reportingOrg
     *
     * @return bool
     */
    public function update($id, $reportingOrg): bool
    {
        $organization = $this->organizationRepository->find($id);

        foreach ($reportingOrg['reporting_org'] as $key => $description) {
            $reportingOrg['reporting_org'][$key]['narrative'] = array_values($description['narrative']);
        }

        $reportingOrg = array_values($reportingOrg['reporting_org']);
        $hasChanged = $this->checkForChange($organization, $reportingOrg);

        $deprecationStatusMap = $organization->deprecation_status_map;
        $deprecationStatusMap['reporting_org'] = doesOrganisationReportingOrgHaveDeprecatedCode($reportingOrg);

        return $organization->update([
                'reporting_org'          => $reportingOrg,
                'deprecation_status_map' => $deprecationStatusMap,
            ]) && (!$hasChanged || $this->syncReportingOrg($id));
    }

    /**
     * Generates name form.
     *
     * @param $id
     *
     * @return Form
     */
    public function formGenerator($id, $deprecationStatusMap = []): Form
    {
        $element = readOrganizationElementJsonSchema();
        $model['reporting_org'] = $this->getReportingOrgData($id) ?? [];
        $this->parentCollectionFormCreator->url = route('admin.organisation.reporting-org.update', [$id]);

        return $this->parentCollectionFormCreator->editForm($model, $element['reporting_org'], 'PUT', '/organisation', deprecationStatusMap: $deprecationStatusMap);
    }

    /**
     * Generates xml data for reporting org.
     *
     * @param Organization $organization
     *
     * @return array
     */
    public function getXmlData(Organization $organization): array
    {
        $organizationData = [];
        $orgReportingOrg = (array) $organization->reporting_org;

        foreach ($orgReportingOrg as $reportingOrg) {
            $organizationData[] = [
                '@attributes' => [
                    'type'                  => $reportingOrg['type'],
                    'ref'                   => $reportingOrg['ref'],
                    'secondary-reporter'    => $reportingOrg['secondary_reporter'],
                ],
                'narrative'   => $this->buildNarrative($reportingOrg['narrative']),
            ];
        }

        return $organizationData;
    }

    /**
     * Updates activity->reporting_org when there's change in organisation reporting_org.
     *
     * @param $id
     *
     * @return int
     */
    protected function syncReportingOrg($id): int
    {
        $orgReportingOrg = $this->organizationRepository->find($id)->reporting_org[0];

        return $this->activityRepository->syncReportingOrg($id, $orgReportingOrg);
    }

    /**
     * Checks if there's changes in ref, type or narrative.
     *
     * @param object|null $organization
     * @param array $reportingOrg
     *
     * @return bool
     */
    private function checkForChange(?object $organization, array $reportingOrg): bool
    {
        $oldReportingOrg = $organization->reporting_org[0];
        $reportingOrg = $reportingOrg[0];

        return $oldReportingOrg['ref'] !== $reportingOrg['ref']
            || (!isset($oldReportingOrg['type'])
            || $oldReportingOrg['type'] !== $reportingOrg['type'])
            || (!isset($oldReportingOrg['narrative'])
            || $oldReportingOrg['narrative'] !== $reportingOrg['narrative']);
    }
}
