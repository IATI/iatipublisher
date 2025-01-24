<?php

declare(strict_types=1);

namespace App\IATI\Traits;

use App\IATI\Models\Activity\Activity;
use App\IATI\Models\Activity\Indicator;
use App\IATI\Models\Activity\Period;
use App\IATI\Models\Activity\Result;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * Class EditFormTrait.
 */
trait EditFormTrait
{
    /**
     * Returns string for form header.
     * Wrapped by css class: overflow-hidden ellipsis__title
     * Example:
     * Add Document link for Activity 1.
     * Edit Document link for Activity 1.
     *
     * @param $hasData
     * @param $elementName
     * @param $parentTitle
     *
     * @return string
     */
    protected function getFormHeader($hasData, $elementName, $parentTitle): string
    {
        $elementName = ucfirst(str_replace('_', ' ', $elementName));

        if ($hasData) {
            return sprintf('%s %s %s %s', trans('common/common.edit'), $elementName, trans('common/common.for'), $parentTitle);
        }

        return sprintf('%s %s %s %s', trans('common/common.add'), $elementName, trans('common/common.for'), $parentTitle);
    }

    protected function basicBreadCrumbInfo(Activity $activity, string $elementName): array
    {
        $activityId = Arr::get($activity, 'id');
        $activityTitle = Arr::get($activity, 'title.0.narrative') ?? $this->getTranslatedUntitledActivity();
        $activityTitle = substr($activityTitle, 0, 32);

        return [
            $activityTitle                               => "/activity/$activityId",
            ucwords(str_replace('_', ' ', $elementName)) => "/activity/$activityId#$elementName",
        ];
    }

    protected function resultBreadCrumbInfo(Activity $activity, ?Result $result): array
    {
        $activityId = Arr::get($activity, 'id');
        $activityTitle = Arr::get($activity, 'title.0.narrative') ?? $this->getTranslatedUntitledActivity();
        $activityTitle = substr($activityTitle, 0, 32);

        $resultTitle = $this->getTranslatedUntitledResult();
        $resultPath = 'result/create';

        if ($result) {
            $resultTitle = Arr::get($result, 'result.title.0.narrative.0.narrative') ?? $resultTitle;
            $resultTitle = substr($resultTitle, 0, 32);
            $resultId = Arr::get($result, 'id');
            $resultPath = "result/$resultId";
        }

        return [
            $activityTitle                     => "/activity/$activityId",
            trans('common/common.result_list') => "/activity/$activityId/result",
            $resultTitle                       => "/activity/$activityId/$resultPath",
        ];
    }

    protected function indicatorBreadCrumbInfo(Activity $activity, ?Result $result, ?Indicator $indicator): array
    {
        $activityId = Arr::get($activity, 'id');
        $activityTitle = Arr::get($activity, 'title.0.narrative') ?? $this->getTranslatedUntitledActivity();
        $activityTitle = substr($activityTitle, 0, 32);
        $resultTitle = $this->getTranslatedUntitledResult();
        $resultTitle = Arr::get($result, 'result.title.0.narrative.0.narrative') ?? $resultTitle;
        $resultTitle = substr($resultTitle, 0, 32);
        $resultId = Arr::get($result, 'id');
        $resultPath = "result/$resultId";

        $indicatorTitle = $this->getTranslatedUntitledIndicator();
        $indicatorPath = 'indicator/create';

        if ($indicator) {
            $indicatorTitle = Arr::get($indicator, 'indicator.title.0.narrative.0.narrative') ?? $indicatorTitle;
            $indicatorTitle = substr($indicatorTitle, 0, 32);
            $indicatorId = Arr::get($indicator, 'id', '#');
            $indicatorPath = "indicator/$indicatorId";
        }

        return [
            $activityTitle                        => "/activity/$activityId",
            trans('common/common.result_list')    => "/activity/$activityId/result",
            $resultTitle                          => "/activity/$activityId/$resultPath",
            trans('common/common.indicator_list') => "/result/$resultId/indicator",
            $indicatorTitle                       => "/result/$resultId/$indicatorPath",
        ];
    }

    protected function periodBreadCrumbInfo(
        Activity $activity,
        ?Result $result,
        ?Indicator $indicator,
        ?Period $period
    ): array {
        $activityId = Arr::get($activity, 'id');
        $activityTitle = Arr::get($activity, 'title.0.narrative') ?? $this->getTranslatedUntitledActivity();
        $activityTitle = substr($activityTitle, 0, 32);
        $resultTitle = $this->getTranslatedUntitledResult();
        $resultId = '#';
        $indicatorTitle = $this->getTranslatedUntitledIndicator();
        $indicatorId = '#';
        $periodTitle = $this->getTranslatedNewPeriod();
        $periodId = 'create';

        if ($result) {
            $resultTitle = Arr::get($result, 'result.title.0.narrative.0.narrative') ?? $resultTitle;
            $resultTitle = substr($resultTitle, 0, 32);
            $resultId = Arr::get($result, 'id', $resultId);
        }

        if ($indicator) {
            $indicatorTitle = Arr::get($indicator, 'indicator.title.0.narrative.0.narrative') ?? $indicatorTitle;
            $indicatorTitle = substr($indicatorTitle, 0, 32);
            $indicatorId = Arr::get($indicator, 'id', '#');
        }

        if ($period) {
            $periodTitle = 'Period';
            $periodId = Arr::get($period, 'id') ?? $period;
        }

        return [
            $activityTitle                        => "/activity/$activityId",
            trans('common/common.result_list')    => "/activity/$activityId/result",
            $resultTitle                          => "/activity/$activityId/result/$resultId",
            trans('common/common.indicator_list') => "/result/$resultId/indicator",
            $indicatorTitle                       => "/result/$resultId/indicator/$indicatorId",
            trans('common/common.period_list')    => "/indicator/$indicatorId/period",
            $periodTitle                          => "/indicator/$indicatorId/period/$periodId",
        ];
    }

    protected function transactionBreadCrumbInfo($activity, $transaction): array
    {
        $activityId = Arr::get($activity, 'id');
        $activityTitle = Arr::get($activity, 'title.0.narrative');
        $activityTitle = substr($activityTitle, 0, 32);

        return [
            $activityTitle => "/activity/$activityId",
            'Transactions' => "/activity/$activityId/transaction",
        ];
    }

    /**
     * @return string
     */
    protected function getTranslatedUntitledActivity(): string
    {
        return Str::title(sprintf('%s %s', trans('common/common.untitled'), trans('common/common.activity')));
    }

    public function getTranslatedUntitledResult(): string
    {
        return Str::title(sprintf('%s Result', trans('common/common.untitled')));
    }

    public function getTranslatedUntitledIndicator(): string
    {
        return Str::title(sprintf('%s Indicator', trans('common/common.untitled')));
    }

    /**
     * @return string
     */
    public function getTranslatedNewPeriod(): string
    {
        return Str::title(sprintf('%s Period', trans('common/common.new')));
    }
}
