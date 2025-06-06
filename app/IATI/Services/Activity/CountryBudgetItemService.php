<?php

declare(strict_types=1);

namespace App\IATI\Services\Activity;

use App\IATI\Elements\Builder\MultilevelSubElementFormCreator;
use App\IATI\Models\Activity\Activity;
use App\IATI\Repositories\Activity\ActivityRepository;
use App\IATI\Traits\XmlBaseElement;
use Illuminate\Support\Arr;
use Kris\LaravelFormBuilder\Form;

/**
 * Class CountryBudgetItemService.
 */
class CountryBudgetItemService
{
    use XmlBaseElement;

    /**
     * @var ActivityRepository
     */
    protected ActivityRepository $activityRepository;

    /**
     * @var MultilevelSubElementFormCreator
     */
    protected MultilevelSubElementFormCreator $multilevelSubElementFormCreator;

    /**
     * CountryBudgetItemService constructor.
     *
     * @param ActivityRepository              $activityRepository
     * @param MultilevelSubElementFormCreator $multilevelSubElementFormCreator
     */
    public function __construct(ActivityRepository $activityRepository, MultilevelSubElementFormCreator $multilevelSubElementFormCreator)
    {
        $this->activityRepository = $activityRepository;
        $this->multilevelSubElementFormCreator = $multilevelSubElementFormCreator;
    }

    /**
     * Returns country budget item data of an activity.
     *
     * @param int $activity_id
     *
     * @return array|null
     */
    public function getCountryBudgetItemData(int $activity_id): ?array
    {
        return $this->activityRepository->find($activity_id)->country_budget_items;
    }

    /**
     * Returns activity object.
     *
     * @param $id
     *
     * @return object
     */
    public function getActivityData($id): object
    {
        return $this->activityRepository->find($id);
    }

    /**
     * Updates activity country budget item.
     *
     * @param $id
     * @param $activityCountryBudgetItem
     *
     * @return bool
     */
    public function update($id, $activityCountryBudgetItem): bool
    {
        foreach ($activityCountryBudgetItem['budget_item'] as $key => $budget_item) {
            $activityCountryBudgetItem['budget_item'][$key]['description'][0]['narrative'] = array_values($budget_item['description'][0]['narrative']);
        }

        $activityCountryBudgetItem['budget_item'] = array_values($activityCountryBudgetItem['budget_item']);

        $activity = $this->activityRepository->find($id);
        $deprecationStatusMap = $activity->deprecation_status_map;
        $deprecationStatusMap['country_budget_items'] = doesCountryBudgetItemsHaveDeprecatedCode($activityCountryBudgetItem);

        return $this->activityRepository->update($id, [
            'country_budget_items'   => $activityCountryBudgetItem,
            'deprecation_status_map' => $deprecationStatusMap,
        ]);
    }

    /**
     * Generates country budget form.
     *
     * @param $id
     *
     * @return Form
     * @throws \JsonException
     */
    public function formGenerator($id, $activityDefaultFieldValues, $deprecationStatusMap = []): Form
    {
        $element = getElementSchema('country_budget_items');
        $model = $this->getCountryBudgetItemData($id) ?: [];
        $this->multilevelSubElementFormCreator->url = route('admin.activity.country-budget-items.update', [$id]);

        return $this->multilevelSubElementFormCreator->editForm($model, $element, 'PUT', '/activity/' . $id, overRideDefaultFieldValue: $activityDefaultFieldValues, deprecationStatusMap: $deprecationStatusMap);
    }

    /**
     * Returns data in required xml array format.
     *
     * @param Activity $activity
     *
     * @return array
     */
    public function getXmlData(Activity $activity): array
    {
        $activityData = [];
        $countryBudgetItem = (array) $activity->country_budget_items;

        if (count($countryBudgetItem)) {
            $activityData[] = [
                '@attributes' => [
                    'vocabulary' => Arr::get($countryBudgetItem, 'country_budget_vocabulary', null),
                ],
                'budget-item' => $this->buildBudgetItem(
                    Arr::get($countryBudgetItem, 'budget_item', []),
                ),
            ];
        }

        return $activityData;
    }

    /**
     * Returns array of xml budget items.
     *
     * @param $budgetItems
     *
     * @return array
     */
    private function buildBudgetItem($budgetItems): array
    {
        $budgetItemData = [];

        if (count($budgetItems)) {
            foreach ($budgetItems as $budgetItem) {
                $budgetItemData[] = [
                    '@attributes' => [
                        'code'       => Arr::get(
                            $budgetItem,
                            'code',
                            null
                        ),
                        'percentage' => Arr::get($budgetItem, 'percentage') ?? 100,
                    ],
                    'description' => [
                        'narrative' => $this->buildNarrative(
                            Arr::get($budgetItem, 'description.0.narrative', null)
                        ),
                    ],
                ];
            }
        }

        return $budgetItemData;
    }
}
