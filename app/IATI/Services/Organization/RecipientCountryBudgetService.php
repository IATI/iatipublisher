<?php

declare(strict_types=1);

namespace App\IATI\Services\Organization;

use App\IATI\Elements\Builder\ParentCollectionFormCreator;
use App\IATI\Repositories\Organization\OrganizationRepository;
use App\IATI\Traits\OrganizationXmlBaseElements;
use Illuminate\Database\Eloquent\Model;
use Kris\LaravelFormBuilder\Form;

/**
 * Class RecipientCountryBudgetService.
 */
class RecipientCountryBudgetService
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
     * RecipientCountryBudgetService constructor.
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
     * Returns recipient country budget of an organization.
     *
     * @param int $organization_id
     *
     * @return array|null
     */
    public function getRecipientCountryBudgetData(int $organization_id): ?array
    {
        return $this->organizationRepository->find($organization_id)->recipient_country_budget;
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
     * Updates recipient country budget.
     *
     * @param $id
     * @param $recipientCountryBudget
     *
     * @return bool
     */
    public function update($id, $recipientCountryBudget): bool
    {
        $recipientCountryBudget['recipient_country_budget'] = array_values($recipientCountryBudget['recipient_country_budget']);

        foreach ($recipientCountryBudget['recipient_country_budget'] as $key => $budget) {
            foreach ($budget['recipient_country'] as $sub_index => $sub_element) {
                $recipientCountryBudget['recipient_country_budget'][$key]['recipient_country'][$sub_index]['narrative'] = array_values($sub_element['narrative']);
            }

            foreach ($budget['budget_line'] as $sub_index => $sub_element) {
                $recipientCountryBudget['recipient_country_budget'][$key]['budget_line'][$sub_index]['narrative'] = array_values($sub_element['narrative']);
            }

            $recipientCountryBudget['recipient_country_budget'][$key]['budget_line'] = array_values($recipientCountryBudget['recipient_country_budget'][$key]['budget_line']);
            $recipientCountryBudget['recipient_country_budget'][$key]['recipient_country'] = array_values($recipientCountryBudget['recipient_country_budget'][$key]['recipient_country']);
        }

        $recipientCountryBudget = $recipientCountryBudget['recipient_country_budget'];

        $organization = $this->organizationRepository->find($id);
        $deprecationStatusMap = $organization->deprecation_status_map;
        $deprecationStatusMap['recipient_country_budget'] = doesOrganisationRecipientCountryBudgetHaveDeprecatedCode($recipientCountryBudget);

        return $this->organizationRepository->update($id, [
            'recipient_country_budget' => $recipientCountryBudget,
            'deprecation_status_map'   => $deprecationStatusMap,
        ]);
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
        $model['recipient_country_budget'] = $this->getRecipientCountryBudgetData($id) ?? [];
        $this->parentCollectionFormCreator->url = route('admin.organisation.recipient-country-budget.update', [$id]);

        return $this->parentCollectionFormCreator->editForm($model, $element['recipient_country_budget'], 'PUT', '/organisation', deprecationStatusMap: $deprecationStatusMap);
    }

    /**
     * Generates xml data for recipient_country_budget.
     *
     * @param OrganizationData $organizationData
     *
     * @return array
     */
    public function getXmlData($organizationData): array
    {
        $orgRecipientCountryData = [];
        $recipientCountryBudget = (array) $organizationData->recipient_country_budget;
        foreach ($recipientCountryBudget as $orgRecipientCountry) {
            $orgRecipientCountryData[] = [
                '@attributes'   => [
                    'status' => $orgRecipientCountry['status'],
                ],
                'recipient-country' => [
                    '@attributes' => [
                        'code' => $orgRecipientCountry['recipient_country'][0]['code'],
                    ],
                    'narrative' => $this->buildNarrative($orgRecipientCountry['recipient_country'][0]['narrative']),
                ],
                'period-start' => [
                    '@attributes' => [
                        'iso-date' => $orgRecipientCountry['period_start'][0]['date'],
                    ],
                ],
                'period-end' => [
                    '@attributes' => [
                        'iso-date' => $orgRecipientCountry['period_end'][0]['date'],
                    ],
                ],
                'value' => [
                    '@value'      => $orgRecipientCountry['value'][0]['amount'],
                    '@attributes' => [
                        'currency' => $orgRecipientCountry['value'][0]['currency'],
                        'value-date' => $orgRecipientCountry['value'][0]['value_date'],
                    ],
                ],
                'budget-line' => $this->buildBudgetLine($orgRecipientCountry['budget_line']),
            ];
        }

        return $orgRecipientCountryData;
    }
}
