<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Activity;

use App\Http\Controllers\Controller;
use App\Http\Requests\Activity\RecipientRegion\RecipientRegionRequest;
use App\IATI\Services\Activity\ActivityService;
use App\IATI\Services\Activity\RecipientRegionService;
use App\IATI\Traits\EditFormTrait;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;

/**
 * Class RecipientRegionController.
 */
class RecipientRegionController extends Controller
{
    use EditFormTrait;

    /**
     * @var RecipientRegionService
     */
    protected RecipientRegionService $recipientRegionService;

    /**
     * For recipient region or country element json schema.
     *
     * @var ActivityService
     */
    protected ActivityService $activityService;

    /**
     * RecipientRegionController Constructor.
     *
     * @param RecipientRegionService $recipientRegionService
     * @param ActivityService $activityService
     */
    public function __construct(RecipientRegionService $recipientRegionService, ActivityService $activityService)
    {
        $this->recipientRegionService = $recipientRegionService;
        $this->activityService = $activityService;
    }

    /**
     * Renders recipient region edit form.
     *
     * @param int $id
     *
     * @return View|RedirectResponse
     */
    public function edit(int $id): View|RedirectResponse
    {
        try {
            $activity = $this->recipientRegionService->getActivityData($id);
            $element = $this->activityService->getRecipientRegionOrCountryManipulatedElementSchema($activity, 'recipient_region');
            $deprecationStatusMap = Arr::get($activity->deprecation_status_map, 'recipient_region', []);
            $form = $this->recipientRegionService->formGenerator(
                id                        : $id,
                element                   : $element,
                activityDefaultFieldValues: $activity->default_field_values ?? [],
                deprecationStatusMap      : $deprecationStatusMap
            );

            $hasData = (bool) Arr::get($activity, 'recipient_region', false);
            $formHeader = $this->getFormHeader(
                hasData    : $hasData,
                elementName: 'recipient_region',
                parentTitle: Arr::get($activity, 'title.0.narrative', getTranslatedUntitled())
            );
            $breadCrumbInfo = $this->basicBreadCrumbInfo($activity, 'recipient_region');

            $data = [
                'title'            => $element['label'],
                'name'             => 'recipient_region',
                'form_header'      => $formHeader,
                'bread_crumb_info' => $breadCrumbInfo,
            ];

            return view('admin.activity.recipientRegion.edit', compact('form', 'activity', 'data'));
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
     * Updates recipient-region form.
     *
     * @param RecipientRegionRequest $request
     * @param $id
     *
     * @return JsonResponse|RedirectResponse
     */
    public function update(RecipientRegionRequest $request, $id): JsonResponse|RedirectResponse
    {
        try {
            if (!$this->recipientRegionService->update($id, $request->all())) {
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
