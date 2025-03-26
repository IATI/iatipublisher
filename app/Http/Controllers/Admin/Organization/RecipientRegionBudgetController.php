<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Organization;

use App\Http\Controllers\Controller;
use App\Http\Requests\Organization\RecipientRegionBudget\RecipientRegionBudgetRequest;
use App\IATI\Services\Organization\RecipientRegionBudgetService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

/**
 * Class RecipientRegionBudgetController.
 */
class RecipientRegionBudgetController extends Controller
{
    /**
     * @var RecipientRegionBudgetService
     */
    protected RecipientRegionBudgetService $recipientRegionBudgetService;

    /**
     * RecipientRegionBudgetController Constructor.
     *
     * @param RecipientRegionBudgetService    $recipientRegionBudgetService
     */
    public function __construct(RecipientRegionBudgetService $recipientRegionBudgetService)
    {
        $this->recipientRegionBudgetService = $recipientRegionBudgetService;
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
            $organization = $this->recipientRegionBudgetService->getOrganizationData($id);
            $form = $this->recipientRegionBudgetService->formGenerator($id, Arr::get($organization->deprecation_status_map, 'recipient_region_budget', []));
            $data = ['title' => $element['recipient_region_budget']['label'], 'name' => 'recipient_region_budget'];

            return view('admin.organisation.forms.recipientRegionBudget.recipientRegionBudget', compact('form', 'organization', 'data'));
        } catch (\Exception $e) {
            logger()->error($e->getMessage());
            $translatedMessage = trans('common/common.error_opening_data_entry_form');

            return redirect()->route('admin.organisation.index')->with('error', $translatedMessage);
        }
    }

    /**
     * Updates organization total budget data.
     *
     * @param RecipientRegionBudgetRequest $request
     *
     * @return RedirectResponse
     */
    public function update(RecipientRegionBudgetRequest $request): RedirectResponse
    {
        try {
            if (!$this->recipientRegionBudgetService->update(Auth::user()->organization_id, $request->all())) {
                $translatedMessage = trans('common/common.failed_to_update_data');

                return redirect()->route('admin.organisation.index')->with('error', $translatedMessage);
            }
            $translatedMessage = trans('common/common.updated_successfully');

            return redirect()->route('admin.organisation.index')->with('success', $translatedMessage);
        } catch (\Exception $e) {
            logger()->error($e->getMessage());
            $translatedMessage = trans('common/common.failed_to_update_data');

            return redirect()->route('admin.organisation.index')->with('error', $translatedMessage);
        }
    }
}
