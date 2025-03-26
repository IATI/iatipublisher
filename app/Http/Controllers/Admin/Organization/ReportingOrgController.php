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
            $element = readOrganizationElementJsonSchema();
            $organization = $this->reportingOrgService->getOrganizationData($id);
            $form = $this->reportingOrgService->formGenerator($id, Arr::get($organization->deprecation_status_map, 'reporting_org', []));
            $data = ['title'=> $element['reporting_org']['label'], 'name'=>'reporting-org'];

            return view('admin.organisation.forms.reportingOrg.reportingOrg', compact('form', 'organization', 'data'));
        } catch (\Exception $e) {
            logger()->error($e->getMessage());
            $translatedMessage = trans('common/common.error_opening_data_entry_form');

            return redirect()->route('admin.organisation.index')->with('error', $translatedMessage);
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
                $translatedMessage = trans('common/common.failed_to_update_data');

                return $request->expectsJson() ?
                    response()->json(['success' => false, 'error' => $translatedMessage], 500) :
                    redirect()->route('admin.organisation.index')->with('error', $translatedMessage);
            }

            DB::commit();

            $translatedMessage = trans('common/common.updated_successfully');

            return $request->expectsJson() ?
                response()->json(['success' => true, 'message' => $translatedMessage]) :
                redirect()->route('admin.organisation.index')->with('success', $translatedMessage);
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error($e->getMessage());
            $translatedMessage = trans('common/common.failed_to_update_data');

            return $request->expectsJson() ?
                response()->json(['success' => false, 'error' => $translatedMessage], 500) :
                redirect()->route('admin.organisation.index')->with('error', $translatedMessage);
        }
    }
}
