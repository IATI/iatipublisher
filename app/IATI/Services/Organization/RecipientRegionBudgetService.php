<?php

declare(strict_types=1);

namespace App\IATI\Services\Organization;

use App\IATI\Elements\Builder\ParentCollectionFormCreator;
use App\IATI\Repositories\Organization\OrganizationRepository;
use App\IATI\Traits\OrganizationXmlBaseElements;
use Illuminate\Database\Eloquent\Model;
use Kris\LaravelFormBuilder\Form;

/**
 * Class RecipientRegionBudgetService.
 */
class RecipientRegionBudgetService
{
    use OrganizationXmlBaseElements;

    /**
     * @var OrganizationRepository
     */
    protected OrganizationRepository $organizationRepository;

    /**
     * @var ParentCollectionFormCreator
     */
    protected ParentCollectionFormCreator $parentCollectionFormCreator;

    /**
     * RecipientRegionBudgetService constructor.
     *
     * @param OrganizationRepository $organizationRepository
     * @param ParentCollectionFormCreator $parentCollectionFormCreator
     */
    public function __construct(OrganizationRepository $organizationRepository, ParentCollectionFormCreator $parentCollectionFormCreator)
    {
        $this->organizationRepository = $organizationRepository;
        $this->parentCollectionFormCreator = $parentCollectionFormCreator;
    }

    /**
     * Returns recipient region budget of an organization.
     *
     * @param int $organization_id
     *
     * @return array|null
     */
    public function getRecipientRegionBudgetData(int $organization_id): ?array
    {
        return $this->organizationRepository->find($organization_id)->recipient_region_budget;
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
     * Updates recipient org budget.
     *
     * @param $id
     * @param $recipientRegionBudget
     *
     * @return bool
     */
    public function update($id, $recipientRegionBudget): bool
    {
        $recipientRegionBudget['recipient_region_budget'] = array_values($recipientRegionBudget['recipient_region_budget']);

        foreach ($recipientRegionBudget['recipient_region_budget'] as $key => $budget) {
            foreach ($budget['recipient_region'] as $sub_index => $sub_element) {
                $recipientRegionBudget['recipient_region_budget'][$key]['recipient_region'][$sub_index]['narrative'] = array_values($sub_element['narrative']);
            }

            foreach ($budget['budget_line'] as $sub_index => $sub_element) {
                $recipientRegionBudget['recipient_region_budget'][$key]['budget_line'][$sub_index]['narrative'] = array_values($sub_element['narrative']);
            }

            $recipientRegionBudget['recipient_region_budget'][$key]['budget_line'] = array_values($recipientRegionBudget['recipient_region_budget'][$key]['budget_line']);
            $recipientRegionBudget['recipient_region_budget'][$key]['recipient_region'] = array_values($recipientRegionBudget['recipient_region_budget'][$key]['recipient_region']);
        }

        $recipientRegionBudget = array_values($recipientRegionBudget['recipient_region_budget']);

        $organization = $this->organizationRepository->find($id);
        $deprecationStatusMap = $organization->deprecation_status_map;
        $deprecationStatusMap['recipient_region_budget'] = doesOrganisationRecipientRegionBudgetHaveDeprecatedCode($recipientRegionBudget);

        return $this->organizationRepository->update($id, [
            'recipient_region_budget' => $recipientRegionBudget,
            'deprecation_status_map'  => $deprecationStatusMap,
        ]);
    }

    /**
     * Generates recipient region budget form.
     *
     * @param $id
     *
     * @return Form
     */
    public function formGenerator($id, $deprecationStatusMap = []): Form
    {
        $element = readOrganizationElementJsonSchema();
        $model['recipient_region_budget'] = $this->getRecipientRegionBudgetData($id) ?? [];
        $this->parentCollectionFormCreator->url = route('admin.organisation.recipient-region-budget.update', [$id]);

        return $this->parentCollectionFormCreator->editForm($model, $element['recipient_region_budget'], 'PUT', '/organisation', deprecationStatusMap: $deprecationStatusMap);
    }

    /**
     * return recipient region budget xml data.
     *
     * @param OrganizationData $organizationData
     *
     * @return array
     */
    public function getXmlData($organizationData): array
    {
        $orgRecipientRegionData = [];
        $recipientRegionBudget = (array) $organizationData->recipient_region_budget;

        foreach ($recipientRegionBudget as $orgRecipientRegion) {
            $orgRecipientRegionData[] = [
                '@attributes'      => [
                    'status' => $orgRecipientRegion['status'],
                ],
                'recipient-region' => [
                    '@attributes' => [
                        'vocabulary'     => $orgRecipientRegion['recipient_region'][0]['region_vocabulary'],
                        'vocabulary-uri' => $orgRecipientRegion['recipient_region'][0]['vocabulary_uri'] ?? '',
                        'code'           => $orgRecipientRegion['recipient_region'][0]['region_code'] ?? '',
                    ],
                    'narrative'   => $this->buildNarrative($orgRecipientRegion['recipient_region'][0]['narrative']),
                ],
                'period-start'     => [
                    '@attributes' => [
                        'iso-date' => $orgRecipientRegion['period_start'][0]['date'],
                    ],
                ],
                'period-end'       => [
                    '@attributes' => [
                        'iso-date' => $orgRecipientRegion['period_end'][0]['date'],
                    ],
                ],
                'value'            => $this->buildValue($orgRecipientRegion['value']),
                'budget-line'      => $this->buildBudgetLine($orgRecipientRegion['budget_line']),
            ];
        }

        return $orgRecipientRegionData;
    }
}
