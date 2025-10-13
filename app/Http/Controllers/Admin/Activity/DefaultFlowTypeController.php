<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Activity;

use App\Http\Controllers\Controller;
use App\Http\Requests\Activity\DefaultFlowType\DefaultFlowTypeRequest;
use App\IATI\Services\Activity\DefaultFlowTypeService;
use App\IATI\Traits\EditFormTrait;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;

/**
 * Class DefaultFlowTypeController.
 */
class DefaultFlowTypeController extends Controller
{
    use EditFormTrait;

    /**
     * @var DefaultFlowTypeService
     */
    protected DefaultFlowTypeService $defaultFlowTypeService;

    /**
     * DefaultFlowTypeController Constructor.
     *
     * @param DefaultFlowTypeService $defaultFlowTypeService
     */
    public function __construct(DefaultFlowTypeService $defaultFlowTypeService)
    {
        $this->defaultFlowTypeService = $defaultFlowTypeService;
    }

    /**
     * Renders default flow type edit form.
     *
     * @param int $id
     *
     * @return View|RedirectResponse
     */
    public function edit(int $id): View|RedirectResponse
    {
        try {
            $element = getElementSchema('default_flow_type');
            $activity = $this->defaultFlowTypeService->getActivityData($id);
            $deprecationStatusMap = Arr::get($activity->deprecation_status_map, 'default_flow_type', []);
            $form = $this->defaultFlowTypeService->formGenerator(
                id                        : $id,
                activityDefaultFieldValues: $activity->default_field_values,
                deprecationStatusMap      : $deprecationStatusMap
            );

            $hasData = (bool) Arr::get($activity, 'default_flow_type', false);
            $formHeader = $this->getFormHeader(
                hasData    : $hasData,
                elementName: 'default_flow_type',
                parentTitle: Arr::get($activity, 'title.0.narrative', getTranslatedUntitled())
            );
            $breadCrumbInfo = $this->basicBreadCrumbInfo($activity, 'default_flow_type');

            $data = [
                'title'            => $element['label'],
                'name'             => 'default_flow_type',
                'form_header'      => $formHeader,
                'bread_crumb_info' => $breadCrumbInfo,
            ];

            return view('admin.activity.defaultFlowType.edit', compact('form', 'activity', 'data'));
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
     * Updates default flow type data.
     *
     * @param DefaultFlowTypeRequest $request
     * @param                        $id
     *
     * @return JsonResponse|RedirectResponse
     */
    public function update(DefaultFlowTypeRequest $request, $id): JsonResponse|RedirectResponse
    {
        try {
            $activityDefaultFlowType = $request->get('default_flow_type') !== null ? (int) $request->get('default_flow_type') : null;

            if (!$this->defaultFlowTypeService->update($id, ['code' => $activityDefaultFlowType])) {
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
