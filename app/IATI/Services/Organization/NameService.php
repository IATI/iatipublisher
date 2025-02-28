<?php

declare(strict_types=1);

namespace App\IATI\Services\Organization;

use App\IATI\Elements\Builder\BaseFormCreator;
use App\IATI\Models\Organization\Organization;
use App\IATI\Repositories\Organization\OrganizationRepository;
use App\IATI\Traits\OrganizationXmlBaseElements;
use Illuminate\Database\Eloquent\Model;
use Kris\LaravelFormBuilder\Form;

/**
 * Class NameService.
 */
class NameService
{
    use OrganizationXmlBaseElements;

    /**
     * @var OrganizationRepository
     */
    protected OrganizationRepository $organizationRepository;

    /**
     * @var BaseFormCreator
     */
    protected BaseFormCreator $baseFormCreator;

    /**
     * NameService constructor.
     *
     * @param OrganizationRepository $organizationRepository
     * @param BaseFormCreator $baseFormCreator
     */
    public function __construct(OrganizationRepository $organizationRepository, BaseFormCreator $baseFormCreator)
    {
        $this->organizationRepository = $organizationRepository;
        $this->baseFormCreator = $baseFormCreator;
    }

    /**
     * Returns name data of an organization.
     *
     * @param int $organization_id
     *
     * @return array|null
     */
    public function getNameData(int $organization_id): ?array
    {
        return $this->organizationRepository->find($organization_id)->name;
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
        return $this->organizationRepository->getOrganizationData($id);
    }

    /**
     * Updates Organization name.
     *
     * @param $id
     * @param $organizationName
     *
     * @return bool
     */
    public function update($id, $organizationName): bool
    {
        $organizationName = array_values($organizationName['narrative']);
        $organization = $this->organizationRepository->find($id);
        $deprecationStatusMap = $organization->deprecation_status_map;
        $deprecationStatusMap['name'] = doesOrganisationNameHaveDeprecatedCode($organizationName);

        return $this->organizationRepository->update($id, [
            'name'                   => ['narrative' => $organizationName],
            'deprecation_status_map' => $deprecationStatusMap,
        ]);
    }

    /**
     * Generates name form.
     *
     * @param $id
     * @param array $deprecationStatusMap
     * @return Form
     */
    public function formGenerator($id, $deprecationStatusMap = []): Form
    {
        $element = readOrganizationElementJsonSchema();
        $model['narrative'] = $this->getNameData($id);
        $this->baseFormCreator->url = route('admin.organisation.name.update', [$id]);

        return $this->baseFormCreator->editForm($model, $element['name'], 'PUT', '/organisation', deprecationStatusMap: $deprecationStatusMap);
    }

    /**
     * Returns name data for xml generation.
     *
     * @param Organization $organization
     *
     * @return array
     */
    public function getXMLData(Organization $organization): array
    {
        $name = (array) $organization->name;
        $organizationData = [];

        if (count($name)) {
            $organizationData[]['narrative'] = $this->buildNarrative($name);
        }

        return $organizationData;
    }
}
