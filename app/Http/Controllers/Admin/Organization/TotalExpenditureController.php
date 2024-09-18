<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Organization;

use App\Http\Controllers\Controller;
use App\Http\Requests\Organization\TotalExpenditure\TotalExpenditureRequest;
use App\IATI\Services\Organization\TotalExpenditureService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

/**
 * Class TotalExpenditureController.
 */
class TotalExpenditureController extends Controller
{
    /**
     * @var TotalExpenditureService
     */
    protected TotalExpenditureService $totalExpenditureService;

    /**
     * TotalExpenditureController Constructor.
     *
     * @param TotalExpenditureService    $totalExpenditureService
     */
    public function __construct(TotalExpenditureService $totalExpenditureService)
    {
        $this->totalExpenditureService = $totalExpenditureService;
    }

    /**
     * Renders title edit form.
     *
     * @return View|RedirectResponse
     */
    public function edit(): View|RedirectResponse
    {
        try {
            $id = Auth::user()->organization_id;
            $element = json_decode(file_get_contents(app_path('IATI/Data/organizationElementJsonSchema.json')), true, 512, JSON_THROW_ON_ERROR);
            $organization = $this->totalExpenditureService->getOrganizationData($id);
            $form = $this->totalExpenditureService->formGenerator($id, Arr::get($organization->deprecation_status_map, 'total_expenditure`', []));
            $data = ['title'=> $element['total_expenditure']['label'], 'name'=>'total-expenditure'];

            return view('admin.organisation.forms.totalExpenditure.totalExpenditure', compact('form', 'organization', 'data'));
        } catch (\Exception $e) {
            logger()->error($e->getMessage());
            $translatedMessage = trans('organisationDetail/total_expenditure_controller.error_has_occurred_while_opening_organization_total_expenditure_form');

            return redirect()->route('admin.organisation.index')->with('error', $translatedMessage);
        }
    }

    /**
     * Updates organization total expenditure data.
     *
     * @param TotalExpenditureRequest $request
     *
     * @return RedirectResponse
     */
    public function update(TotalExpenditureRequest $request): RedirectResponse
    {
        try {
            if (!$this->totalExpenditureService->update(Auth::user()->organization_id, $request->all())) {
                $translatedMessage = trans('organisationDetail/total_expenditure_controller.error_has_occurred_while_updating_organization_total_expenditure');

                return redirect()->route('admin.organisation.index')->with('error', $translatedMessage);
            }
            $translatedMessage = trans('organisationDetail/total_expenditure_controller.organization_total_expenditure_updated_successfully');

            return redirect()->route('admin.organisation.index')->with('success', $translatedMessage);
        } catch (\Exception $e) {
            logger()->error($e->getMessage());
            $translatedMessage = trans('organisationDetail/total_expenditure_controller.error_has_occurred_while_updating_organization_total_expenditure');

            return redirect()->route('admin.organisation.index')->with('error', $translatedMessage);
        }
    }
}
