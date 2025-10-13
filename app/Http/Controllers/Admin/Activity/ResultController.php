<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Activity;

use App\Http\Controllers\Controller;
use App\Http\Requests\Activity\Result\ResultRequest;
use App\Http\Requests\BulkDeleteResultRequest;
use App\IATI\Models\Activity\Result;
use App\IATI\Services\Activity\ActivityService;
use App\IATI\Services\Activity\ResultService;
use App\IATI\Traits\EditFormTrait;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

/**
 * Class ResultController.
 */
class ResultController extends Controller
{
    use EditFormTrait;

    /**
     * @var ResultService
     */
    protected ResultService $resultService;

    /**
     * @var ActivityService
     */
    protected ActivityService $activityService;

    /**
     * ResultController Constructor.
     *
     * @param ResultService   $resultService
     * @param ActivityService $activityService
     */
    public function __construct(
        ResultService $resultService,
        ActivityService $activityService
    ) {
        $this->resultService = $resultService;
        $this->activityService = $activityService;
    }

    /**
     * Renders result listing page.
     *
     * @param $activityId
     *
     * @return View|RedirectResponse
     */
    public function index($activityId): View|RedirectResponse
    {
        try {
            $activity = $this->activityService->getActivity($activityId);
            $results = $this->resultService->getActivityResults($activityId);
            $types = getResultTypes();
            $toast = generateToastData();

            return view('admin.activity.result.index', compact('activity', 'results', 'types', 'toast'));
        } catch (Exception $e) {
            logger()->error($e);
            $translatedMessage = trans('common/common.error_has_occurred_while_rendering_activity_transactions_listing');

            return redirect()->route('admin.activity.result.index', $activityId)->with(
                'error',
                $translatedMessage
            );
        }
    }

    /**
     * Returns paginated results.
     *
     * @param int $activityId
     * @param int $page
     *
     * @return JsonResponse
     */
    public function getPaginatedResults(int $activityId, int $page = 1) : JsonResponse
    {
        try {
            $result = $this->resultService->getPaginatedResult($activityId, $page, $this->sanitizeRequest(request()));
            $stats = $this->resultService->getResultCountStats($activityId);
            $translatedMessage = 'Results Fetched Successfully';

            return response()->json([
                'success' => true,
                'message' => $translatedMessage,
                'data'    => [
                    'results' => $result,
                    'stats'   => $stats,
                ],
            ]);
        } catch (Exception $e) {
            logger()->error($e);
            $translatedMessage = 'Error occurred while fetching the data.';

            return response()->json(['success' => false, 'message' => $translatedMessage]);
        }
    }

    public function sanitizeRequest(\Illuminate\Http\Request $request)
    {
        $tableConfig = getTableConfig('result');
        $transactionTypeMap = [
            'all'     => 'all',
            'output'  => 1,
            'outcome' => 2,
            'impact'  => 3,
            'other'   => 9,
        ];

        $queryParams = [];

        if (!empty($request->get('q'))) {
            $queryParams['query'] = filter_var($request->get('q'), FILTER_SANITIZE_STRING);
        }

        if (!empty($request->get('limit'))) {
            $queryParams['limit'] = $request->get('limit') ?? 10;
        }

        if (in_array($request->get('orderBy'), $tableConfig['orderBy'], true)) {
            $queryParams['orderBy'] = $request->get('orderBy') ?? '';

            if (in_array($request->get('direction'), $tableConfig['direction'], true)) {
                $queryParams['direction'] = $request->get('direction') ?? 'asc';
            }
        }

        if (in_array($request->get('filterBy'), $tableConfig['filterBy'], true)) {
            $queryParams['filterBy'] = $request->get('filterBy');
            $queryParams['filterBy'] = Arr::get($transactionTypeMap, $queryParams['filterBy'], 'all');
        }

        return $queryParams;
    }

    /**
     * Renders result create form.
     *
     * @param $id
     *
     * @return Factory|View|RedirectResponse|Application
     */
    public function create($id): Factory|View|RedirectResponse|Application
    {
        try {
            $element = getElementSchema('result');
            $activity = $this->activityService->getActivity($id);
            $form = $this->resultService->createFormGenerator($id, $activity->default_field_values ?? []);

            $formHeader = $this->getFormHeader(
                hasData    : false,
                elementName: 'result',
                parentTitle: Arr::get($activity, 'title.0.narrative', getTranslatedUntitled())
            );
            $breadCrumbInfo = $this->resultBreadCrumbInfo(
                activity: $activity,
                result  : null
            );

            $data = [
                'title'            => $element['label'],
                'name'             => 'result',
                'form_header'      => $formHeader,
                'bread_crumb_info' => $breadCrumbInfo,
            ];

            return view('admin.activity.result.edit', compact('form', 'activity', 'data'));
        } catch (Exception $e) {
            logger()->error($e);
            $translatedMessage = trans('common/common.error_opening_data_entry_form');

            return redirect()->route('admin.activity.result.index', $id)->with(
                'error',
                $translatedMessage
            );
        }
    }

