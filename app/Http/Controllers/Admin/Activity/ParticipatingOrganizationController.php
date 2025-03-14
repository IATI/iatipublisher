<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Activity;

use App\Http\Controllers\Controller;
use App\Http\Requests\Activity\ParticipatingOrganization\ParticipatingOrganizationRequest;
use App\IATI\Services\Activity\ParticipatingOrganizationService;
use App\IATI\Traits\EditFormTrait;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;

/**
 * Class ParticipatingOrganizationController
 *.
 */
class ParticipatingOrganizationController extends Controller
{
    use EditFormTrait;

    /**
     * @var ParticipatingOrganizationService
     */
    protected ParticipatingOrganizationService $participatingOrganizationService;

    /**
     * ParticipatingOrganizationController Constructor.
     *
     * @param ParticipatingOrganizationService $participatingOrganizationService
     */
    public function __construct(ParticipatingOrganizationService $participatingOrganizationService)
    {
        $this->participatingOrganizationService = $participatingOrganizationService;
    }

    /**
     * Renders participating organization edit form.
     * Renders participating organization edit form.
     *
     * @param int $id
     *
     * @return View|RedirectResponse
     */
    public function edit(int $id): View|RedirectResponse
    {
        try {
            $element = getElementSchema('participating_org');
            $activity = $this->participatingOrganizationService->getActivityData($id);
            $deprecationStatusMap = Arr::get($activity->deprecation_status_map, 'participating_org', []);
            $form = $this->participatingOrganizationService->formGenerator(
                id                        : $id,
                activityDefaultFieldValues: $activity->default_field_values ?? [],
                deprecationStatusMap      : $deprecationStatusMap
            );

            $hasData = (bool) Arr::get($activity, 'participating_org', false);
            $formHeader = $this->getFormHeader(
                hasData    : $hasData,
                elementName: 'participating_org',
                parentTitle: Arr::get($activity, 'title.0.narrative', getTranslatedUntitled())
            );
            $breadCrumbInfo = $this->basicBreadCrumbInfo($activity, 'participating_org');

            $data = [
                'title'            => $element['label'],
                'name'             => 'participating_org',
                'form_header'      => $formHeader,
                'bread_crumb_info' => $breadCrumbInfo,
            ];

            return view('admin.activity.participatingOrganization.edit', compact('form', 'activity', 'data'));
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
     * Updates participating-organization data.
     *
     * @param ParticipatingOrganizationRequest $request
     * @param                                  $id
     *
     * @return JsonResponse|RedirectResponse
     */
    public function update(ParticipatingOrganizationRequest $request, $id): JsonResponse|RedirectResponse
    {
        try {
            if (!$this->participatingOrganizationService->update($id, $request->except(['_token', '_method']))) {
                $translatedMessage = trans('common/common.failed_to_update_data');

                return redirect()->route('admin.activities.show', $id)->with('error', $translatedMessage);
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
