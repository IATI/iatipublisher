<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\IATI\Models\Organization\Organization;
use App\IATI\Services\OIDC\IatiOidcService;
use App\IATI\Services\OIDC\OidcAuthenticationException;
use App\IATI\Services\RegisterYourDataApi\IatiDataSyncService;
use App\IATI\Services\RegisterYourDataApi\ReportingOrgApiService;
use App\IATI\Services\RegisterYourDataApi\UserApiService;
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
        private ReportingOrgApiService $reportingOrgApiService,
        private UserApiService $userApiService
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

            $publisherUserRole = in_array('iati_superadmin', data_get($authResult->claims, 'roles', []), true) ? 'iati_superadmin' : 'contributor_pending';

            DB::beginTransaction();

            $reportingOrgs = [];
            $syncOrgs = [];

            //check if normal users are logging in
            if ($publisherUserRole !== 'iati_superadmin') {
                $reportingOrgs = $this->reportingOrgApiService->getReportingOrgs($authResult->accessToken, ['include_meta' => 'yes', 'include_actions' => 'yes']);

                // when reporting orgs is 0 we check if we got provider admin access first
                if (count($reportingOrgs) === 0) {
                    $accessibleReportingOrgs = $this->userApiService->getProviderAdminOrganisationAccesss($authResult->accessToken, $authResult->claims['iatiRegistryId']);
                    $orgsWithProviderAdminAccess = $this->dataSyncService->getOnlyProviderAdminList($accessibleReportingOrgs);

                    if (count($orgsWithProviderAdminAccess) !== 0) {
                        $this->dataSyncService->syncAccessibleReportingOrgs($orgsWithProviderAdminAccess);
                        $publisherUserRole = 'provider_admin';
                    } else {
                        DB::commit();

                        return $this->showOrganizationMissingPage();
                    }
                } else {
                    //sync all orgs at once
                    foreach ($reportingOrgs as $org) {
                        $publisherOrg = $this->dataSyncService->syncOrganizationDownstream(data_get($org, 'id'), $org['metadata'] ?? []);
                        $this->dataSyncService->syncSettings($publisherOrg);
                        $syncOrgs[] = $publisherOrg;
                    }

                    $firstOrg = data_get($reportingOrgs, 0);

                    if (count($reportingOrgs) === 1) {
                        if ($firstOrg['user_role'] === 'contributor_pending') {
                            DB::commit();

                            return $this->showYouArePendingApprovalPage();
                        }
                    }
                }
            }

            $publisherUserRole = $this->dataSyncService->mapRegisterRoleToPublisher(data_get($firstOrg, 'user_role', $publisherUserRole));

            $user = $this->dataSyncService->syncUserFromClaims(
                $authResult->uuid,
                $authResult->claims,
                count($syncOrgs) === 1 ? $syncOrgs[0]->id : null,
                $publisherUserRole
            );

            // Empty for superadmins and provider admins, who have no organization_user rows
            if (!empty($reportingOrgs)) {
                $this->dataSyncService->syncUserOrganizations($user, $reportingOrgs);
            }

            DB::commit();

            cache()->put('oidc_id_token', $authResult->idToken);
            auth()->login($user);

            session([
                'uuid'    => $user->organization?->uuid,
                'role_id' => $user->role_id,
            ]);

            if (isSuperAdmin()) {
                session(['superadmin_user_id' => $user->id]);
            }

            // If user belongs to multiple orgs and hasn't picked one yet (or we want to force pick)
            if (count($reportingOrgs) > 1) {
                return redirect()->route('onboarding.select-organization');
            }

            if ($user->organization) {
                $this->dataSyncService->syncMissingDatasetUuids($authResult->accessToken, $user->organization);
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

    public function showOrganizationSelectionPage()
    {
        $user = auth()->user();
        if (!$user) {
            return redirect()->route('web.index.login');
        }

        $organizations = $user->organizations;

        if ($organizations->isEmpty()) {
            return $this->showOrganizationMissingPage();
        }

        if ($organizations->count() === 1) {
            if ($organizations->first()->pivot->role === 'contributor_pending') {
                return $this->showYouArePendingApprovalPage();
            }

            $user->update(['organization_id' => $organizations->first()->id]);

            return redirect()->intended('/');
        }

        return view('auth.onboarding.select-organization', compact('organizations'));
    }

    public function selectOrganization(string $orgUuid)
    {
        $user = auth()->user();
        $organization = Organization::where('uuid', $orgUuid)->firstOrFail();

        // Check if user belongs to this org
        $membership = $user->organizations->firstWhere('id', $organization->id);

        if (!$membership) {
            abort(403);
        }

        if ($membership->pivot->role === 'contributor_pending') {
            return redirect()->route('onboarding.select-organization');
        }

        $user->update(['organization_id' => $organization->id]);

        if ($accessToken = session('oidc_access_token')) {
            $this->dataSyncService->syncMissingDatasetUuids($accessToken, $organization);
        }

        session([
            'uuid'    => $organization->uuid,
            'role_id' => $user->role_id,
        ]);

        return redirect()->intended('/');
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

    public function showErrorPage()
    {
        session(['redirect' => 'sync-error']);

        return view('auth.onboarding.sync-error');
    }
}
