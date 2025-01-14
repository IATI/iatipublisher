<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Activity;

use App\Http\Controllers\Controller;
use App\Http\Requests\Activity\DocumentLink\DocumentLinkRequest;
use App\IATI\Services\Activity\DocumentLinkService;
use App\IATI\Traits\EditFormTrait;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Database\DatabaseManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;

/**
 * Class DocumentLinkController
 *.
 */
class DocumentLinkController extends Controller
{
    use EditFormTrait;
    /**
     * @var DocumentLinkService
     */
    protected DocumentLinkService $documentLinkService;

    /**
     * @var DatabaseManager
     */
    protected DatabaseManager $db;

    /**
     * DocumentLinkControllerConstructor.
     *
     * @param DocumentLinkService $documentLinkService
     * @param DatabaseManager     $db
     */
    public function __construct(
        DocumentLinkService $documentLinkService,
        DatabaseManager $db
    ) {
        $this->documentLinkService = $documentLinkService;
        $this->db = $db;
    }

    /**
     * Renders country budget item edit form.
     *
     * @param int $id
     *
     * @return View|RedirectResponse
     */
    public function edit(int $id): View|RedirectResponse
    {
        try {
            $element = getElementSchema('document_link');
            $activity = $this->documentLinkService->getActivityData($id);
            $deprecationStatusMap = Arr::get($activity->deprecation_status_map, 'document_link', []);
            $form = $this->documentLinkService->formGenerator(
                id                        : $id,
                activityDefaultFieldValues: $activity->default_field_values ?? [],
                deprecationStatusMap      : $deprecationStatusMap
            );

            $hasData = (bool) Arr::get($activity, 'document_link', false);
            $formHeader = $this->getFormHeader(
                hasData    : $hasData,
                elementName: 'document_link',
                parentTitle: Arr::get($activity, 'title.0.narrative', 'Untitled')
            );
            $breadCrumbInfo = $this->basicBreadCrumbInfo($activity, 'document_link');

            $data = [
                'title'            => $element['label'],
                'name'             => 'document_link',
                'form_header'      => $formHeader,
                'bread_crumb_info' => $breadCrumbInfo,
            ];

            return view('admin.activity.documentLink.edit', compact('form', 'activity', 'data'));
        } catch (Exception $e) {
            logger()->error($e->getMessage());
            $translatedData = trans('activity_detail/document_link_controller.error_has_occurred_while_rendering_document_link_form');

            return redirect()->route('admin.activity.show', $id)->with(
                'error',
                $translatedData
            );
        }
    }

    /**
     * Updates country budget item data.
     *
     * @param DocumentLinkRequest $request
     * @param                     $id
     *
     * @return JsonResponse|RedirectResponse
     * @throws \Throwable
     */
    public function update(DocumentLinkRequest $request, $id): JsonResponse|RedirectResponse
    {
        try {
            $this->db->beginTransaction();
            $documentLink = $request->except(['_token', '_method']);
            $this->documentLinkService->update($id, $documentLink);
            $this->db->commit();

            $translatedData = trans('activity_detail/document_link_controller.document_link_updated_successfully');

            return redirect()->route('admin.activity.show', $id)->with('success', $translatedData);
        } catch (Exception $e) {
            $this->db->rollBack();
            logger()->error($e->getMessage());

            $translatedData = trans('activity_detail/document_link_controller.error_has_occurred_while_updating_document_link');

            return redirect()->route('admin.activity.show', $id)->with('error', $translatedData);
        }
    }
}
