<?php

declare(strict_types=1);

namespace App\IATI\Services\Organization;

use App\IATI\Elements\Builder\ParentCollectionFormCreator;
use App\IATI\Repositories\Organization\OrganizationRepository;
use App\IATI\Traits\OrganizationXmlBaseElements;
use Illuminate\Database\Eloquent\Model;
use Kris\LaravelFormBuilder\Form;

/**
 * Class TotalExpenditureService.
 */
class TotalExpenditureService
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
     * TotalExpenditureService constructor.
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
     * Returns total expenditure of an organization.
     *
     * @param int $organization_id
     *
     * @return array|null
     */
    public function getTotalExpenditureData(int $organization_id): ?array
    {
        return $this->organizationRepository->find($organization_id)->total_expenditure;
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
     * Updates Organization total expenditure.
     *
     * @param $id
     * @param $totalExpenditure
     *
     * @return bool
     */
    public function update($id, $totalExpenditure): bool
    {
        $totalExpenditure['total_expenditure'] = array_values($totalExpenditure['total_expenditure']);

        foreach ($totalExpenditure['total_expenditure'] as $key => $budget) {
            foreach ($budget['expense_line'] as $sub_index => $sub_element) {
                $totalExpenditure['total_expenditure'][$key]['expense_line'][$sub_index]['narrative'] = array_values($sub_element['narrative']);
            }

            $totalExpenditure['total_expenditure'][$key]['expense_line'] = array_values($totalExpenditure['total_expenditure'][$key]['expense_line']);
        }

        $totalExpenditure = $totalExpenditure['total_expenditure'];

        $organization = $this->organizationRepository->find($id);
        $deprecationStatusMap = $organization->deprecation_status_map;
        $deprecationStatusMap['total_expenditure'] = doesOrganisationTotalExpenditureHaveDeprecatedCode($totalExpenditure);

        return $this->organizationRepository->update($id, [
            'total_expenditure'      => $totalExpenditure,
            'deprecation_status_map' => $deprecationStatusMap,
        ]);
    }

    /**
     * Generates total expenditure form.
     *
     * @param $id
     */
    public function formGenerator($id, $deprecationStatusMap = []): Form
    {
        $element = readOrganizationElementJsonSchema();
        $model['total_expenditure'] = $this->getTotalExpenditureData($id) ?? [];
        $this->parentCollectionFormCreator->url = route('admin.organisation.total-expenditure.update', [$id]);

        return $this->parentCollectionFormCreator->editForm($model, $element['total_expenditure'], 'PUT', '/organisation');
    }

    /**
     * Generates total_expenditure xml data.
     *
     * @param $orgData
     *
     * @return array
     */
    public function getXmlData($orgData): array
    {
        $orgTotalExpenseData = [];
        $totalBudget = (array) $orgData->total_expenditure;

        foreach ($totalBudget as $orgTotalExpense) {
            $orgTotalExpenseData[] = [
                '@attributes'  => [
                ],
                'period-start' => [
                    '@attributes' => [
                        'iso-date' => $orgTotalExpense['period_start'][0]['date'],
                    ],
                ],
                'period-end'   => [
                    '@attributes' => [
                        'iso-date' => $orgTotalExpense['period_end'][0]['date'],
                    ],
                ],
                'value'        => [
                    '@value'      => $orgTotalExpense['value'][0]['amount'],
                    '@attributes' => [
                        'currency'   => $orgTotalExpense['value'][0]['currency'],
                        'value-date' => $orgTotalExpense['value'][0]['value_date'],
                    ],
                ],
                'expense-line' => $this->buildBudgetLine($orgTotalExpense['expense_line']),
            ];
        }

        return $orgTotalExpenseData;
    }
}
