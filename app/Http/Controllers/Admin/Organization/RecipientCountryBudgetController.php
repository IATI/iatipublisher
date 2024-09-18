<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Organization;

use App\Http\Controllers\Controller;
use App\Http\Requests\Organization\RecipientCountryBudget\RecipientCountryBudgetRequest;
use App\IATI\Services\Organization\RecipientCountryBudgetService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

/**
 * Class RecipientCountryBudgetController.
 */
class RecipientCountryBudgetController extends Controller
{
    /**
     * @var RecipientCountryBudgetService
     */
    protected RecipientCountryBudgetService $recipientCountryBudgetService;

    /**
     * RecipientCountryBudgetController Constructor.
     *
     * @param RecipientCountryBudgetService    $recipientCountryBudgetService
     */
    public function __construct(RecipientCountryBudgetService $recipientCountryBudgetService)
    {
        $this->recipientCountryBudgetService = $recipientCountryBudgetService;
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
            $organization = $this->recipientCountryBudgetService->getOrganizationData($id);

            $form = $this->recipientCountryBudgetService->formGenerator($id, deprecationStatusMap: Arr::get($organization->deprecation_status_map, 'recipient_country_budget', []));

            $data = ['title' => $element['recipient_country_budget']['label'], 'name' => 'recipient_country_budget'];

            return view('admin.organisation.forms.recipientCountryBudget.recipientCountryBudget', compact('form', 'organization', 'data'));
        } catch (\Exception $e) {
            logger()->error($e->getMessage());
            $translatedMessage = trans('common/common.error_opening_data_entry_form');

            return redirect()->route('admin.organisation.index')->with('error', $translatedMessage);
        }
    }

    /**
     * Updates organization total budget data.
     *
     * @param RecipientCountryBudgetRequest $request
     *
     * @return RedirectResponse
     */
    public function update(RecipientCountryBudgetRequest $request): RedirectResponse
    {
        try {
            if (!$this->recipientCountryBudgetService->update(Auth::user()->organization_id, $request->all())) {
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
