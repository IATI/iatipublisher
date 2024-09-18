<?php

declare(strict_types=1);

namespace App\IATI\Services\Organization;

use App\IATI\Elements\Builder\ParentCollectionFormCreator;
use App\IATI\Repositories\Organization\OrganizationRepository;
use App\IATI\Traits\OrganizationXmlBaseElements;
use Illuminate\Database\Eloquent\Model;
use Kris\LaravelFormBuilder\Form;

/**
 * Class RecipientOrgBudgetService.
 */
class RecipientOrgBudgetService
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
     * RecipientOrgBudgetService constructor.
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
     * Returns recipient org budget of an organization.
     *
     * @param int $organization_id
     *
     * @return array|null
     */
    public function getRecipientOrgBudgetData(int $organization_id): ?array
    {
        return $this->organizationRepository->find($organization_id)->recipient_org_budget;
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
     * @param $recipientOrgBudget
     *
     * @return bool
     */
    public function update($id, $recipientOrgBudget): bool
    {
        $recipientOrgBudget['recipient_org_budget'] = array_values($recipientOrgBudget['recipient_org_budget']);

        foreach ($recipientOrgBudget['recipient_org_budget'] as $key => $budget) {
            foreach ($budget['recipient_org'] as $sub_index => $sub_element) {
                $recipientOrgBudget['recipient_org_budget'][$key]['recipient_org'][$sub_index]['narrative'] = array_values($sub_element['narrative']);
            }

            foreach ($budget['budget_line'] as $sub_index => $sub_element) {
                $recipientOrgBudget['recipient_org_budget'][$key]['budget_line'][$sub_index]['narrative'] = array_values($sub_element['narrative']);
            }

            $recipientOrgBudget['recipient_org_budget'][$key]['budget_line'] = array_values($recipientOrgBudget['recipient_org_budget'][$key]['budget_line']);
        }

        $recipientOrgBudget = array_values($recipientOrgBudget['recipient_org_budget']);

        $organization = $this->organizationRepository->find($id);
        $deprecationStatusMap = $organization->deprecation_status_map;
        $deprecationStatusMap['recipient_org_budget'] = doesOrganisationRecipientOrgBudgetHaveDeprecatedCode($recipientOrgBudget);

        return $this->organizationRepository->update($id, [
            'recipient_org_budget'   => $recipientOrgBudget,
            'deprecation_status_map' => $deprecationStatusMap,
        ]);
    }

    /**
     * Generates recipient org budget form.
     *
     * @param $id
     *
     * @return Form
     */
    public function formGenerator($id, $deprecationStatusMap = []): Form
    {
        $element = readOrganizationElementJsonSchema();
        $model['recipient_org_budget'] = $this->getRecipientOrgBudgetData($id) ?? [];
        $this->parentCollectionFormCreator->url = route('admin.organisation.recipient-org-budget.update', [$id]);

        return $this->parentCollectionFormCreator->editForm($model, $element['recipient_org_budget'], 'PUT', '/organisation', deprecationStatusMap: $deprecationStatusMap);
    }

    /**
     * Generates xml data for recipient org budget.
     *
     * @param $organization
     *
     * @return mixed
     */
    public function getXmlData($organization): array
    {
        $organizationData = [];
        $recipientOrgBudgetData = (array) $organization->recipient_org_budget;

        foreach ($recipientOrgBudgetData as $recipientOrgBudget) {
            $organizationData[] = [
                '@attributes'   => [
                    'status' => $recipientOrgBudget['status'],
                ],
                'recipient-org' => [
                    '@attributes' => [
                        'ref' => $recipientOrgBudget['recipient_org'][0]['ref'],
                    ],
                    'narrative'   => $this->buildNarrative($recipientOrgBudget['recipient_org'][0]['narrative']),
                ],
                'period-start'  => [
                    '@attributes' => [
                        'iso-date' => $recipientOrgBudget['period_start'][0]['date'],
                    ],
                ],
                'period-end'    => [
                    '@attributes' => [
                        'iso-date' => $recipientOrgBudget['period_end'][0]['date'],
                    ],
                ],
                'value'         => [
                    '@value'      => $recipientOrgBudget['value'][0]['amount'],
                    '@attributes' => [
                        'currency'   => $recipientOrgBudget['value'][0]['currency'],
                        'value-date' => $recipientOrgBudget['value'][0]['value_date'],
                    ],
                ],
                'budget-line' => $this->buildBudgetLine($recipientOrgBudget['budget_line']),
            ];
        }

        return $organizationData;
    }
}
