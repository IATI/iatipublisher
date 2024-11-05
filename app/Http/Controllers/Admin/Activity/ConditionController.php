<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Activity;

use App\Http\Controllers\Controller;
use App\Http\Requests\Activity\Condition\ConditionRequest;
use App\IATI\Services\Activity\ConditionService;
use App\IATI\Traits\EditFormTrait;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;

/**
 * Class ConditionController.
 */
class ConditionController extends Controller
{
    use EditFormTrait;

    /**
     * @var ConditionService
     */
    protected ConditionService $conditionService;

    /**
     * ConditionController Constructor.
     *
     * @param ConditionService $conditionService
     */
    public function __construct(ConditionService $conditionService)
    {
        $this->conditionService = $conditionService;
    }

    /**
     * Renders condition edit form.
     *
     * @param int $id
     *
     * @return View|RedirectResponse
     */
    public function edit(int $id): View|RedirectResponse
    {
        try {
            $element = getElementSchema('conditions');
            $activity = $this->conditionService->getActivityData($id);
            $deprecationStatusMap = Arr::get($activity->deprecation_status_map, 'condition', []);
            $form = $this->conditionService->formGenerator(
                id                        : $id,
                activityDefaultFieldValues: $activity->default_field_values ?? [],
                deprecationStatusMap      : $deprecationStatusMap
            );

            $hasData = (bool) Arr::get($activity, 'conditions', false);
            $formHeader = $this->getFormHeader(
                hasData    : $hasData,
                elementName: 'conditions',
                parentTitle: Arr::get($activity, 'title.0.narrative', 'Untitled')
            );
            $breadCrumbInfo = $this->basicBreadCrumbInfo($activity, 'conditions');

            $data = [
                'title'            => $element['label'],
                'name'             => 'conditions',
                'form_header'      => $formHeader,
                'bread_crumb_info' => $breadCrumbInfo,
            ];

            return view('admin.activity.condition.edit', compact('form', 'activity', 'data'));
        } catch (Exception $e) {
            logger()->error($e->getMessage());

            return redirect()->route('admin.activity.show', $id)->with(
                'error',
                'Error has occurred while rendering activity condition form.'
            );
        }
    }

    /**
     * Updates condition data.
     *
     * @param ConditionRequest $request
     * @param $id
     *
     * @return JsonResponse|RedirectResponse
     */
    public function update(ConditionRequest $request, $id): JsonResponse|RedirectResponse
    {
        try {
            $activityCondition = $request->except(['_token', '_method']);

            if (!$this->conditionService->update($id, $activityCondition)) {
                return redirect()->route('admin.activity.show', $id)->with('error', 'Error has occurred while updating activity condition.');
            }

            return redirect()->route('admin.activity.show', $id)->with('success', 'Activity condition updated successfully.');
        } catch (Exception $e) {
            logger()->error($e->getMessage());

            return redirect()->route('admin.activity.show', $id)->with('error', 'Error has occurred while updating activity condition.');
        }
    }
}
