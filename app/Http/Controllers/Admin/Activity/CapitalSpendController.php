<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Activity;

use App\Http\Controllers\Controller;
use App\Http\Requests\Activity\CapitalSpend\CapitalSpendRequest;
use App\IATI\Services\Activity\CapitalSpendService;
use App\IATI\Traits\EditFormTrait;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;

/**
 * Class CapitalSpendController.
 */
class CapitalSpendController extends Controller
{
    use EditFormTrait;
    /**
     * @var CapitalSpendService
     */
    protected CapitalSpendService $capitalSpendService;

    /**
     * CapitalSpendController Constructor.
     *
     * @param CapitalSpendService $capitalSpendService
     */
    public function __construct(CapitalSpendService $capitalSpendService)
    {
        $this->capitalSpendService = $capitalSpendService;
    }

    /**
     * Renders capital spend edit form.
     *
     * @param int $id
     *
     * @return View|RedirectResponse
     */
    public function edit(int $id): View|RedirectResponse
    {
        try {
            $element = getElementSchema('capital_spend');
            $activity = $this->capitalSpendService->getActivityData($id);
            $deprecationStatusMap = Arr::get($activity->deprecation_status_map, 'capital_spend', []);
            $form = $this->capitalSpendService->formGenerator(
                id                  : $id,
                deprecationStatusMap: $deprecationStatusMap
            );

            $hasData = (bool) Arr::get($activity, 'capital_spend', false);
            $formHeader = $this->getFormHeader(
                hasData    : $hasData,
                elementName: 'capital_spend',
                parentTitle: Arr::get($activity, 'title.0.narrative', getTranslatedUntitled())
            );
            $breadCrumbInfo = $this->basicBreadCrumbInfo($activity, 'capital_spend');

            $data = [
                'title'            => $element['label'],
                'name'             => 'capital_spend',
                'form_header'      => $formHeader,
                'bread_crumb_info' => $breadCrumbInfo,
            ];

            return view('admin.activity.capitalSpend.edit', compact('form', 'activity', 'data'));
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
     * Updates capitals spend data.
     *
     * @param $id
     * @param CapitalSpendRequest $request
     *
     * @return JsonResponse|RedirectResponse
     */
    public function update($id, CapitalSpendRequest $request): JsonResponse|RedirectResponse
    {
        try {
            $activityCapitalSpend = $request->get('capital_spend') !== null ? (float) $request->get('capital_spend') : null;

            if (!$this->capitalSpendService->update($id, $activityCapitalSpend)) {
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
