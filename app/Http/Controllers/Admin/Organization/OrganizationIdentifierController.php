<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Organization;

use App\Http\Controllers\Controller;
use App\Http\Requests\Organization\OrganizationIdentifier\OrganizationIdentifierRequest;
use App\IATI\Services\ApiLog\ApiLogService;
use App\IATI\Services\Organization\OrganizationIdentifierService;
use GuzzleHttp\Client;
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
     * @var ApiLogService
     */
    protected ApiLogService $apiLogService;

    /**
     * OrganizationIdentifierController Constructor.
     *
     * @param OrganizationIdentifierService    $organizationIdentifierService
     */
    public function __construct(OrganizationIdentifierService $organizationIdentifierService, ApiLogService $apiLogService)
    {
        $this->organizationIdentifierService = $organizationIdentifierService;
        $this->apiLogService = $apiLogService;
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
            $organization = $this->organizationIdentifierService->getOrganizationData($id);
            $form = $this->organizationIdentifierService->formGenerator($id, deprecationStatusMap: Arr::get($organization->deprecation_status_map, 'organization_identifier', []));
            $data = ['title' => $element['organisation_identifier']['label'], 'name' => 'organisation-identifier'];

            return view('admin.organisation.forms.organisationIdentifier.edit', compact('form', 'organization', 'data'));
        } catch (\Exception $e) {
            logger()->error($e->getMessage());
            $translatedMessage = trans('organisationDetail/organisation_identifier_controller.error_has_occurred_while_opening_organization_identifier_form');

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

//            if (!$this->verifyPublisher($organizationIdentifier)) {
//                return redirect()->route('admin.organisation.identifier.edit')->with('error', 'Please enter the correct identifier to match your IATI Registry account.')->withInput();
//            }

            DB::beginTransaction();

            if ($this->organizationIdentifierService->update($id, $organizationIdentifier)) {
                DB::commit();
                $translatedMessage = trans('organisationDetail/organisation_identifier_controller.organisation_identifier_updated_successfully');

                return redirect()->route('admin.organisation.index')->with('success', $translatedMessage);
            }

            DB::rollBack();
            $translatedMessage = trans('organisationDetail/organisation_identifier_controller.error_has_occurred_while_updating_organisation_identifier');

            return redirect()->route('admin.organisation.index')->with('error', $translatedMessage);
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error($e);
            $translatedMessage = trans('organisationDetail/organisation_identifier_controller.error_has_occurred_while_updating_organisation_identifier');

            return redirect()->route('admin.organisation.index')->with('error', $translatedMessage);
        }
    }

    /**
     * Verify publisher.
     *
     * @param array $data
     *
     * @return bool
     * @throws GuzzleException
     */
    public function verifyPublisher(array $data): bool
    {
        try {
            $organization = Auth::user()->organization;
            $identifier = $data['organization_registration_agency'] . '-' . $data['registration_number'];

            $client = new Client(
                [
                    'base_uri' => env('IATI_API_ENDPOINT'),
                    'headers'  => [
                        'X-CKAN-API-Key' => env('IATI_API_KEY'),
                    ],
                ]
            );
            $requestOptions = [
                'auth'            => [env('IATI_USERNAME'), env('IATI_PASSWORD')],
                'query'           => ['id' => $organization['publisher_id']],
                'connect_timeout' => 500,
            ];

            $res = $client->request('GET', env('IATI_API_ENDPOINT') . '/action/organization_show', $requestOptions);
            $this->apiLogService->store(generateApiInfo('GET', env('IATI_API_ENDPOINT') . '/action/organization_show', $requestOptions, $res));

            $response = json_decode($res->getBody()->getContents(), false, 512, JSON_THROW_ON_ERROR)->result;

            return $response->publisher_iati_id === $identifier;
        } catch (\Exception $e) {
            logger()->error($e->getMessage());

            return false;
        }
    }
}
