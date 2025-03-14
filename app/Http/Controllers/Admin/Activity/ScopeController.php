<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Activity;

use App\Http\Controllers\Controller;
use App\Http\Requests\Activity\Scope\ScopeRequest;
use App\IATI\Services\Activity\ScopeService;
use App\IATI\Traits\EditFormTrait;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;

/**
 * Class ScopeController.
 */
class ScopeController extends Controller
{
    use EditFormTrait;

    /**
     * @var ScopeService
     */
    protected ScopeService $scopeService;

    /**
     * ScopeController Constructor.
     *
     * @param ScopeService $scopeService
     */
    public function __construct(ScopeService $scopeService)
    {
        $this->scopeService = $scopeService;
    }

    /**
     * Renders scope edit form.
     *
     * @param int $id
     *
     * @return View|RedirectResponse
     */
    public function edit(int $id): View|RedirectResponse
    {
        try {
            $element = getElementSchema('activity_scope');
            $activity = $this->scopeService->getActivityData($id);
            $deprecationStatusMap = Arr::get($activity->deprecation_status_map, 'activity_scope', []);
            $form = $this->scopeService->formGenerator(
                id                  : $id,
                deprecationStatusMap: $deprecationStatusMap
            );

            $hasData = (bool) Arr::get($activity, 'activity_scope', false);
            $formHeader = $this->getFormHeader(
                hasData    : $hasData,
                elementName: 'activity_scope',
                parentTitle: Arr::get($activity, 'title.0.narrative', getTranslatedUntitled())
            );
            $breadCrumbInfo = $this->basicBreadCrumbInfo($activity, 'activity_scope');

            $data = [
                'title'            => $element['label'],
                'name'             => 'activity_scope',
                'form_header'      => $formHeader,
                'bread_crumb_info' => $breadCrumbInfo,
            ];

            return view('admin.activity.scope.edit', compact('form', 'activity', 'data'));
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
     * Updates scope data.
     *
     * @param ScopeRequest $request
     * @param $id
     *
     * @return JsonResponse|RedirectResponse
     */
    public function update(ScopeRequest $request, $id): JsonResponse|RedirectResponse
    {
        try {
            $activityScope = $request->get('activity_scope') !== null ? (int) $request->get('activity_scope') : null;

            if (!$this->scopeService->update($id, $activityScope)) {
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
