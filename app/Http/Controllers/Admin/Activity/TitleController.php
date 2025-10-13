<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Activity;

use App\Http\Controllers\Controller;
use App\Http\Requests\Activity\Title\TitleRequest;
use App\IATI\Services\Activity\TitleService;
use App\IATI\Traits\EditFormTrait;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;

/**
 * Class TitleController.
 */
class TitleController extends Controller
{
    use EditFormTrait;

    /**
     * @var TitleService
     */
    protected TitleService $titleService;

    /**
     * TitleController Constructor.
     *
     * @param TitleService $titleService
     */
    public function __construct(TitleService $titleService)
    {
        $this->titleService = $titleService;
    }

    /**
     * Renders title edit form.
     *
     * @param int $id
     *
     * @return Factory|View|RedirectResponse|Application
     */
    public function edit(int $id): Factory|View|RedirectResponse|Application
    {
        try {
            $element = getElementSchema('title');
            $activity = $this->titleService->getActivityData($id);
            $deprecationStatusMap = Arr::get($activity->deprecation_status_map, 'title', []);
            $form = $this->titleService->formGenerator(
                id                        : $id,
                activityDefaultFieldValues: $activity->default_field_values ?? [],
                deprecationStatusMap      : $deprecationStatusMap
            );

            $hasData = (bool) Arr::get($activity, 'title', false);
            $formHeader = $this->getFormHeader(
                hasData    : $hasData,
                elementName: 'title',
                parentTitle: Arr::get($activity, 'title.0.narrative', getTranslatedUntitled())
            );
            $breadCrumbInfo = $this->basicBreadCrumbInfo($activity, 'title');

            $data = [
                'title'            => $element['label'],
                'name'             => 'title',
                'form_header'      => $formHeader,
                'bread_crumb_info' => $breadCrumbInfo,
            ];

            return view('admin.activity.title.edit', compact('form', 'activity', 'data'));
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
     * Updates activity title data.
     *
     * @param TitleRequest $request
     * @param              $id
     *
     * @return JsonResponse|RedirectResponse
     */
    public function update(TitleRequest $request, $id): JsonResponse|RedirectResponse
    {
        try {
            if (!$this->titleService->update($id, $request->all())) {
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
