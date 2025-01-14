<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Organization;

use App\Http\Controllers\Controller;
use App\Http\Requests\Organization\RecipientOrgBudget\RecipientOrgBudgetRequest;
use App\IATI\Services\Organization\RecipientOrgBudgetService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

/**
 * Class RecipientOrgBudgetController.
 */
class RecipientOrgBudgetController extends Controller
{
    /**
     * @var RecipientOrgBudgetService
     */
    protected RecipientOrgBudgetService $recipientOrgBudgetService;

    /**
     * RecipientOrgBudgetController Constructor.
     *
     * @param RecipientOrgBudgetService    $recipientOrgBudgetService
     */
    public function __construct(RecipientOrgBudgetService $recipientOrgBudgetService)
    {
        $this->recipientOrgBudgetService = $recipientOrgBudgetService;
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
            $organization = $this->recipientOrgBudgetService->getOrganizationData($id);
            $form = $this->recipientOrgBudgetService->formGenerator($id, Arr::get($organization->deprecation_status_map, 'recipient_org_budget', []));
            $data = ['title' => $element['recipient_org_budget']['label'], 'name' => 'recipient_org_budget'];
            $translatedData = trans('organisationDetail/recipient_org_budget_controller.error_has_occurred_while_opening_organization_recipient_org_budget_form');

            return view('admin.organisation.forms.recipientOrgBudget.recipientOrgBudget', compact('form', 'organization', 'data'));
        } catch (\Exception $e) {
            logger()->error($e->getMessage());
            $translatedData = trans('organisationDetail/recipient_org_budget_controller.error_has_occurred_while_opening_organization_recipient_org_budget_form');

            return redirect()->route('admin.organisation.index')->with('error', 'Error has occurred while opening organization recipient-org-budget form.');
        }
    }

    /**
     * Updates organization total budget data.
     *
     * @param RecipientOrgBudgetRequest $request
     *
     * @return RedirectResponse
     */
    public function update(RecipientOrgBudgetRequest $request): RedirectResponse
    {
        try {
            if (!$this->recipientOrgBudgetService->update(Auth::user()->organization_id, $request->all())) {
                $translatedData = trans('organisationDetail/recipient_org_budget_controller.error_has_occurred_while_updating_organization_recipient_org_budget');

                return redirect()->route('admin.organisation.index')->with('error', $translatedData);
            }
            $translatedData = trans('organisationDetail/recipient_org_budget_controller.organization_recipient_org_budget_updated_successfully');

            return redirect()->route('admin.organisation.index')->with('success', $translatedData);
        } catch (\Exception $e) {
            logger()->error($e->getMessage());
            $translatedData = trans('organisationDetail/recipient_org_budget_controller.error_has_occurred_while_updating_organization_recipient_org_budget');

            return redirect()->route('admin.organisation.index')->with('error', $translatedData);
        }
    }
}
