<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Activity;

use App\Http\Controllers\Controller;
use App\Http\Requests\Activity\RecipientCountry\RecipientCountryRequest;
use App\IATI\Services\Activity\ActivityService;
use App\IATI\Services\Activity\RecipientCountryService;
use App\IATI\Traits\EditFormTrait;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use JsonException;

/**
 * Class RecipientCountryController.
 */
class RecipientCountryController extends Controller
{
    use EditFormTrait;

    /**
     * @var RecipientCountryService
     */
    protected RecipientCountryService $recipientCountryService;

    /**
     * For recipient region or country element json schema.
     *
     * @var ActivityService
     */
    protected ActivityService $activityService;

    /**
     * RecipientCountryController Constructor.
     *
     * @param RecipientCountryService $recipientCountryService
     * @param ActivityService $activityService
     */
    public function __construct(RecipientCountryService $recipientCountryService, ActivityService $activityService)
    {
        $this->recipientCountryService = $recipientCountryService;
        $this->activityService = $activityService;
    }

    /**
     * Renders recipient country edit form.
     *
     * @param int $id
     *
     * @return View|RedirectResponse
     */
    public function edit(int $id): View|RedirectResponse
    {
        try {
            $activity = $this->recipientCountryService->getActivityData($id);
            $element = $this->activityService->getRecipientRegionOrCountryManipulatedElementSchema($activity, 'recipient_country');
            $deprecationStatusMap = Arr::get($activity->deprecation_status_map, 'recipient_country', []);
            $form = $this->recipientCountryService->formGenerator(
                id                        : $id,
                element                   : $element,
                activityDefaultFieldValues: $activity->default_field_values ?? [],
                deprecationStatusMap      : $deprecationStatusMap
            );

            $hasData = (bool) Arr::get($activity, 'recipient_country', false);
            $formHeader = $this->getFormHeader(
                hasData    : $hasData,
                elementName: 'recipient_country',
                parentTitle: Arr::get($activity, 'title.0.narrative', getTranslatedUntitled())
            );
            $breadCrumbInfo = $this->basicBreadCrumbInfo($activity, 'recipient_country');

            $data = [
                'title'            => $element['label'],
                'name'             => 'recipient_country',
                'form_header'      => $formHeader,
                'bread_crumb_info' => $breadCrumbInfo,
            ];

            return view('admin.activity.recipientCountry.edit', compact('form', 'activity', 'data'));
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
     * Update the recipient-country data.
     *
     * @param RecipientCountryRequest $request
     * @param $id
     *
     * @return JsonResponse|RedirectResponse
     */
    public function update(RecipientCountryRequest $request, $id): JsonResponse|RedirectResponse
    {
        try {
            if (!$this->recipientCountryService->update($id, $request->all())) {
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
     * append freeze and info_text if recipient country or region exists in any one of the activity transactions
     * if exists then it freezes the section.
     *
     * @param $activity
     *
     * @throws JsonException
     *
     * @return array
     */
    public function getRecipientCountryManipulatedElementSchema($activity): array
    {
        $element = getElementSchema('recipient_country');

        if (count($activity->transactions)) {
            $recipient_region = $activity->transactions->pluck('transaction.recipient_region')->toArray();
            $recipient_country = $activity->transactions->pluck('transaction.recipient_country')->toArray();

            if (!is_array_value_empty($recipient_region) || !is_array_value_empty($recipient_country)) {
                $element['freeze'] = true;
                $translatedMessage = trans('activity_detail/recipient_country_controller.recipient_country_is_already_added_at_transaction_level');

                $element['info_text'] = $translatedMessage;
            }
        }

        return $element;
    }
}
