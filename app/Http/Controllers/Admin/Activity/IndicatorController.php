<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Activity;

use App\Http\Controllers\Controller;
use App\Http\Requests\Activity\Indicator\IndicatorRequest;
use App\IATI\Services\Activity\ActivityService;
use App\IATI\Services\Activity\IndicatorService;
use App\IATI\Services\Activity\PeriodService;
use App\IATI\Services\Activity\ResultService;
use App\IATI\Traits\EditFormTrait;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Session;

/**
 * IndicatorController Class.
 */
class IndicatorController extends Controller
{
    use EditFormTrait;

    /**
     * @var ResultService
     */
    protected ResultService $resultService;

    /**
     * @var IndicatorService
     */
    protected IndicatorService $indicatorService;

    /**
     * @var PeriodService
     */
    protected PeriodService $periodService;

    /**
     * @var ActivityService
     */
    protected ActivityService $activityService;

    /**
     * IndicatorController Constructor.
     *
     * @param ResultService    $resultService
     * @param IndicatorService $indicatorService
     * @param PeriodService    $periodService
     * @param ActivityService  $activityService
     */
    public function __construct(
        ResultService $resultService,
        IndicatorService $indicatorService,
        PeriodService $periodService,
        ActivityService $activityService
    ) {
        $this->indicatorService = $indicatorService;
        $this->periodService = $periodService;
        $this->resultService = $resultService;
        $this->activityService = $activityService;
    }

    /**
     * Renders indicator listing page.
     *
     * @param $resultId
     *
     * @return View|RedirectResponse
     */
    public function index($resultId): View|RedirectResponse
    {
        try {
            $result = $this->resultService->getResult($resultId);
            $activity = $result->activity;
            $parentData = [
                'result' => [
                    'id'    => $resultId,
                    'title' => $result['result']['title'][0]['narrative'],
                ],
            ];
            $indicators = $this->indicatorService->getIndicators($resultId);
            $types = getIndicatorTypes();
            $toast = generateToastData();

            return view('admin.activity.indicator.indicator', compact('activity', 'parentData', 'indicators', 'types', 'toast'));
        } catch (Exception $e) {
            logger()->error($e->getMessage());

            $translatedMessage = trans('common/common.error_has_occurred_while_rendering_activity_transactions_listing');

            return redirect()->route('admin.activity.result.index', $resultId)->with(
                'error',
                $translatedMessage
            );
        }
    }

    /**
     * Returns paginated indicator.
     *
     * @param int $resultId
     * @param int $page
     *
     * @return JsonResponse
     */
    public function getPaginatedIndicators(int $resultId, int $page = 1): JsonResponse
    {
        try {
            $indicator = $this->indicatorService->getPaginatedIndicator($resultId, $page);
            $translatedMessage = 'Indicators Fetched Successfully';

            return response()->json([
                'success' => true,
                'message' => $translatedMessage,
                'data'    => $indicator,
            ]);
        } catch (Exception $e) {
            logger()->error($e->getMessage());

            return response()->json(['success' => false, 'message' => 'Error occurred while fetching the data.']);
        }
    }

    /**
     * Renders indicator create page.
     *
     * @param $resultId
     *
     * @return Factory|View|RedirectResponse|Application
     */
    public function create($resultId): Factory|View|RedirectResponse|Application
    {
        try {
            $element = getElementSchema('indicator');
            $result = $this->resultService->getResult($resultId);
            $activity = $result->activity;
            $form = $this->indicatorService->createFormGenerator($resultId);

            $formHeader = $this->getFormHeader(
                hasData    : true,
                elementName: 'indicator',
                parentTitle: Arr::get($result, 'result.title.0.narrative.0.narrative', getTranslatedUntitled())
            );
            $breadCrumbInfo = $this->indicatorBreadCrumbInfo(
                activity : $activity,
                result   : $result,
                indicator: null,
            );

            $data = [
                'status'           => false,
                'title'            => $element['label'],
                'name'             => 'indicator',
                'form_header'      => $formHeader,
                'bread_crumb_info' => $breadCrumbInfo,
            ];

            return view('admin.activity.indicator.edit', compact('form', 'activity', 'data'));
        } catch (Exception $e) {
            logger()->error($e->getMessage());
            $translatedMessage = trans('common/common.error_opening_data_entry_form');

            return redirect()->route('admin.result.indicator.index', [$resultId])->with(
                'error',
                $translatedMessage
            );
        }
    }

    /**
     * Creates new indicator.
     *
     * @param IndicatorRequest $request
     * @param                  $resultId
     *
     * @return RedirectResponse
     */
    public function store(IndicatorRequest $request, $resultId): RedirectResponse
    {
        try {
            $indicatorData = $request->except(['_token']);
            $result = $this->resultService->getResult($resultId);
            $indicator = $this->indicatorService->create([
                'result_id' => $resultId,
                'indicator' => $indicatorData,
                'default_field_values'=>$result->activity->default_field_values,
            ]);

            $translatedMessage = trans('activity_detail/indicator_controller.result_indicator_created_successfully');

            return redirect()->route('admin.result.indicator.show', [$resultId, $indicator['id']])->with(
                'success',
                $translatedMessage
            );
        } catch (Exception $e) {
            logger()->error($e->getMessage());

            return redirect()->route('admin.result.indicator.index', $resultId)->with(
                'error',
                'Error has occurred while creating result indicator.'
            );
        }
    }

