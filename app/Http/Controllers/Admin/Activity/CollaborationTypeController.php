<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Activity;

use App\Http\Controllers\Controller;
use App\Http\Requests\Activity\CollaborationType\CollaborationTypeRequest;
use App\IATI\Services\Activity\CollaborationTypeService;
use App\IATI\Traits\EditFormTrait;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;

/**
 * Class CollaborationTypeController.
 */
class CollaborationTypeController extends Controller
{
    use EditFormTrait;

    /**
     * @var CollaborationTypeService
     */
    protected CollaborationTypeService $collaborationTypeService;

    /**
     * CollaborationTypeController Constructor.
     *
     * @param CollaborationTypeService $collaborationTypeService
     */
    public function __construct(CollaborationTypeService $collaborationTypeService)
    {
        $this->collaborationTypeService = $collaborationTypeService;
    }

    /**
     * Updates collaboration type edit form.
     *
     * @param int $id
     *
     * @return View|RedirectResponse
     */
    public function edit(int $id): View|RedirectResponse
    {
        try {
            $element = getElementSchema('collaboration_type');
            $activity = $this->collaborationTypeService->getActivityData($id);
            $deprecationStatusMap = Arr::get($activity->deprecation_status_map, 'collaboration_type', []);
            $form = $this->collaborationTypeService->formGenerator(
                id                        : $id,
                activityDefaultFieldValues: $activity->default_field_values ?? [],
                deprecationStatusMap      : $deprecationStatusMap
            );

            $hasData = (bool) Arr::get($activity, 'collaboration_type', false);
            $formHeader = $this->getFormHeader(
                hasData    : $hasData,
                elementName: 'collaboration_type',
                parentTitle: Arr::get($activity, 'title.0.narrative', getTranslatedUntitled())
            );
            $breadCrumbInfo = $this->basicBreadCrumbInfo($activity, 'collaboration_type');

            $data = [
                'title'            => $element['label'],
                'name'             => 'collaboration_type',
                'form_header'      => $formHeader,
                'bread_crumb_info' => $breadCrumbInfo,

            ];

            return view('admin.activity.collaborationType.edit', compact('form', 'activity', 'data'));
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
     * Updates collaboration type data.
     *
     * @param CollaborationTypeRequest $request
     * @param $id
     *
     * @return JsonResponse|RedirectResponse
     */
    public function update(CollaborationTypeRequest $request, $id): JsonResponse|RedirectResponse
    {
        try {
            $activityCollaborationType = $request->get('collaboration_type') !== null ? (int) $request->get('collaboration_type') : null;

            if (!$this->collaborationTypeService->update($id, $activityCollaborationType)) {
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
