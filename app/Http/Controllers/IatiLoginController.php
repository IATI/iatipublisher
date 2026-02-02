<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\IATI\Services\OIDC\IatiOidcService;
use App\IATI\Services\OIDC\OidcAuthenticationException;
use App\IATI\Services\RegisterYourDataApi\IatiDataSyncService;
use App\IATI\Services\RegisterYourDataApi\ReportingOrgApiService;
use Exception;
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
    public function handleProviderCallback()
    {
        try {
            $authResult = $this->oidcService->handleCallback();
            $firstOrg = null;

            session([
                'oidc_id_token'                => $authResult->idToken,
                'oidc_access_token'            => $authResult->accessToken,
                'oidc_refresh_token'           => $authResult->refreshToken,
                'oidc_access_token_expires_at' => $authResult->expiresIn
                    ? now()->addSeconds($authResult->expiresIn - 60)->toIso8601String()
                    : null,
            ]);

            $publisherOrg = null;
            $publisherOrgUUID = null;
            $publisherUserRole = in_array('iati_superadmin', data_get($authResult->claims, 'roles', []), true) ? 'iati_superadmin' : 'admin';

            DB::beginTransaction();

            if ($publisherUserRole !== 'iati_superadmin') {
                $reportingOrgs = $this->reportingOrgApiService->getReportingOrgs($authResult->accessToken, ['include_meta' => 'yes', 'include_actions' => 'yes']);
                $firstOrg = data_get($reportingOrgs, 0);

                if (count($reportingOrgs) > 1) {
                    $this->showNotSupportMultipleOrgsPage();
                } elseif (!empty($reportingOrgs) && count($reportingOrgs) === 1) {
                    // check if role is contributor_pending
                    if ($firstOrg['user_role'] === 'contributor_pending') {
                        $this->showYouArePendingApprovalPage();
                    }

                    $publisherOrgUUID = data_get($firstOrg, 'id');

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

            $publisherUserRole = $this->dataSyncService->mapRegisterRoleToPublisher(data_get($firstOrg, 'user_role', $publisherUserRole));
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
                'uuid'    => $publisherOrgUUID,
                'role_id' => $user->role_id,
            ]);

            if (isSuperAdmin()) {
                session(['superadmin_user_id' => $user->id]);
            }

            return redirect()->intended('/');
        } catch (OidcAuthenticationException $e) {
            DB::rollBack();
            Log::error('OIDC Authentication Failed', ['message' => $e->getMessage()]);

            $this->logout();

            return redirect()
                ->route('web.index.login')
                ->withErrors(['message' => 'Authentication error: ' . $e->getMessage()]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error when OIDC login', ['message' => $e->getMessage()]);

            return $this->showErrorPage();
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

    public function showYouArePendingApprovalPage()
    {
        session(['redirect' => 'pending-approval']);

        return view('auth.onboarding.pending-approval');
    }

    public function showNotSupportMultipleOrgsPage()
    {
        session(['redirect' => 'multiple-orgs']);

        return view('auth.onboarding.multiple-orgs');
    }

    public function showErrorPage()
    {
        session(['redirect' => 'sync-error']);

        return view('auth.onboarding.sync-error');
    }
}
