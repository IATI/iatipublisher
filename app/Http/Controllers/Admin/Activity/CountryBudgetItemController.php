<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Activity;

use App\Http\Controllers\Controller;
use App\Http\Requests\Activity\CountryBudgetItem\CountryBudgetItemRequest;
use App\IATI\Services\Activity\CountryBudgetItemService;
use App\IATI\Traits\EditFormTrait;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;

/**
 * Class CountryBudgetItemController.
 */
class CountryBudgetItemController extends Controller
{
    use EditFormTrait;

    /**
     * @var CountryBudgetItemService
     */
    protected CountryBudgetItemService $countryBudgetItemService;

    /**
     * CountryBudgetItemController Constructor.
     *
     * @param CountryBudgetItemService $countryBudgetItemService
     */
    public function __construct(CountryBudgetItemService $countryBudgetItemService)
    {
        $this->countryBudgetItemService = $countryBudgetItemService;
    }

    /**
     * Renders country budget item edit form.
     *
     * @param int $id
     *
     * @return View|RedirectResponse
     */
    public function edit(int $id): View|RedirectResponse
    {
        try {
            $element = getElementSchema('country_budget_items');
            $activity = $this->countryBudgetItemService->getActivityData($id);
            $deprecationStatusMap = Arr::get($activity->deprecation_status_map, 'country_budget_items', []);
            $form = $this->countryBudgetItemService->formGenerator(
                id                        : $id,
                activityDefaultFieldValues: $activity->default_field_values ?? [],
                deprecationStatusMap      : $deprecationStatusMap
            );

            $hasData = (bool) Arr::get($activity, 'country_budget_items', false);
            $formHeader = $this->getFormHeader(
                hasData    : $hasData,
                elementName: 'country_budget_items',
                parentTitle: Arr::get($activity, 'title.0.narrative', getTranslatedUntitled())
            );
            $breadCrumbInfo = $this->basicBreadCrumbInfo($activity, 'country_budget_items');

            $data = [
                'title'            => $element['label'],
                'name'             => 'country_budget_items',
                'form_header'      => $formHeader,
                'bread_crumb_info' => $breadCrumbInfo,
            ];

            return view('admin.activity.countryBudgetItem.edit', compact('form', 'activity', 'data'));
        } catch (Exception $e) {
            logger()->error($e->getMessage());
            $translatedMessage = trans('common/common.error_opening_data_entry_form');

            return redirect()->route('admin.activity.show', $id)->with(
                'error',
                $translatedMessage
            );
        }
    }

    /**
     * Updates country budget item data.
     *
     * @param CountryBudgetItemRequest $request
     * @param                          $id
     *
     * @return JsonResponse|RedirectResponse
     */
    public function update(CountryBudgetItemRequest $request, $id): JsonResponse|RedirectResponse
    {
        try {
            $activityCountryBudgetItem = $request->except(['_token', '_method']);

            if (!$this->countryBudgetItemService->update($id, $activityCountryBudgetItem)) {
                $translatedMessage = trans('common/common.error_opening_data_entry_form');

                return redirect()->route('admin.activity.show', $id)->with('error', $translatedMessage);
            }
            $translatedMessage = trans('common/common.updated_successfully');

            return redirect()->route('admin.activity.show', $id)->with('success', $translatedMessage);
        } catch (Exception $e) {
            logger()->error($e->getMessage());
            $translatedMessage = trans('common/common.error_opening_data_entry_form');

            return redirect()->route('admin.activity.show', $id)->with('error', $translatedMessage);
        }
    }
}
