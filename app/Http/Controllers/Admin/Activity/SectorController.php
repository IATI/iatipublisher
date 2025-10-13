<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Activity;

use App\Http\Controllers\Controller;
use App\Http\Requests\Activity\Sector\SectorRequest;
use App\IATI\Services\Activity\SectorService;
use App\IATI\Traits\EditFormTrait;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use JsonException;

/**
 * Class SectorController.
 */
class SectorController extends Controller
{
    use EditFormTrait;

    /**
     * @var SectorService
     */
    protected SectorService $sectorService;

    /**
     * SectorController Constructor.
     *
     * @param SectorService $sectorService
     */
    public function __construct(SectorService $sectorService)
    {
        $this->sectorService = $sectorService;
    }

    /**
     * Renders Sector edit form.
     *
     * @param int $id
     *
     * @return View|RedirectResponse
     */
    public function edit(int $id): View|RedirectResponse
    {
        try {
            $activity = $this->sectorService->getActivityData($id);
            $element = $this->getSectorManipulatedElementSchema($activity);
            $deprecationStatusMap = Arr::get($activity->deprecation_status_map, 'sector', []);
            $form = $this->sectorService->formGenerator(
                id                        : $id,
                element                   : $element,
                activityDefaultFieldValues: $activity->default_field_values ?? [],
                deprecationStatusMap      : $deprecationStatusMap
            );

            $hasData = (bool) Arr::get($activity, 'sector', false);
            $formHeader = $this->getFormHeader(
                hasData    : $hasData,
                elementName: 'sector',
                parentTitle: Arr::get($activity, 'title.0.narrative', getTranslatedUntitled())
            );
            $breadCrumbInfo = $this->basicBreadCrumbInfo($activity, 'sector');

            $data = [
                'title'            => $element['label'],
                'name'             => 'sector',
                'form_header'      => $formHeader,
                'bread_crumb_info' => $breadCrumbInfo,
            ];

            return view('admin.activity.sector.edit', compact('form', 'activity', 'data'));
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
     * Updates sector data.
     *
     * @param SectorRequest $request
     * @param               $id
     *
     * @return JsonResponse|RedirectResponse
     */
    public function update(SectorRequest $request, $id): JsonResponse|RedirectResponse
    {
        try {
            if (!$this->sectorService->update($id, $request->all())) {
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

    /**
     * append freeze if sector found in activity transactions
     * and freezes sector in activity level.
     *
     * @param $activity
     *
     * @throws JsonException
     *
     * @return array
     */
    public function getSectorManipulatedElementSchema($activity): array
    {
        $element = getElementSchema('sector');

        if (count($activity->transactions)) {
            $sector = $activity->transactions->pluck('transaction.sector')->toArray();

            if (!is_array_value_empty($sector)) {
                $element['freeze'] = true;
                $translatedMessage = trans('activity_detail/sector_controller.sector_has_already_been_declared_at_transaction_level');

                $element['info_text'] = $translatedMessage;
            }
        }

        return $element;
    }
}
