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
            $element = readOrganizationElementJsonSchema();
            $organization = $this->recipientOrgBudgetService->getOrganizationData($id);
            $form = $this->recipientOrgBudgetService->formGenerator($id, Arr::get($organization->deprecation_status_map, 'recipient_org_budget', []));
            $data = ['title' => $element['recipient_org_budget']['label'], 'name' => 'recipient_org_budget'];
            $translatedMessage = trans('common/common.error_opening_data_entry_form');

            return view('admin.organisation.forms.recipientOrgBudget.recipientOrgBudget', compact('form', 'organization', 'data'));
        } catch (\Exception $e) {
            logger()->error($e);
            $translatedMessage = trans('common/common.error_opening_data_entry_form');

            return redirect()->route('admin.organisation.index')->with('error', $translatedMessage);
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
                $translatedMessage = trans('common/common.failed_to_update_data');

                return redirect()->route('admin.organisation.index')->with('error', $translatedMessage);
            }
            $translatedMessage = trans('common/common.updated_successfully');

            return redirect()->route('admin.organisation.index')->with('success', $translatedMessage);
        } catch (\Exception $e) {
            logger()->error($e);
            $translatedMessage = trans('common/common.failed_to_update_data');

            return redirect()->route('admin.organisation.index')->with('error', $translatedMessage);
        }
    }
}
