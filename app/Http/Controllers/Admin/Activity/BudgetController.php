<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Activity;

use App\Http\Controllers\Controller;
use App\Http\Requests\Activity\Budget\BudgetRequest;
use App\IATI\Services\Activity\ActivityService;
use App\IATI\Services\Activity\BudgetService;
use App\IATI\Traits\EditFormTrait;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;

/**
 * Class BudgetController.
 */
class BudgetController extends Controller
{
    use EditFormTrait;

    /**
     * @var BudgetService
     */
    protected BudgetService $budgetService;

    /**
     * @var ActivityService
     */
    private ActivityService $activityService;

    /**
     * BudgetController Constructor.
     *
     * @param BudgetService   $budgetService
     * @param ActivityService $activityService
     */
    public function __construct(BudgetService $budgetService, ActivityService $activityService)
    {
        $this->budgetService = $budgetService;
        $this->activityService = $activityService;
    }

    /**
     * Renders budget edit form.
     *
     * @param int $id
     *
     * @return View|RedirectResponse
     */
    public function edit(int $id): View|RedirectResponse
    {
        try {
            $element = getElementSchema('budget');
            $activity = $this->activityService->getActivity($id);

            $deprecationStatusMap = Arr::get($activity->deprecation_status_map, 'budget', []);
            $form = $this->budgetService->formGenerator(
                id                        : $id,
                activityDefaultFieldValues: $activity->default_field_values ?? [],
                deprecationStatusMap      : $deprecationStatusMap
            );

            $hasData = (bool) Arr::get($activity, 'budget', false);
            $formHeader = $this->getFormHeader(
                hasData    : $hasData,
                elementName: 'budget',
                parentTitle: Arr::get($activity, 'title.0.narrative', getTranslatedUntitled())
            );
            $breadCrumbInfo = $this->basicBreadCrumbInfo($activity, 'budget');

            $data = [
                'title'            => $element['label'],
                'name'             => 'budget',
                'form_header'      => $formHeader,
                'bread_crumb_info' => $breadCrumbInfo,
            ];

            return view('admin.activity.budget.edit', compact('form', 'activity', 'data'));
        } catch (Exception $e) {
            logger()->error($e);
            $translatedMessage = trans('common/common.error_opening_data_entry_form');

            return redirect()->route('admin.activity.show', $id)->with(
                'error',
                $translatedMessage
            );
        }
    }

    /**
     * Updates budget data.
     *
     * @param BudgetRequest $request
     * @param               $id
     *
     * @return JsonResponse|RedirectResponse
     */
    public function update(BudgetRequest $request, $id): JsonResponse|RedirectResponse
    {
        try {
            $activityBudget = $request->all();

            if (!$this->budgetService->update($id, $activityBudget)) {
                $translatedMessage = trans('common/common.failed_to_update_data');

                return redirect()->route('admin.activity.show', $id)->with('error', $translatedMessage);
            }
            $translatedMessage = trans('common/common.updated_successfully');

            return redirect()->route('admin.activity.show', $id)->with('success', $translatedMessage);
        } catch (Exception $e) {
            logger()->error($e);
            $translatedMessage = trans('common/common.failed_to_update_data');

            return redirect()->route('admin.activity.show', $id)->with('error', $translatedMessage);
        }
    }
}
