<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Activity;

use App\Http\Controllers\Controller;
use App\Http\Requests\Activity\Identifier\IdentifierRequest;
use App\IATI\Services\Activity\ActivityIdentifierService;
use App\IATI\Traits\EditFormTrait;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

/**
 * Class IdentifierController.
 */
class IdentifierController extends Controller
{
    use EditFormTrait;

    /**
     * @var ActivityIdentifierService
     */
    protected ActivityIdentifierService $identifierService;

    /**
     * IdentifierController Constructor.
     *
     * @param ActivityIdentifierService $identifierService
     */
    public function __construct(ActivityIdentifierService $identifierService)
    {
        $this->identifierService = $identifierService;
    }

    /**
     * Renders status edit form.
     *
     * @param int $id
     *
     * @return View|RedirectResponse
     */
    public function edit(int $id): View|RedirectResponse
    {
        try {
            $element = getElementSchema('iati_identifier');
            $activity = $this->identifierService->getActivityData($id);
            $deprecationStatusMap = Arr::get($activity->deprecation_status_map, 'iati_identifier', []);
            $form = $this->identifierService->formGenerator(
                id                  : $id,
                deprecationStatusMap: $deprecationStatusMap
            );

            $hasData = (bool) Arr::get($activity, 'iati_identifier', false);
            $formHeader = $this->getFormHeader(
                hasData    : $hasData,
                elementName: 'IATI_identifier',
                parentTitle: Arr::get($activity, 'title.0.narrative', 'Untitled')
            );
            $breadCrumbInfo = $this->basicBreadCrumbInfo($activity, 'IATI_identifier');

            $data = [
                'title'            => $element['label'],
                'name'             => 'iati_identifier',
                'form_header'      => $formHeader,
                'bread_crumb_info' => $breadCrumbInfo,
            ];

            return view('admin.activity.identifier.edit', compact('form', 'activity', 'data'));
        } catch (Exception $e) {
            logger()->error($e->getMessage());

            return redirect()->route('admin.activity.show', $id)->with(
                'error',
                'Error has occurred while opening activity title form.'
            );
        }
    }

    /**
     * Updates identifier data.
     *
     * @param IdentifierRequest $request
     * @param                   $id
     *
     * @return JsonResponse|RedirectResponse
     */
    public function update(IdentifierRequest $request, $id): JsonResponse|RedirectResponse
    {
        try {
            DB::beginTransaction();

            if (!$this->identifierService->update($id, $request->except(['_method', '_token']))) {
                return redirect()->route('admin.activity.show', $id)->with('error', 'Error has occurred while updating iati-identifier.');
            }

            DB::commit();

            return redirect()->route('admin.activity.show', $id)->with('success', 'Iati-identifier updated successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            logger()->error($e->getMessage());

            return response()->json(
                ['success' => false, 'error' => 'Error has occurred while updating iati-identifier.']
            );
        }
    }
}
