<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\IATI\Services\OIDC\IatiOidcService;
use App\IATI\Services\OIDC\OidcAuthenticationException;
use App\IATI\Services\RegisterYourDataApi\DatasetApiService;
use App\IATI\Services\RegisterYourDataApi\IatiDataSyncService;
use App\IATI\Services\RegisterYourDataApi\ReportingOrgApiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IatiLoginController extends Controller
{
    public function __construct(
        private IatiOidcService $oidcService,
        private IatiDataSyncService $dataSyncService,
        private DatasetApiService $datasetApiService,
        private ReportingOrgApiService $reportingOrgApiService
    ) {
    }

    /**
     * Delegates the responsibility of starting the login flow to the OIDC service.
     */
    public function redirectToProvider(): void
    {
        $this->oidcService->redirectToProvider();
    }

    /**
     * Handles the OIDC callback by orchestrating the service and session management.
     */
    public function handleProviderCallback(): RedirectResponse
    {
        try {
            $authResult = $this->oidcService->handleCallback();

            $publisherOrg = null;
            $publisherOrgUUID = null;
            $publisherUserRole = 'general_user';

            $reportingOrgs = $this->reportingOrgApiService->getReportingOrgs($authResult->accessToken, ['include_meta' => 'yes', 'include_actions' => 'yes']);

            DB::beginTransaction();

            if (!empty($reportingOrgs)) {
                $firstOrg = $reportingOrgs[0];
                $publisherOrgUUID = $firstOrg['id'] ?? null;
                $publisherUserRole = $this->dataSyncService->mapRegisterRoleToPublisher($firstOrg['user_role'] ?? $publisherUserRole);

                if ($publisherUserRole !== 'iati_admin') {
                    if ($publisherOrgUUID) {
                        $reportingOrgMetadata = $firstOrg['metadata'] ?? [];
                        $publisherOrg = $this->dataSyncService->syncOrganizationDownstream(
                            $publisherOrgUUID,
                            $reportingOrgMetadata
                        );
                        $__ = $this->dataSyncService->syncSettings($publisherOrg);
                    }
                }
            }

            $user = $this->dataSyncService->syncUserFromClaims(
                $authResult->uuid,
                $authResult->claims,
                $publisherOrg?->id,
                $publisherUserRole
            );

            DB::commit();

            cache()->put('oidc_id_token', $authResult->idToken);

            auth()->login($user);

            session([
                'oidc_id_token'     => $authResult->idToken,
                'oidc_access_token' => $authResult->accessToken,
                'uuid'              => $publisherOrgUUID,
                'role_id'           => $user->role_id,
            ]);

            if (isSuperAdmin()) {
                session(['superadmin_user_id' => $user->id]);
            }

            return redirect()->intended('/');
        } catch (OidcAuthenticationException $e) {
            DB::rollBack();
            Log::error('OIDC Authentication Failed', ['message' => $e->getMessage()]);

            return redirect()
                ->route('login')
                ->withErrors(['message' => 'Authentication error: ' . $e->getMessage()]);
        }
    }

    /**
     * Handles logging the user out of the local app and the central OIDC session.
     */
    public function logout(): RedirectResponse
    {
        $idTokenHint = session('oidc_id_token');

        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();

        $this->oidcService->logout($idTokenHint);

        return redirect('/');
    }

    public function showOrganizationMissingPage()
    {
        return view('auth.onboarding.organization-missing');
    }

    public function testPublish()
    {
        DB::enableQueryLog();

        $rawAccessToken = session('oidc_access_token');
        $accessToken = trim($rawAccessToken);

        $org = auth()->user()->organization;

        $newDatasetPayload = [
            /* Accessors: getHumanReadableName, sourceType */
            'human_readable_name'   => $org->human_readable_name . 'aaa',
            'source_type'           => $org->source_type,
            'short_name'            => $org->publisher_id,
            'url'                   => awsUrl('xml/mergedActivityXml/ztest-activities.xml'),
            'visibility'            => 'public',
            'licence_id'            => $org->data_license,
            'owner_organisation_id' => $org->uuid,
        ];

        $queries = DB::getQueryLog();

        $oldDatasets = $this->reportingOrgApiService->getDatasetsForOrganisation($accessToken, session('uuid'));
        $newDataset = $this->datasetApiService->createDataset($accessToken, $newDatasetPayload);
        $allDatasets1 = $this->reportingOrgApiService->getDatasetsForOrganisation($accessToken, session('uuid'));

        $republishData = $this->datasetApiService->updateDataset($accessToken, 'a397d965-043f-c09b-137c-6915a820af7e', $newDatasetPayload);
        $allDatasets2 = $this->reportingOrgApiService->getDatasetsForOrganisation($accessToken, session('uuid'));

        dd($oldDatasets, $allDatasets1, $republishData, $allDatasets2);
    }
}
