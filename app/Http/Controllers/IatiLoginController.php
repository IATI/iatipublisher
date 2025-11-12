<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\IATI\Services\OIDC\IatiOidcService;
use App\IATI\Services\OIDC\OidcAuthenticationException;
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

            $organization = null;
            $orgId = null;
            $userRole = null;

            $reportingOrgs = $this->reportingOrgApiService->getReportingOrgs($authResult->accessToken, ['include_meta' => 'yes', 'include_actions' => 'yes']);

            DB::beginTransaction();

            if (!empty($reportingOrgs)) {
                $firstOrg = $reportingOrgs[0];
                $orgId = $firstOrg['id'] ?? null;
                $userRole = $firstOrg['user_role'] ?? null;

                if ($orgId) {
                    $reportingOrgMetadata = $firstOrg['metadata'] ?? [];
                    $organization = $this->dataSyncService->syncOrganisationFromClaims(
                        $orgId,
                        $reportingOrgMetadata
                    );
                    $__ = $this->dataSyncService->syncSettings($organization);
                }
            }

            $user = $this->dataSyncService->syncUserFromClaims(
                $authResult->subject,
                $authResult->claims,
                $organization?->id,
                $userRole
            );

            DB::commit();

            session([
                'oidc_id_token' => $authResult->idToken,
                'oidc_access_token' => $authResult->accessToken,
                'org_uuid' => $orgId,
            ]);

            cache()->put('oidc_access_token', $authResult->accessToken);

            Auth::login($user);

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
}
