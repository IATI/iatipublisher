<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Activity;

use App\Http\Controllers\Controller;
use App\Http\Requests\Activity\HumanitarianScope\HumanitarianScopeRequest;
use App\IATI\Services\Activity\HumanitarianScopeService;
use App\IATI\Traits\EditFormTrait;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;

/**
 * Class HumanitarianScopeController.
 */
class HumanitarianScopeController extends Controller
{
    use EditFormTrait;

    /**
     * @var HumanitarianScopeService
     */
    protected HumanitarianScopeService $humanitarianScopeService;

    /**
     * HumanitarianScopeController Constructor.
     *
     * @param HumanitarianScopeService $humanitarianScopeService
     */
    public function __construct(HumanitarianScopeService $humanitarianScopeService)
    {
        $this->humanitarianScopeService = $humanitarianScopeService;
    }

    /**
     * Renders humanitarian edit form.
     *
     * @param int $id
     *
     * @return View|RedirectResponse
     */
    public function edit(int $id): View|RedirectResponse
    {
        try {
            $element = getElementSchema('humanitarian_scope');
            $activity = $this->humanitarianScopeService->getActivityData($id);
            $deprecationStatusMap = Arr::get($activity->deprecation_status_map, 'humanitarian_scope', []);
            $form = $this->humanitarianScopeService->formGenerator(
                id                        : $id,
                activityDefaultFieldValues: $activity->default_field_values ?? [],
                deprecationStatusMap      : $deprecationStatusMap
            );

            $hasData = (bool) Arr::get($activity, 'humanitarian_scope', false);
            $formHeader = $this->getFormHeader(
                hasData    : $hasData,
                elementName: 'humanitarian_scope',
                parentTitle: Arr::get($activity, 'title.0.narrative', getTranslatedUntitled())
            );
            $breadCrumbInfo = $this->basicBreadCrumbInfo($activity, 'humanitarian_scope');

            $data = [
                'title'            => $element['label'],
                'name'             => 'humanitarian_scope',
                'form_header'      => $formHeader,
                'bread_crumb_info' => $breadCrumbInfo,
            ];

            return view('admin.activity.humanitarianScope.edit', compact('form', 'activity', 'data'));
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
     * Updates humanitarian scope form.
     *
     * @param HumanitarianScopeRequest $request
     * @param                          $id
     *
     * @return JsonResponse|RedirectResponse
     */
    public function update(HumanitarianScopeRequest $request, $id): JsonResponse|RedirectResponse
    {
        try {
            if ($this->humanitarianScopeService->update($id, $request->except(['_token', '_method']))) {
                $translatedMessage = trans('common/common.updated_successfully');

                return redirect()->route('admin.activity.show', $id)->with('success', $translatedMessage);
            }

            $translatedMessage = trans('common/common.failed_to_update_data');

            return redirect()->route('admin.activity.show', $id)->with('error', $translatedMessage);
        } catch (Exception $e) {
            logger()->error($e->getMessage());
            $translatedMessage = trans('common/common.updated_successfully');

            return redirect()->route('admin.activity.show', $id)->with('error', $translatedMessage);
        }
    }
}
