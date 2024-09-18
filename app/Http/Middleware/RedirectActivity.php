<?php

namespace App\Http\Middleware;

use App\IATI\Services\Activity\ActivityService;
use App\IATI\Services\Activity\IndicatorService;
use App\IATI\Services\Activity\PeriodService;
use App\IATI\Services\Activity\ResultService;
use App\IATI\Services\Activity\TransactionService;
use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;

/**
 * Class RedirectActivity.
 */
class RedirectActivity
{
    /**
     * @var ActivityService
     */
    protected ActivityService $activityService;

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
    private PeriodService $periodService;

    /**
     * @var TransactionService
     */
    private TransactionService $transactionService;

    /**
     * @param  ActivityService  $activityService
     * @param  ResultService  $resultService
     * @param  IndicatorService  $indicatorService
     * @param  PeriodService  $periodService
     * @param  TransactionService  $transactionService
     */
    public function __construct(
        ActivityService $activityService,
        ResultService $resultService,
        IndicatorService $indicatorService,
        PeriodService $periodService,
        TransactionService $transactionService
    ) {
        $this->activityService = $activityService;
        $this->resultService = $resultService;
        $this->indicatorService = $indicatorService;
        $this->periodService = $periodService;
        $this->transactionService = $transactionService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        /**
         * Basically avoid any checks.
         */
        $byPassActivityRoutes = [
            'admin.activities.index',
            'admin.activities.paginate',
            'admin.activities.codelist',
            'admin.activity.store',
            'admin.activities.paginate',
        ];

        if (in_array($request->route()->getName(), $byPassActivityRoutes, true)) {
            return $next($request);
        }

        $id = (int) $request->route('id');

        if (strlen($id) === strlen($request->route('id'))) {
            $activity = [];

            [$module, $subModule] = $this->getRouteParams($request->route()->getName());

            if ($module === 'activity') {
                $activity = $this->activityService->getActivity($id);

                if ($activity === null || !$activity->isActivityOfOrg()) {
                    return redirect(RouteServiceProvider::HOME)
                        ->with('error', trans('validation.activity_not_exist'));
                }

                $byPassResultRoutes = [
                    'admin.activity.result.index',
                    'admin.activity.results.paginate',
                    'admin.activity.result.create',
                    'admin.activity.result.store',
                    'admin.activity.results.bulkDelete',
                ];

                if (in_array($subModule, ['result', 'results']) && !in_array(
                    $request->route()->getName(),
                    $byPassResultRoutes,
                    true
                )) {
                    $resultId = (int) $request->route('resultId');

                    if (!$this->resultService->activityResultExists($id, $resultId)) {
                        return redirect(RouteServiceProvider::HOME)
                            ->with('error', trans('validation.result_not_exist'));
                    }
                }

                /**
                 * Basically avoid the `activityTransactionExists()` check.
                 */
                $byPassTransactionRoutes = [
                    'admin.activity.transaction.index',
                    'admin.activity.transactions.paginate',
                    'admin.activity.transaction.create',
                    'admin.activity.transaction.store',
                    'admin.activity.transactions.bulkDelete',
                ];

                if (in_array($subModule, ['transaction', 'transactions']) && !in_array(
                    $request->route()->getName(),
                    $byPassTransactionRoutes,
                    true
                )) {
                    $transactionId = (int) $request->route('transactionId');

                    if (!$this->transactionService->activityTransactionExists($id, $transactionId)) {
                        return redirect(RouteServiceProvider::HOME)
                            ->with('error', trans('validation.transaction_not_exist'));
                    }
                }
            } elseif ($module === 'result') {
                $result = $this->resultService->getResult($id);

                if ($result === null) {
                    return redirect(RouteServiceProvider::HOME)
                        ->with('error', trans('validation.result_not_exist'));
                }

                if ($result->activity === null || !$result->activity->isActivityOfOrg()) {
                    return redirect(RouteServiceProvider::HOME)
                        ->with('error', trans('validation.activity_not_exist'));
                }

                $byPassIndicatorRoutes = [
                    'admin.result.indicator.index',
                    'admin.result.indicators.paginate',
                    'admin.result.indicator.create',
                    'admin.result.indicator.store',
                ];

                if (in_array($subModule, ['indicator', 'indicators']) && !in_array(
                    $request->route()->getName(),
                    $byPassIndicatorRoutes,
                    true
                )) {
                    $indicatorId = (int) $request->route('indicatorId');

                    if (!$this->indicatorService->resultIndicatorExists($id, $indicatorId)) {
                        return redirect(RouteServiceProvider::HOME)
                            ->with('error', trans('validation.indicator_not_exist'));
                    }
                }

                $activity = $result->activity;
            } elseif ($module === 'indicator') {
                $indicator = $this->indicatorService->getIndicator($id);

                if ($indicator === null) {
                    return redirect(RouteServiceProvider::HOME)
                        ->with('error', trans('validation.indicator_not_exist'));
                }

                if ($indicator->result === null) {
                    return redirect(RouteServiceProvider::HOME)
                        ->with('error', trans('validation.result_not_exist'));
                }

                if ($indicator->result->activity === null || !$indicator->result->activity->isActivityOfOrg()) {
                    return redirect(RouteServiceProvider::HOME)
                        ->with('error', trans('validation.activity_not_exist'));
                }

                $byPassPeriodRoutes = [
                    'admin.indicator.period.index',
                    'admin.indicator.periods.paginate',
                    'admin.indicator.period.create',
                    'admin.indicator.period.store',
                ];

                if (in_array($subModule, ['period', 'periods']) && !in_array(
                    $request->route()->getName(),
                    $byPassPeriodRoutes,
                    true
                )) {
                    $periodId = (int) $request->route('periodId');

                    if (!$this->periodService->indicatorPeriodExist($id, $periodId)) {
                        return redirect(RouteServiceProvider::HOME)
                            ->with('error', trans('validation.period_not_exist'));
                    }
                }
                $activity = $indicator->result->activity;
            }

            if ($activity && $activity->isActivityOfOrg()) {
                return $next($request);
            }

            return redirect(RouteServiceProvider::HOME)
                ->with('error', trans('validation.activity_not_exist'));
        }

        return abort(404);
    }

    /**
     * Returns the route parameters.
     *
     * @param $params
     *
     * @return array
     */
    public function getRouteParams($params): array
    {
        $routeParams = explode('.', $params);

        if (empty($routeParams)) {
            return abort(404);
        }

        return [$routeParams[1], $routeParams[2]];
    }
}
