<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Activity;

use App\Http\Controllers\Controller;
use App\Http\Requests\Activity\Description\DescriptionRequest;
use App\IATI\Services\Activity\DescriptionService;
use App\IATI\Traits\EditFormTrait;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;

/**
 * Class DescriptionController.
 */
class DescriptionController extends Controller
{
    use EditFormTrait;

    /**
     * @var DescriptionService
     */
    protected DescriptionService $descriptionService;

    /**
     * DescriptionController Constructor.
     *
     * @param DescriptionService $descriptionService
     */
    public function __construct(DescriptionService $descriptionService)
    {
        $this->descriptionService = $descriptionService;
    }

    /**
     * Renders description edit form.
     *
     * @param int $id
     *
     * @return View|RedirectResponse
     */
    public function edit(int $id): View|RedirectResponse
    {
        try {
            $element = getElementSchema('description');
            $activity = $this->descriptionService->getActivityData($id);
            $deprecationStatusMap = Arr::get($activity->deprecation_status_map, 'description', []);
            $form = $this->descriptionService->formGenerator(
                id                        : $id,
                activityDefaultFieldValues: $activity->default_field_values ?? [],
                deprecationStatusMap      : $deprecationStatusMap
            );

            $hasData = (bool) Arr::get($activity, 'description', false);
            $formHeader = $this->getFormHeader(
                hasData    : $hasData,
                elementName: 'description',
                parentTitle: Arr::get($activity, 'title.0.narrative', 'Untitled')
            );
            $breadCrumbInfo = $this->basicBreadCrumbInfo($activity, 'description');

            $data = [
                'title'            => $element['label'],
                'name'             => 'description',
                'form_header'      => $formHeader,
                'bread_crumb_info' => $breadCrumbInfo,
            ];

            return view('admin.activity.description.edit', compact('form', 'activity', 'data'));
        } catch (Exception $e) {
            logger()->error($e->getMessage());

            return redirect()->route('admin.activity.show', $id)->with(
                'error',
                'Error has occurred while rendering activity description form.'
            );
        }
    }

    /**
     * Updates description data.
     *
     * @param DescriptionRequest $request
     * @param $id
     *
     * @return JsonResponse|RedirectResponse
     */
    public function update(DescriptionRequest $request, $id): JsonResponse|RedirectResponse
    {
        try {
            $activityData = $this->descriptionService->getActivityData($id);
            $activityDescription = $request->all();

            if (!$this->descriptionService->update($activityDescription, $activityData)) {
                return redirect()->route('admin.activity.show', $id)->with('error', 'Error has occurred while updating description.');
            }

            return redirect()->route('admin.activity.show', $id)->with('success', 'Description updated successfully.');
        } catch (Exception $e) {
            logger()->error($e->getMessage());

            return redirect()->route('admin.activity.show', $id)->with('error', 'Error has occurred while updating description.');
        }
    }
}
