<?php

declare(strict_types=1);

namespace App\IATI\Services\Organization;

use App\IATI\Elements\Builder\BaseFormCreator;
use App\IATI\Models\Organization\Organization;
use App\IATI\Repositories\Activity\ActivityRepository;
use App\IATI\Repositories\Organization\OrganizationRepository;
use Illuminate\Database\Eloquent\Model;
use JsonException;
use Kris\LaravelFormBuilder\Form;

/**
 * Class OrganizationIdentifierService.
 */
class OrganizationIdentifierService
{
    /**
     * @var OrganizationRepository
     */
    protected OrganizationRepository $organizationRepository;

    /**
     * @var BaseFormCreator
     */
    protected BaseFormCreator $baseFormCreator;

    /**
     * @var ActivityRepository
     */
    protected ActivityRepository $activityRepository;

    /**
     * OrganizationIdentifierService constructor.
     *
     * @param OrganizationRepository $organizationRepository
     * @param ActivityRepository $activityRepository
     * @param BaseFormCreator $baseFormCreator
     */
    public function __construct(
        OrganizationRepository $organizationRepository,
        ActivityRepository $activityRepository,
        BaseFormCreator $baseFormCreator
    ) {
        $this->organizationRepository = $organizationRepository;
        $this->activityRepository = $activityRepository;
        $this->baseFormCreator = $baseFormCreator;
    }

    /**
     * Returns organization identifier data of an organization.
     *
     * @param int $organization_id
     *
     * @return string
     */
    public function getIdentifierData(int $organization_id): string
    {
        return $this->organizationRepository->find($organization_id)->identifier;
    }

    /**
     * Returns Organization object.
     *
     * @param $id
     *
     * @return Model
     */
    public function getOrganizationData($id): Organization
    {
        return $this->organizationRepository->getOrganizationData($id);
    }

    /**
     * Updates Organization identifier.
     * Syncs activity level reporting org if changes are made to organization identifier.
     *
     * @param $id
     * @param $organizationIdentifiers
     *
     * @return bool
     * @throws JsonException
     */
    public function update($id, $organizationIdentifiers): bool
    {
        $organization = $this->organizationRepository->find($id);
        $deprecationStatusMap = $organization->deprecation_status_map;
        $olderOrgInfo = clone $organization;
        $reportingOrg = $organization->reporting_org;
        $reportingOrg[0]['ref'] = $organizationIdentifiers['organization_registration_agency'] . '-' . $organizationIdentifiers['registration_number'];

        $organizationIdentifiers = [
            'identifier'          => $reportingOrg[0]['ref'],
            'country'             => $organizationIdentifiers['organization_country'],
            'registration_agency' => $organizationIdentifiers['organization_registration_agency'],
            'registration_number' => $organizationIdentifiers['registration_number'],
            'reporting_org'       => $reportingOrg,
        ];

        $organization->fill($organizationIdentifiers);
        $hasChanged = $organization->isDirty('identifier');

        if ($hasChanged) {
            $appendableObject = [
                'identifier' => $olderOrgInfo->identifier,
                'updated_at' => now(),
            ];
            $allOldIdentifiers = $olderOrgInfo->old_identifiers;
            $alreadyExists = false;

            foreach ($allOldIdentifiers as $index => $oldIdentifier) {
                if ($oldIdentifier && isset($oldIdentifier['identifier']) && $oldIdentifier['identifier'] === $olderOrgInfo->identifier) {
                    $alreadyExists = true;
                    $oldIdentifier['updated_at'] = now();
                    $allOldIdentifiers[$index] = $oldIdentifier;

                    break;
                }
            }

            if (!$alreadyExists) {
                $allOldIdentifiers[] = $appendableObject;
            }

            $organization->old_identifiers = $allOldIdentifiers;
        }

        $deprecationStatusMap['organization_identifier'] = doesOrganisationIdentifierHaveDeprecatedCode($organizationIdentifiers);
        $organization->deprecation_status_map = $deprecationStatusMap;

        $organization->save();

        if ($hasChanged) {
            $syncedActivityIdentifier = $this->syncActivityIdentifierForNeverPublishedActivities($organization);
            $syncedOtherIdentifier = $this->syncOtherIdentifierForEverPublishedActivities($olderOrgInfo);
            $syncedReportingOrg = $this->syncActivityReportingOrgFromIdentifier($id);

            return $syncedActivityIdentifier && $syncedOtherIdentifier && $syncedReportingOrg;
        }

        return true;
    }

    /**
     * Generates name form.
     *
     * @param $id
     *
     * @return Form
     * @throws JsonException
     */
    public function formGenerator($id, $deprecationStatusMap = []): Form
    {
        $element = readOrganizationElementJsonSchema();
        $organization = $this->getOrganizationData($id);
        $model['organisation_identifier'] = $organization['identifier'];
        $model['organization_country'] = $organization['country'];
        $model['organization_registration_agency'] = $organization['registration_agency'];
        $model['registration_number'] = $organization['registration_number'];
        $this->baseFormCreator->url = route('admin.organisation.identifier.update', [$id]);

        return $this->baseFormCreator->editForm(
            $model,
            $element['organisation_identifier'],
            'PUT',
            '/organisation',
            true,
            additonalInfo: [
                'formId'   => 'save-and-exit-organization-identifier-form',
                'submitId' => 'save-and-exit-button',
            ],
            deprecationStatusMap: $deprecationStatusMap
        );
    }

    /**
     * Returns organization identifier data for xml generation.
     *
     * @param Organization $organization
     *
     * @return array
     */
    public function getXMLData(Organization $organization): array
    {
        $organizationData[] = $organization->identifier;

        return $organizationData;
    }

    /**
     * Updates activity->reporting_org when there's change in organization identifier.
     *
     * @param $id
     *
     * @return int
     */
    public function syncActivityReportingOrgFromIdentifier($id): int
    {
        $orgReportingOrg = $this->organizationRepository->find($id)->reporting_org[0];

        return $this->activityRepository->syncReportingOrg($id, $orgReportingOrg);
    }

    /**
     * Updates Organization identifier for activities where 'has_ever_been_published === false'.
     *
     * @param $organization
     *
     * @return bool
     *
     * @throws JsonException
     */
    public function syncActivityIdentifierForNeverPublishedActivities($organization): bool
    {
        return $this->activityRepository->syncActivityIdentifierForNeverPublishedActivities($organization);
    }

    /**
     * Updates other_identifier field of activities where has_ever_been_published === true.
     *
     * @param Organization $organization
     *
     * @return bool
     *
     * @throws JsonException
     */
    private function syncOtherIdentifierForEverPublishedActivities(Organization $organization): bool
    {
        $appendableOtherIdentifier = [
            'reference'      => $organization->identifier,
            'reference_type' => 'B1',
            'owner_org'      => [
                [
                    'ref'       => null,
                    'narrative' => [
                        [
                            'narrative' => null,
                            'language'  => null,
                        ],
                    ],
                ],
            ],
        ];

        return $this->activityRepository->syncOtherIdentifierOfOrganizationActivities($organization, $appendableOtherIdentifier);
    }
}
