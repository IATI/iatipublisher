<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Activity;

use App\Http\Controllers\Controller;
use App\Http\Requests\Activity\PlannedDisbursement\PlannedDisbursementRequest;
use App\IATI\Services\Activity\PlannedDisbursementService;
use App\IATI\Traits\EditFormTrait;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;

/**
 * Class PlannedDisbursementController
 *.
 */
class PlannedDisbursementController extends Controller
{
    use EditFormTrait;

    /**
     * @var PlannedDisbursementService
     */
    protected PlannedDisbursementService $plannedDisbursementService;

    /**
     * PlannedDisbursementController Constructor.
     *
     * @param PlannedDisbursementService $plannedDisbursementService
     */
    public function __construct(PlannedDisbursementService $plannedDisbursementService)
    {
        $this->plannedDisbursementService = $plannedDisbursementService;
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
            $element = getElementSchema('planned_disbursement');
            $activity = $this->plannedDisbursementService->getActivityData($id);
            $deprecationStatusMap = Arr::get($activity->deprecation_status_map, 'planned_disbursement', []);
            $form = $this->plannedDisbursementService->formGenerator(
                id                        : $id,
                activityDefaultFieldValues: $activity->default_field_values ?? [],
                deprecationStatusMap      : $deprecationStatusMap
            );

            $hasData = (bool) Arr::get($activity, 'planned_disbursement', false);
            $formHeader = $this->getFormHeader(
                hasData    : $hasData,
                elementName: 'planned_disbursement',
                parentTitle: Arr::get($activity, 'title.0.narrative', getTranslatedUntitled())
            );
            $breadCrumbInfo = $this->basicBreadCrumbInfo($activity, 'planned_disbursement');

            $data = [
                'title'            => $element['label'],
                'name'             => 'planned_disbursement',
                'form_header'      => $formHeader,
                'bread_crumb_info' => $breadCrumbInfo,
            ];

            return view('admin.activity.plannedDisbursement.edit', compact('form', 'activity', 'data'));
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
     * Updates planned disbursement data.
     *
     * @param PlannedDisbursementRequest $request
     * @param $id
     *
     * @return JsonResponse|RedirectResponse
     */
    public function update(PlannedDisbursementRequest $request, $id): JsonResponse|RedirectResponse
    {
        try {
            if (!$this->plannedDisbursementService->update($id, $request->except(['_token', '_method']))) {
                $translatedMessage = trans('common/common.failed_to_update_data');

                return redirect()->route('admin.activity.show', $id)->with('error', $translatedMessage);
            }
            $translatedMessage = trans('common/common.updated_successfully');

            return redirect()->route('admin.activity.show', $id)->with('success', $translatedMessage);
        } catch (Exception $e) {
            logger()->error($e->getMessage());
            $translatedMessage = trans('common/common.failed_to_update_data');

            return redirect()->route('admin.activity.show', $id)->with('error', $translatedMessage);
        }
    }
}
