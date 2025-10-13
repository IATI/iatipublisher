<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Activity;

use App\Http\Controllers\Controller;
use App\Http\Requests\Activity\Status\StatusRequest;
use App\IATI\Services\Activity\StatusService;
use App\IATI\Traits\EditFormTrait;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;

/**
 * Class StatusController.
 */
class StatusController extends Controller
{
    use EditFormTrait;

    /**
     * @var StatusService
     */
    protected StatusService $statusService;

    /**
     * StatusController Constructor.
     *
     * @param StatusService $statusService
     */
    public function __construct(StatusService $statusService)
    {
        $this->statusService = $statusService;
    }

    /**
     * Renders status edit form.
     *
     * @param int $id
     *
     * @return View|RedirectResponse|JsonResponse
     */
    public function edit(int $id): View|RedirectResponse|JsonResponse
    {
        try {
            $element = getElementSchema('activity_status');
            $activity = $this->statusService->getActivityData($id);
            $deprecationStatusMap = Arr::get($activity->deprecation_status_map, 'activity_status', []);
            $form = $this->statusService->formGenerator(
                id                  : $id,
                deprecationStatusMap: $deprecationStatusMap
            );

            $hasData = (bool) Arr::get($activity, 'activity_status', false);
            $formHeader = $this->getFormHeader(
                hasData    : $hasData,
                elementName: 'activity_status',
                parentTitle: Arr::get($activity, 'title.0.narrative', getTranslatedUntitled())
            );
            $breadCrumbInfo = $this->basicBreadCrumbInfo($activity, 'activity_status');

            $data = [
                'title'            => $element['label'],
                'name'             => 'activity_status',
                'form_header'      => $formHeader,
                'bread_crumb_info' => $breadCrumbInfo,
            ];

            return view('admin.activity.status.edit', compact('form', 'activity', 'data'));
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
     * Updates status data.
     *
     * @param StatusRequest $request
     * @param $id
     *
     * @return JsonResponse|RedirectResponse
     */
    public function update(StatusRequest $request, $id): JsonResponse|RedirectResponse
    {
        try {
            $activityStatus = $request->get('activity_status') !== null ? (int) $request->get('activity_status') : null;

            if (!$this->statusService->update($id, $activityStatus)) {
                $translatedMessage = trans('common/common.failed_to_update_data');

                return redirect()->route('admin.activity.show', $id)->with('error', $translatedMessage);
            }
            $translatedMessage = trans('common/common.updated_successfully');

            return redirect()->route('admin.activity.show', $id)->with('success', $translatedMessage);
        } catch (Exception $e) {
            logger()->error($e);
            $translatedMessage = trans('common/common.failed_to_update_data');

            return response()->json(
                ['success' => false, 'error' => $translatedMessage]
            );
        }
    }
}
