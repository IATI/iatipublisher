<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Activity;

use App\Http\Controllers\Controller;
use App\Http\Requests\Activity\Tag\TagRequest;
use App\IATI\Services\Activity\TagService;
use App\IATI\Traits\EditFormTrait;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;

/**
 * Class TagController.
 */
class TagController extends Controller
{
    use EditFormTrait;

    /**
     * @var TagService
     */
    protected TagService $tagService;

    /**
     * TagController Constructor.
     *
     * @param TagService $tagService
     */
    public function __construct(TagService $tagService)
    {
        $this->tagService = $tagService;
    }

    /**
     * Renders tag edit form.
     *
     * @param int $id
     *
     * @return View|RedirectResponse
     */
    public function edit(int $id): View|RedirectResponse
    {
        try {
            $element = getElementSchema('tag');
            $activity = $this->tagService->getActivityData($id);
            $deprecationStatusMap = Arr::get($activity->deprecation_status_map, 'tag', []);
            $form = $this->tagService->formGenerator(
                id                        : $id,
                activityDefaultFieldValues: $activity->default_field_values ?? [],
                deprecationStatusMap      : $deprecationStatusMap
            );

            $hasData = (bool) Arr::get($activity, 'tag', false);
            $formHeader = $this->getFormHeader(
                hasData    : $hasData,
                elementName: 'tag',
                parentTitle: Arr::get($activity, 'title.0.narrative', 'Untitled')
            );
            $breadCrumbInfo = $this->basicBreadCrumbInfo($activity, 'tag');

            $data = [
                'title'            => $element['label'],
                'name'             => 'tag',
                'form_header'      => $formHeader,
                'bread_crumb_info' => $breadCrumbInfo,
            ];

            return view('admin.activity.tag.edit', compact('form', 'activity', 'data'));
        } catch (Exception $e) {
            logger()->error($e->getMessage());
            $translatedData = trans('activity_detail/tag_controller.error_has_occurred_while_opening_tag_form');

            return redirect()->route('admin.activity.show', $id)->with(
                'error',
                $translatedData
            );
        }
    }

    /**
     * Updates tag form.
     *
     * @param TagRequest $request
     * @param $id
     *
     * @return JsonResponse|RedirectResponse
     */
    public function update(TagRequest $request, $id): JsonResponse|RedirectResponse
    {
        try {
            if (!$this->tagService->update($id, $request->all())) {
                $translatedData = trans('activity_detail/tag_controller.error_has_occurred_while_updating_tag');

                return redirect()->route('admin.activity.show', $id)->with('error', $translatedData);
            }
            $translatedData = trans('activity_detail/tag_controller.tag_updated_successfully');

            return redirect()->route('admin.activity.show', $id)->with('success', $translatedData);
        } catch (Exception $e) {
            logger()->error($e->getMessage());
            $translatedData = trans('activity_detail/tag_controller.error_has_occurred_while_updating_tag');

            return redirect()->route('admin.activity.show', $id)->with('error', $translatedData);
        }
    }
}
