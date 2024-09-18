<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Activity;

use App\Http\Controllers\Controller;
use App\Http\Requests\Activity\RelatedActivity\RelatedActivityRequest;
use App\IATI\Services\Activity\RelatedActivityService;
use App\IATI\Traits\EditFormTrait;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;

/**
 * Class RelatedActivityController.
 */
class RelatedActivityController extends Controller
{
    use EditFormTrait;

    /**
     * @var RelatedActivityService
     */
    protected RelatedActivityService $relatedActivityService;

    /**
     * RelatedActivityController Constructor.
     *
     * @param RelatedActivityService $relatedActivityService
     */
    public function __construct(RelatedActivityService $relatedActivityService)
    {
        $this->relatedActivityService = $relatedActivityService;
    }

    /**
     * @param int $id
     *
     * @return View|RedirectResponse
     */
    public function edit(int $id): View|RedirectResponse
    {
        try {
            $element = getElementSchema('related_activity');
            $activity = $this->relatedActivityService->getActivityData($id);
            $deprecationStatusMap = Arr::get($activity->deprecation_status_map, 'related_activity', []);
            $form = $this->relatedActivityService->formGenerator(
                id                  : $id,
                deprecationStatusMap: $deprecationStatusMap
            );

            $hasData = (bool) Arr::get($activity, 'related_activity', false);
            $formHeader = $this->getFormHeader(
                hasData    : $hasData,
                elementName: 'related_activity',
                parentTitle: Arr::get($activity, 'title.0.narrative', getTranslatedUntitled())
            );
            $breadCrumbInfo = $this->basicBreadCrumbInfo($activity, 'related_activity');

            $data = [
                'title'            => $element['label'],
                'name'             => 'related_activity',
                'form_header'      => $formHeader,
                'bread_crumb_info' => $breadCrumbInfo,
            ];

            return view('admin.activity.relatedActivity.edit', compact('form', 'activity', 'data'));
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
     * Updates related-activity data.
     *
     * @param RelatedActivityRequest $request
     * @param                        $id
     *
     * @return JsonResponse|RedirectResponse
     */
    public function update(RelatedActivityRequest $request, $id): JsonResponse|RedirectResponse
    {
        try {
            if (!$this->relatedActivityService->update($id, $request->all())) {
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
