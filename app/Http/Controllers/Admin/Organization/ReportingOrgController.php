<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Organization;

use App\Http\Controllers\Controller;
use App\Http\Requests\Organization\ReportingOrg\ReportingOrgRequest;
use App\IATI\Services\Organization\ReportingOrgService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Class ReportingOrgController.
 */
class ReportingOrgController extends Controller
{
    /**
     * @var ReportingOrgService
     */
    protected ReportingOrgService $reportingOrgService;

    /**
     * ReportingOrgController Constructor.
     *
     * @param ReportingOrgService    $reportingOrgService
     */
    public function __construct(ReportingOrgService $reportingOrgService)
    {
        $this->reportingOrgService = $reportingOrgService;
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
            $organization = $this->reportingOrgService->getOrganizationData($id);
            $form = $this->reportingOrgService->formGenerator($id, Arr::get($organization->deprecation_status_map, 'reporting_org', []));
            $data = ['title'=> $element['reporting_org']['label'], 'name'=>'reporting-org'];

            return view('admin.organisation.forms.reportingOrg.reportingOrg', compact('form', 'organization', 'data'));
        } catch (\Exception $e) {
            logger()->error($e->getMessage());
            $translatedData = trans('organisationDetail/reporting_org_controller.error_has_occurred_while_opening_organization_reporting_org_form');

            return redirect()->route('admin.organisation.index')->with('error', $translatedData);
        }
    }

    /**
     * Updates organization title data.
     *
     * @param ReportingOrgRequest $request
     *
     * @return RedirectResponse|JsonResponse
     */
    public function update(ReportingOrgRequest $request): JsonResponse|RedirectResponse
    {
        try {
            DB::beginTransaction();

            if (!$this->reportingOrgService->update(Auth::user()->organization_id, $request->all())) {
                $translatedData = trans('organisationDetail/reporting_org_controller.error_has_occurred_while_updating_organization_reporting_org');

                return $request->expectsJson() ?
                    response()->json(['success' => false, 'error' => $translatedData], 500) :
                    redirect()->route('admin.organisation.index')->with('error', $translatedData);
            }

            DB::commit();

            $translatedData = trans('organisationDetail/reporting_org_controller.organization_reporting_org_updated_successfully');

            return $request->expectsJson() ?
                response()->json(['success' => true, 'message' => $translatedData]) :
                redirect()->route('admin.organisation.index')->with('success', $translatedData);
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error($e->getMessage());
            $translatedData = trans('organisationDetail/reporting_org_controller.error_has_occurred_while_updating_organization_reporting_org');

            return $request->expectsJson() ?
                response()->json(['success' => false, 'error' => $translatedData], 500) :
                redirect()->route('admin.organisation.index')->with('error', $translatedData);
        }
    }
}
