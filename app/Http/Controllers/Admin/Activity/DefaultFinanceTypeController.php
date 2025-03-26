<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Activity;

use App\Http\Controllers\Controller;
use App\Http\Requests\Activity\DefaultFinanceType\DefaultFinanceTypeRequest;
use App\IATI\Services\Activity\DefaultFinanceTypeService;
use App\IATI\Traits\EditFormTrait;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;

/**
 * Class DefaultFinanceTypeController.
 */
class DefaultFinanceTypeController extends Controller
{
    use EditFormTrait;

    /**
     * @var DefaultFinanceTypeService
     */
    protected DefaultFinanceTypeService $defaultFinanceTypeService;

    /**
     * DefaultFinanceTypeController Constructor.
     *
     * @param DefaultFinanceTypeService $defaultFinanceTypeService
     */
    public function __construct(DefaultFinanceTypeService $defaultFinanceTypeService)
    {
        $this->defaultFinanceTypeService = $defaultFinanceTypeService;
    }

    /**
     * Renders default finance type edit form.
     *
     * @param int $id
     *
     * @return View|RedirectResponse
     */
    public function edit(int $id): View|RedirectResponse
    {
        try {
            $element = getElementSchema('default_finance_type');
            $activity = $this->defaultFinanceTypeService->getActivityData($id);
            $deprecationStatusMap = Arr::get($activity->deprecation_status_map, 'default_finance_type', []);
            $form = $this->defaultFinanceTypeService->formGenerator(
                id                        : $id,
                activityDefaultFieldValues: $activity->default_field_values ?? [],
                deprecationStatusMap      : $deprecationStatusMap
            );

            $hasData = (bool) Arr::get($activity, 'default_finance_type', false);
            $formHeader = $this->getFormHeader(
                hasData    : $hasData,
                elementName: 'default_finance_type',
                parentTitle: Arr::get($activity, 'title.0.narrative', getTranslatedUntitled())
            );
            $breadCrumbInfo = $this->basicBreadCrumbInfo($activity, 'default_finance_type');

            $data = [
                'title'            => $element['label'],
                'name'             => 'default_finance_type',
                'form_header'      => $formHeader,
                'bread_crumb_info' => $breadCrumbInfo,
            ];

            return view('admin.activity.defaultFinanceType.edit', compact('form', 'activity', 'data'));
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
     * Updates default finance type data.
     *
     * @param DefaultFinanceTypeRequest $request
     * @param $id
     *
     * @return JsonResponse|RedirectResponse
     */
    public function update(DefaultFinanceTypeRequest $request, $id): JsonResponse|RedirectResponse
    {
        try {
            $activityDefaultFinanceType = $request->get('default_finance_type') !== null ? (int) $request->get('default_finance_type') : null;

            if (!$this->defaultFinanceTypeService->update($id, ['code' => $activityDefaultFinanceType])) {
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
