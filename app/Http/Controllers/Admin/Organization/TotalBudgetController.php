<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Organization;

use App\Http\Controllers\Controller;
use App\Http\Requests\Organization\TotalBudget\TotalBudgetRequest;
use App\IATI\Services\Organization\TotalBudgetService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

/**
 * Class TotalBudgetController.
 */
class TotalBudgetController extends Controller
{
    protected TotalBudgetService $totalBudgetService;

    /**
     * TotalBudgetController Constructor.
     *
     * @param TotalBudgetService    $totalBudgetService
     */
    public function __construct(TotalBudgetService $totalBudgetService)
    {
        $this->totalBudgetService = $totalBudgetService;
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
            $organization = $this->totalBudgetService->getOrganizationData($id);
            $form = $this->totalBudgetService->formGenerator($id);
            $data = ['title'=> $element['total_budget']['label'], 'name'=>'total-budget'];

            return view('admin.organisation.forms.totalBudget.totalBudget', compact('form', 'organization', 'data'));
        } catch (\Exception $e) {
            logger()->error($e->getMessage());
            $translatedMessage = trans('common/common.error_has_occurred_while_opening_organization_reporting_org_form');

            return redirect()->route('admin.organisation.index')->with('error', $translatedMessage);
        }
    }

    /**
     * Updates organization total budget data.
     *
     * @param TotalBudgetRequest $request
     *
     * @return RedirectResponse
     */
    public function update(TotalBudgetRequest $request): RedirectResponse
    {
        try {
            if (!$this->totalBudgetService->update(Auth::user()->organization_id, $request->all())) {
                $translatedMessage = trans('organisationDetail/total_budget_controller.error_has_occurred_while_updating_organization_total_budget');

                return redirect()->route('admin.organisation.index')->with('error', $translatedMessage);
            }
            $translatedMessage = trans('organisationDetail/total_budget_controller.organization_total_budget_updated_successfully');

            return redirect()->route('admin.organisation.index')->with('success', $translatedMessage);
        } catch (\Exception $e) {
            logger()->error($e->getMessage());
            $translatedMessage = trans('organisationDetail/total_budget_controller.error_has_occurred_while_updating_organization_total_budget');

            return redirect()->route('admin.organisation.index')->with('error', $translatedMessage);
        }
    }
}
