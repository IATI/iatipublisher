<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Organization;

use App\Http\Controllers\Controller;
use App\Http\Requests\Organization\OrganizationIdentifier\OrganizationIdentifierRequest;
use App\IATI\Services\Organization\OrganizationIdentifierService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Class OrganizationIdentifierController.
 */
class OrganizationIdentifierController extends Controller
{
    /**
     * @var OrganizationIdentifierService
     */
    protected OrganizationIdentifierService $organizationIdentifierService;

    /**
     * OrganizationIdentifierController Constructor.
     *
     * @param OrganizationIdentifierService    $organizationIdentifierService
     */
    public function __construct(OrganizationIdentifierService $organizationIdentifierService)
    {
        $this->organizationIdentifierService = $organizationIdentifierService;
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
            $organization = $this->organizationIdentifierService->getOrganizationData($id);
            $form = $this->organizationIdentifierService->formGenerator($id, deprecationStatusMap: Arr::get($organization->deprecation_status_map, 'organization_identifier', []));
            $data = ['title' => $element['organisation_identifier']['label'], 'name' => 'organisation-identifier'];

            return view('admin.organisation.forms.organisationIdentifier.edit', compact('form', 'organization', 'data'));
        } catch (\Exception $e) {
            logger()->error($e);
            $translatedMessage = trans('common/common.error_opening_data_entry_form');

            return redirect()->route('admin.activities.show', $id)->with('error', $translatedMessage);
        }
    }

    /**
     * Updates organization identifier data.
     *
     * @param OrganizationIdentifierRequest $request
     *
     * @return RedirectResponse
     * @throws GuzzleException
     */
    public function update(OrganizationIdentifierRequest $request): RedirectResponse
    {
        try {
            $id = Auth::user()->organization_id;
            $organizationIdentifier = $request->all();

            DB::beginTransaction();

            if ($this->organizationIdentifierService->update($id, $organizationIdentifier)) {
                DB::commit();
                $translatedMessage = trans('common/common.updated_successfully');

                return redirect()->route('admin.organisation.index')->with('success', $translatedMessage);
            }

            DB::rollBack();
            $translatedMessage = trans('common/common.failed_to_update_data');

            return redirect()->route('admin.organisation.index')->with('error', $translatedMessage);
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error($e);
            $translatedMessage = trans('common/common.failed_to_update_data');

            return redirect()->route('admin.organisation.index')->with('error', $translatedMessage);
        }
    }
}
