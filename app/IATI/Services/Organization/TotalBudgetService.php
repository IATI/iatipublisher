<?php

declare(strict_types=1);

namespace App\IATI\Services\Organization;

use App\IATI\Elements\Builder\ParentCollectionFormCreator;
use App\IATI\Repositories\Organization\OrganizationRepository;
use App\IATI\Traits\OrganizationXmlBaseElements;
use Illuminate\Database\Eloquent\Model;
use Kris\LaravelFormBuilder\Form;

/**
 * Class TotalBudgetService.
 */
class TotalBudgetService
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
     * TotalBudgetService constructor.
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
     * Returns total budget of an organization.
     *
     * @param int $organization_id
     *
     * @return array|null
     */
    public function getTotalBudgetData(int $organization_id): ?array
    {
        return $this->organizationRepository->find($organization_id)->total_budget;
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
     * Updates Organization budget.
     *
     * @param $id
     * @param $totalBudget
     *
     * @return bool
     */
    public function update($id, $totalBudget): bool
    {
        $totalBudget['total_budget'] = array_values($totalBudget['total_budget']);

        foreach ($totalBudget['total_budget'] as $key => $budget) {
            foreach ($budget['budget_line'] as $sub_index => $sub_element) {
                $totalBudget['total_budget'][$key]['budget_line'][$sub_index]['narrative'] = array_values($sub_element['narrative']);
            }

            $totalBudget['total_budget'][$key]['budget_line'] = array_values($totalBudget['total_budget'][$key]['budget_line']);
        }

        $totalBudget = array_values($totalBudget['total_budget']);

        $organization = $this->organizationRepository->find($id);
        $deprecationStatusMap = $organization->deprecation_status_map;
        $deprecationStatusMap['total_budget'] = doesOrganisationTotalBudgetHaveDeprecatedCode($totalBudget);

        return $this->organizationRepository->update($id, [
            'total_budget'           => $totalBudget,
            'deprecation_status_map' => $deprecationStatusMap,
        ]);
    }

    /**
     * Generates total budget form.
     *
     * @param $id
     *
     * @return Form
     */
    public function formGenerator($id): Form
    {
        $element = readOrganizationElementJsonSchema();
        $model['total_budget'] = $this->getTotalBudgetData($id) ?? [];
        $this->parentCollectionFormCreator->url = route('admin.organisation.total-budget.update', [$id]);

        return $this->parentCollectionFormCreator->editForm($model, $element['total_budget'], 'PUT', '/organisation');
    }

    /**
     * Generates xml data for total budget.
     *
     * @param $orgData
     *
     * @return array
     */
    public function getXmlData($orgData): array
    {
        $orgTotalBudgetData = [];
        $totalBudget = (array) $orgData->total_budget;

        foreach ($totalBudget as $orgTotalBudget) {
            $orgTotalBudgetData[] = [
                '@attributes'  => [
                    'status' => $orgTotalBudget['total_budget_status'],
                ],
                'period-start' => [
                    '@attributes' => [
                        'iso-date' => $orgTotalBudget['period_start'][0]['date'],
                    ],
                ],
                'period-end'   => [
                    '@attributes' => [
                        'iso-date' => $orgTotalBudget['period_end'][0]['date'],
                    ],
                ],
                'value'        => [
                    '@value'      => $orgTotalBudget['value'][0]['amount'],
                    '@attributes' => [
                        'currency'   => $orgTotalBudget['value'][0]['currency'],
                        'value-date' => $orgTotalBudget['value'][0]['value_date'],
                    ],
                ],
                'budget-line' => $this->buildBudgetLine($orgTotalBudget['budget_line']),
            ];
        }

        return $orgTotalBudgetData;
    }
}
