<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Activity;

use App\Http\Controllers\Controller;
use App\Http\Requests\Activity\DefaultAidType\DefaultAidTypeRequest;
use App\IATI\Services\Activity\DefaultAidTypeService;
use App\IATI\Traits\EditFormTrait;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;

/**
 * Class DefaultAidTypeController.
 */
class DefaultAidTypeController extends Controller
{
    use EditFormTrait;

    /**
     * @var DefaultAidTypeService
     */
    protected DefaultAidTypeService $defaultAidTypeService;

    /**
     * DefaultAidTypeController Constructor.
     *
     * @param DefaultAidTypeService $defaultAidTypeService
     */
    public function __construct(DefaultAidTypeService $defaultAidTypeService)
    {
        $this->defaultAidTypeService = $defaultAidTypeService;
    }

    /**
     * Renders aid type edit form.
     *
     * @param int $id
     *
     * @return View|RedirectResponse
     */
    public function edit(int $id): View|RedirectResponse
    {
        try {
            $element = getElementSchema('default_aid_type');
            $activity = $this->defaultAidTypeService->getActivityData($id);
            $deprecationStatusMap = Arr::get($activity->deprecation_status_map, 'default_aid_type', []);
            $form = $this->defaultAidTypeService->formGenerator(
                id                        : $id,
                activityDefaultFieldValues: $activity->default_field_values ?? [],
                deprecationStatusMap      : $deprecationStatusMap
            );

            $hasData = (bool) Arr::get($activity, 'default_aid_type', false);
            $formHeader = $this->getFormHeader(
                hasData    : $hasData,
                elementName: 'default_aid_type',
                parentTitle: Arr::get($activity, 'title.0.narrative', getTranslatedUntitled())
            );
            $breadCrumbInfo = $this->basicBreadCrumbInfo($activity, 'default_aid_type');

            $data = [
                'title'            => $element['label'],
                'name'             => 'default_aid_type',
                'form_header'      => $formHeader,
                'bread_crumb_info' => $breadCrumbInfo,
            ];

            return view('admin.activity.defaultAidType.edit', compact('form', 'activity', 'data'));
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
     * Updates default aid type data.
     * @param DefaultAidTypeRequest $request
     * @param $id
     *
     * @return JsonResponse|RedirectResponse
     */
    public function update(DefaultAidTypeRequest $request, $id): JsonResponse|RedirectResponse
    {
        try {
            $activityDefaultAidType = $request->all();

            if (!$this->defaultAidTypeService->update($id, $activityDefaultAidType)) {
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
