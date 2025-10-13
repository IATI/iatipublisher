<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Organization;

use App\Http\Controllers\Controller;
use App\Http\Requests\Organization\DocumentLink\DocumentLinkRequest;
use App\IATI\Services\Organization\DocumentLinkService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Class DocumentLinkController.
 */
class DocumentLinkController extends Controller
{
    /**
     * var DocumentLinkService.
     */
    protected DocumentLinkService $documentLinkService;

    /**
     * DocumentLinkController Constructor.
     *
     * @param DocumentLinkService    $documentLinkService
     */
    public function __construct(DocumentLinkService $documentLinkService)
    {
        $this->documentLinkService = $documentLinkService;
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
            $organization = $this->documentLinkService->getOrganizationData($id);
            $form = $this->documentLinkService->formGenerator($id, deprecationStatusMap: Arr::get($organization->deprecation_status_map, 'document_link', []));
            $status = $organization->document_link_element_completed ?? false;
            $data = ['status'=> $organization->document_link_element_completed ?? false, 'title'=> $element['document_link']['label'], 'name'=>'document-link'];

            return view('admin.organisation.forms.documentLink.documentLink', compact('form', 'organization', 'data'));
        } catch (\Exception $e) {
            logger()->error($e);
            $translatedMessage = trans('common/common.error_opening_data_entry_form');

            return redirect()->route('admin.organisation.index')->with('error', $translatedMessage);
        }
    }

    /**
     * Updates organization document link data.
     *
     * @param DocumentLinkRequest $request
     *
     * @return RedirectResponse
     */
    public function update(DocumentLinkRequest $request): RedirectResponse
    {
        try {
            DB::beginTransaction();
            $documentLink = $request->only(['document_link']);

            if (!$this->documentLinkService->update(Auth::user()->organization_id, $documentLink)) {
                $translatedMessage = trans('common/common.failed_to_update_data');
                DB::rollBack();

                return redirect()->route('admin.organisation.index')->with('error', $translatedMessage);
            }

            $translatedMessage = trans('common/common.updated_successfully');

            DB::commit();

            return redirect()->route('admin.organisation.index')->with('success', $translatedMessage);
        } catch (\Exception $e) {
            DB::rollBack();

            logger()->error($e);
            $translatedMessage = trans('common/common.failed_to_update_data');

            return redirect()->route('admin.organisation.index')->with('error', $translatedMessage);
        }
    }
}