    /**
     * Renders indicator detail page.
     *
     * @param $resultId
     * @param $indicatorId
     *
     * @return Factory|View|RedirectResponse|Application
     */
    public function show($resultId, $indicatorId): Factory|View|RedirectResponse|Application
    {
        try {
            $indicator = $this->indicatorService->getIndicator($indicatorId);
            $result = $indicator->result;
            $resultTitle = $result['result']['title'];
            $activity = $result->activity;
            $period = $this->periodService->getPeriods($indicatorId)->toArray();
            $element = getElementSchema('indicator');
            $types = getIndicatorTypes();
            $toast = generateToastData();

            return view('admin.activity.indicator.detail', compact('activity', 'resultTitle', 'indicator', 'period', 'types', 'toast', 'element'));
        } catch (Exception $e) {
            logger()->error($e->getMessage());
            $translatedMessage = trans('common/common.error_has_occurred_while_rending_result_detail_page');

            return redirect()->route('admin.result.indicator.index', $resultId)->with(
                'error',
                $translatedMessage
            );
        }
    }

    /**
     * Renders indicator edit form.
     *
     * @param $resultId
     * @param $indicatorId
     *
     * @return Factory|View|RedirectResponse|Application
     */
    public function edit($resultId, $indicatorId): Factory|View|RedirectResponse|Application
    {
        try {
            $indicator = $this->indicatorService->getIndicator($indicatorId);
            $result = $this->resultService->getResult($resultId);
            $element = getElementSchema('indicator');
            $activity = $this->activityService->getActivity($result->activity->id);
            $form = $this->indicatorService->editFormGenerator($resultId, $indicatorId);

            $formHeader = $this->getFormHeader(
                hasData    : true,
                elementName: 'indicator',
                parentTitle: Arr::get($result, 'result.title.0.narrative.0.narrative', getTranslatedUntitled())
            );
            $breadCrumbInfo = $this->indicatorBreadCrumbInfo(
                activity : $activity,
                result   : $result,
                indicator: $indicator,
            );

            $data = [
                'title'            => $element['label'],
                'name'             => 'indicator',
                'form_header'      => $formHeader,
                'bread_crumb_info' => $breadCrumbInfo,
            ];

            return view('admin.activity.indicator.edit', compact('form', 'activity', 'data'));
        } catch (Exception $e) {
            logger()->error($e->getMessage());
            $translatedMessage = trans('common/common.error_opening_data_entry_form');

            return redirect()->route('admin.result.indicator.index', $resultId)->with(
                'error',
                $translatedMessage
            );
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param IndicatorRequest $request
     * @param                  $resultId
     * @param                  $indicatorId
     *
     * @return RedirectResponse
     */
    public function update(IndicatorRequest $request, $resultId, $indicatorId): RedirectResponse
    {
        try {
            $indicatorData = $request->except(['_method', '_token']);
            $indicator = $this->indicatorService->getIndicator($indicatorId);
            $result = $indicator->result;

            if (!$this->indicatorService->update($indicatorId, ['result_id' => $result->id, 'indicator' => $indicatorData])) {
                $translatedMessage = trans('common/common.failed_to_update_data');

                return redirect()->route('admin.result.indicator.index', $resultId)->with(
                    'error',
                    $translatedMessage
                );
            }

            $translatedMessage = trans('common/common.updated_successfully');

            return redirect()->route('admin.result.indicator.show', [$resultId, $indicatorId])->with(
                'success',
                $translatedMessage
            );
        } catch (Exception $e) {
            logger()->error($e->getMessage());

            $translatedMessage = trans('common/common.failed_to_update_data');

            return redirect()->route('admin.result.indicator.index', $resultId)->with(
                'error',
                $translatedMessage
            );
        }
    }

    /**
     * Deletes Specific Indicator.
     *
     * @param $id
     * @param $indicatorId
     *
     * @return JsonResponse
     */
    public function destroy($id, $indicatorId): JsonResponse
    {
        try {
            $this->indicatorService->deleteIndicator($indicatorId);
            $translatedMessage = trans('common/common.deleted_successfully');
            Session::flash('success', $translatedMessage);

            return response()->json([
                'status'    => true,
                'msg'       => $translatedMessage,
                'result_id' => $id,
            ]);
        } catch (Exception $e) {
            logger()->error($e->getMessage());
            $translatedMessage = trans('common/common.delete_error');
            Session::flash('error', $translatedMessage);

            return response()->json([
                'status'    => true,
                'msg'       => $translatedMessage,
                'result_id' => $id,
            ], 400);
        }
    }
}