    /**
     * Saves new result.
     *
     * @param ResultRequest $request
     * @param               $activityId
     *
     * @return RedirectResponse
     */
    public function store(ResultRequest $request, $activityId): RedirectResponse
    {
        try {
            $resultData = $request->except(['_token']);
            $result = $this->resultService->create([
                'activity_id' => $activityId,
                'result'      => $resultData,
            ]);
            $translatedMessage = trans('activity_detail/result_controller.activity_result_created_successfully');

            return redirect()->route('admin.activity.result.show', [$activityId, $result['id']])->with(
                'success',
                $translatedMessage
            );
        } catch (Exception $e) {
            logger()->error($e);

            return redirect()->route('admin.activity.result.index', $activityId)->with(
                'error',
                'Error has occurred while creating activity result.'
            );
        }
    }

    /**
     * Display the specified resource.
     *
     * @param $activityId
     * @param $resultId
     *
     * @return View|RedirectResponse
     */
    public function show($activityId, $resultId): View|RedirectResponse
    {
        try {
            $toast = generateToastData();
            $activity = $this->activityService->getActivity($activityId);
            $result = $this->resultService->getResultWithIndicatorAndPeriod($resultId, $activityId);
            $element = getElementSchema('result');
            $types = getResultTypes();

            return view('admin.activity.result.detail', compact('activity', 'result', 'types', 'toast', 'element'));
        } catch (Exception $e) {
            logger()->error($e);
            $translatedMessage = trans('common/common.error_has_occurred_while_rending_result_detail_page');

            return redirect()->route('admin.activity.result.index', $activityId)->with(
                'error',
                $translatedMessage
            );
        }
    }

    /**
     * Renders result edit page.
     *
     * @param $activityId
     * @param $resultId
     *
     * @return View|RedirectResponse
     */
    public function edit($activityId, $resultId): View|RedirectResponse
    {
        try {
            $element = getElementSchema('result');
            $activity = $this->activityService->getActivity($activityId);
            $form = $this->resultService->editFormGenerator(
                resultId                  : $resultId,
                activityId                : $activityId,
                activityDefaultFieldValues: $activity->default_field_values ?? []
            );

            $formHeader = $this->getFormHeader(
                hasData    : true,
                elementName: 'result',
                parentTitle: Arr::get($activity, 'title.0.narrative', getTranslatedUntitled())
            );
            $breadCrumbInfo = $this->resultBreadCrumbInfo(
                activity: $activity,
                result  : $this->resultService->getResult($resultId)
            );

            $data = [
                'title'            => $element['label'],
                'name'             => 'result',
                'form_header'      => $formHeader,
                'bread_crumb_info' => $breadCrumbInfo,
            ];

            return view('admin.activity.result.edit', compact('form', 'activity', 'data'));
        } catch (Exception $e) {
            logger()->error($e);
            $translatedMessage = trans('common/common.error_opening_data_entry_form');

            return redirect()->route('admin.activity.result.index', $activityId)->with(
                'error',
                $translatedMessage
            );
        }
    }

    /**
     * Updates specific result.
     *
     * @param ResultRequest $request
     * @param               $activityId
     * @param               $resultId
     *
     * @return RedirectResponse
     */
    public function update(ResultRequest $request, $activityId, $resultId): RedirectResponse
    {
        try {
            $resultData = $request->except(['_method', '_token']);

            if (!$this->resultService->update($resultId, ['activity_id' => $activityId, 'result' => $resultData])) {
                $translatedMessage = trans('common/common.failed_to_update_data');

                return redirect()->route('admin.activity.result.index', $activityId)->with(
                    'error',
                    $translatedMessage
                );
            }
            $translatedMessage = trans('common/common.updated_successfully');

            return redirect()->route('admin.activity.result.show', [$activityId, $resultId])->with(
                'success',
                $translatedMessage
            );
        } catch (Exception $e) {
            logger()->error($e);
            $translatedMessage = trans('common/common.failed_to_update_data');

            return redirect()->route('admin.activity.result.index', $activityId)->with(
                'error',
                $translatedMessage
            );
        }
    }

    /**
     * Deletes Specific Result.
     *
     * @param $id
     * @param $resultId
     *
     * @return JsonResponse
     */
    public function destroy($id, $resultId): JsonResponse
    {
        try {
            $this->resultService->deleteResult($resultId);
            $translatedMessage = trans('common/common.deleted_successfully');
            Session::flash('success', $translatedMessage);

            return response()->json([
                'status'      => true,
                'msg'         => $translatedMessage,
                'activity_id' => $id,
            ]);
        } catch (Exception $e) {
            logger()->error($e);
            $translatedMessage = trans('common/common.delete_error');
            Session::flash('error', $translatedMessage);

            return response()->json([
                'status'      => true,
                'msg'         => $translatedMessage,
                'activity_id' => $id,
            ], 400);
        }
    }

    /**
     * Bulk deletes transactions.
     *
     * @param BulkDeleteResultRequest $request
     * @param $id
     *
     * @return JsonResponse
     */
    public function bulkDeleteResults(BulkDeleteResultRequest $request, $id): JsonResponse
    {
        try {
            $resultIds = $request->validated('result_ids');

            DB::beginTransaction();
            $this->resultService->bulkDeleteResults($resultIds);
            DB::commit();

            $translatedMessage = trans('common/common.deleted_successfully');

            return response()->json([
                'status'      => true,
                'msg'         => $translatedMessage,
                'activity_id' => $id,
            ], 200);
        } catch (Exception $e) {
            DB::rollback();
            logger()->error($e);
            $translatedMessage = trans('common/common.failed_to_bulk_delete');

            return response()->json([
                'status'      => false,
                'msg'         => $translatedMessage,
                'activity_id' => $id,
            ], 400);
        }
    }
}
