<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Activity;

use App\Http\Controllers\Controller;
use App\Http\Requests\Activity\LegacyData\LegacyDataRequest;
use App\IATI\Services\Activity\LegacyDataService;
use App\IATI\Traits\EditFormTrait;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;

/**
 * Class LegacyDataController.
 */
class LegacyDataController extends Controller
{
    use EditFormTrait;

    /**
     * @var LegacyDataService
     */
    protected LegacyDataService $activityLegacyDataService;

    /**
     * LegacyDataController Constructor.
     *
     * @param LegacyDataService $activityLegacyDataService
     */
    public function __construct(LegacyDataService $activityLegacyDataService)
    {
        $this->activityLegacyDataService = $activityLegacyDataService;
    }

    /**
     * Renders legacy data form.
     *
     * @param int $id
     *
     * @return View|RedirectResponse
     */
    public function edit(int $id): View|RedirectResponse
    {
        try {
            $element = getElementSchema('legacy_data');
            $activity = $this->activityLegacyDataService->getActivityData($id);
            $deprecationStatusMap = Arr::get($activity->deprecation_status_map, 'legacy', []);
            $form = $this->activityLegacyDataService->formGenerator(
                id                  : $id,
                deprecationStatusMap: $deprecationStatusMap
            );

            $hasData = (bool) Arr::get($activity, 'legacy_data', false);
            $formHeader = $this->getFormHeader(
                hasData    : $hasData,
                elementName: 'legacy_data',
                parentTitle: Arr::get($activity, 'title.0.narrative', 'Untitled')
            );
            $breadCrumbInfo = $this->basicBreadCrumbInfo($activity, 'legacy_data');

            $data = [
                'title'            => $element['label'],
                'name'             => 'legacy_data',
                'form_header'      => $formHeader,
                'bread_crumb_info' => $breadCrumbInfo,
            ];

            return view('admin.activity.legacyData.edit', compact('form', 'activity', 'data'));
        } catch (Exception $e) {
            logger()->error($e->getMessage());
            $translatedMessage = trans('activity_detail/legacy_data_controller.error_has_occurred_while_rendering_legacy_data_form');

            return redirect()->route('admin.activity.show', $id)->with(
                'error',
                $translatedMessage
            );
        }
    }

    /**
     * Updates legacy data.
     *
     * @param LegacyDataRequest $request
     * @param $id
     *
     * @return JsonResponse|RedirectResponse
     */
    public function update(LegacyDataRequest $request, $id): JsonResponse|RedirectResponse
    {
        try {
            if ($this->activityLegacyDataService->update($id, $request->all())) {
                $translatedMessage = trans('activity_detail/legacy_data_controller.legacy_data_updated_successfully');

                return redirect()->route('admin.activity.show', $id)->with('success', $translatedMessage);
            }
            $translatedMessage = trans('activity_detail/legacy_data_controller.error_has_occurred_while_updating_legacy_data');

            return redirect()->route('admin.activity.show', $id)->with('error', $translatedMessage);
        } catch (Exception $e) {
            logger()->error($e->getMessage());
            $translatedMessage = trans('activity_detail/legacy_data_controller.error_has_occurred_while_updating_legacy_data');

            return redirect()->route('admin.activity.show', $id)->with('error', $translatedMessage);
        }
    }
}
