<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Activity;

use App\Http\Controllers\Controller;
use App\Http\Requests\Activity\DefaultTiedStatus\DefaultTiedStatusRequest;
use App\IATI\Services\Activity\DefaultTiedStatusService;
use App\IATI\Traits\EditFormTrait;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;

/**
 * Class DefaultTiedStatusController.
 */
class DefaultTiedStatusController extends Controller
{
    use EditFormTrait;

    /**
     * @var DefaultTiedStatusService
     */
    protected DefaultTiedStatusService $defaultTiedStatusService;

    /**
     * DefaultTiedStatusController Constructor.
     *
     * @param DefaultTiedStatusService $defaultTiedStatusService
     */
    public function __construct(DefaultTiedStatusService $defaultTiedStatusService)
    {
        $this->defaultTiedStatusService = $defaultTiedStatusService;
    }

    /**
     * Renders default tied status edit form.
     *
     * @param int $id
     *
     * @return View|RedirectResponse
     */
    public function edit(int $id): View|RedirectResponse
    {
        try {
            $element = getElementSchema('default_tied_status');
            $activity = $this->defaultTiedStatusService->getActivityData($id);
            $deprecationStatusMap = Arr::get($activity->deprecation_status_map, 'default_tied_status', []);
            $form = $this->defaultTiedStatusService->formGenerator(
                id                        : $id,
                activityDefaultFieldValues: $activity->default_field_values ?? [],
                deprecationStatusMap      : $deprecationStatusMap
            );

            $hasData = (bool) Arr::get($activity, 'default_tied_status', false);
            $formHeader = $this->getFormHeader(
                hasData    : $hasData,
                elementName: 'default_tied_status',
                parentTitle: Arr::get($activity, 'title.0.narrative', getTranslatedUntitled())
            );
            $breadCrumbInfo = $this->basicBreadCrumbInfo($activity, 'default_tied_status');

            $data = [
                'title'            => $element['label'],
                'name'             => 'default_tied_status',
                'form_header'      => $formHeader,
                'bread_crumb_info' => $breadCrumbInfo,
            ];

            return view('admin.activity.defaultTiedStatus.edit', compact('form', 'activity', 'data'));
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
     * Updates default tied status data.
     *
     * @param DefaultTiedStatusRequest $request
     * @param                          $id
     *
     * @return JsonResponse|RedirectResponse
     */
    public function update(DefaultTiedStatusRequest $request, $id): JsonResponse|RedirectResponse
    {
        try {
            $activityDefaultTiedStatus = $request->get('default_tied_status') !== null ? (int) $request->get('default_tied_status') : null;

            if (!$this->defaultTiedStatusService->update($id, $activityDefaultTiedStatus)) {
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
