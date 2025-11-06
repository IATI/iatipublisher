<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\IATI\Models\Organization\Organization;
use App\IATI\Models\User\Role;
use App\IATI\Models\User\User;
use App\IATI\Services\OIDC\IatiOidcService;
use App\IATI\Services\OIDC\OidcAuthenticationException;
use App\IATI\Services\OIDC\OidcAuthenticationResult;
use App\IATI\Services\RegisterYourDataApi\RegisterYourDataApiException;
use App\IATI\Services\RegisterYourDataApi\ReportingOrgApiService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class IatiLoginController extends Controller
{
    public function __construct(private IatiOidcService $oidcService, private ReportingOrgApiService $reportingOrgApiService)
    {
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
            // 1. Delegate OIDC protocol handling to the service.
            $authResult = $this->oidcService->handleCallback();

            //check on the reporting-orgs api to see if the user has access to the $authResult->org_id
            $reporting_orgs = $this->reportingOrgApiService->getReportingOrgs($authResult->accessToken, ['include_meta' => 'no', 'include_actions' => 'no']);

            if (count($reporting_orgs) > 0) {
                $reporting_org_metadata = $reporting_orgs[0]['metadata'];

                $organization = $this->syncOrganisationFromClaims($reporting_orgs[0]['id'], $reporting_org_metadata);
            }

            // 2. Use the trusted result to synchronize the local user record.
            $user = $this->syncUserFromClaims($authResult->subject, $authResult->claims, $organization?->id, $reporting_orgs[0]['user_role']);

            // 3. Store the necessary tokens in the session.
            session([
                'oidc_id_token'     => $authResult->idToken,
                'oidc_access_token' => $authResult->accessToken,
                'org_uuid'          => Arr::get($authResult->claims, 'org_id', null),
            ]);

            cache()->put('oidc_access_token', $authResult->accessToken);
            // 4. Log the user into the local application.
            Auth::login($user);

            // 5. Redirect to the intended destination.
            return redirect()->intended('/');
        } catch (OidcAuthenticationException $e) {
            Log::error('OIDC Authentication Failed', ['message' => $e->getMessage()]);

            return redirect()->route('login')->withErrors(['message' => 'Authentication error: ' . $e->getMessage()]);
        }
    }

    /**
     * Synchronizes a local user record from the trusted claims provided by the OIDC service.
     */
    private function syncUserFromClaims(string $sub, array $claims, int|null $orgId, string $role): User
    {
        $email = Arr::get($claims, 'email');
        $user = User::where('email', $email)->first();

        if ($user) {
            $user->update([
                'email'              => $email,
                'full_name'          => $this->extractName($claims),
                'username'           => $this->extractName($claims),
                'last_logged_in'     => now(),
                'preferred_username' => Arr::get($claims, 'preferred_username'),
                'given_name'         => Arr::get($claims, 'given_name'),
                'family_name'        => Arr::get($claims, 'family_name'),
                'locale'             => Arr::get($claims, 'locale'),
                'picture'            => Arr::get($claims, 'picture'),
                'organization_id'    => $orgId,
                'role_id'            => Role::where('role', $this->mapRegisterRoleToPublisher($role))->value('id'),
            ]);
        } else {
            $user = User::create([
                'sub'                     => $sub,
                'email'                   => $email,
                'password'                => null,
                'username'                => $email ?: $sub,
                'full_name'               => $this->extractName($claims),
                'address'                 => Arr::get($claims, 'address'),
                'is_active'               => true,
                'email_verified_at'       => now(),
                'role_id'                 => Role::where('role', $this->mapRegisterRoleToPublisher($role))->value('id'),
                'status'                  => true,
                'language_preference'     => Arr::get($claims, 'locale', 'en'),
                'last_logged_in'          => now(),
                'sign_on_method'          => 'oidc',
                'preferred_username'      => Arr::get($claims, 'preferred_username'),
                'given_name'              => Arr::get($claims, 'given_name'),
                'family_name'             => Arr::get($claims, 'family_name'),
                'locale'                  => Arr::get($claims, 'locale'),
                'picture'                 => Arr::get($claims, 'picture'),
                'organization_id'         => $orgId,
                'migrated_from_aidstream' => false,
            ]);
        }

        return $user;
    }

    /**
     * Synchronizes organisation.
     */
    private function syncOrganisationFromClaims(string $uuid, array $data): Organization
    {
        $organization = Organization::updateOrCreate(
            ['identifier' => $data['organisation_identifier']],
            [
                'org_uuid' => $uuid,
                'publisher_id' => $data['short_name'],
                'publisher_name' => $data['human_readable_name'],
                'publisher_type' => $data['organisation_type'],
                'address' => $data['address'],
                'telephone' => $data['phone'],
                'reporting_org' => [
                    [
                        'ref' => $data['organisation_identifier'],
                        'type' => $data['organisation_type'],
                        'secondary_reporter' => $data['reporting_source_type'],
                        'narrative' => [
                            'narrative' => $data['description'],
                            'language' => 'en',
                        ],
                    ],
                ],
                'country' => $data['hq_country'],
                'status' => 'draft',
                'iati_status' => 'pending',
                'is_published' => false,
                'org_status' => 'active',
                'migrated_from_aidsteam' => false,
                'registration_type' => 'existing_org',
                'registry_approved' => $data['registry_approved'],
            ]
        );

        return $organization;
    }

    /**
     * Maps Iati Registery Roles with the roles on Publisher.
     *
     * @param string $registeryRole
     * @return string
     */
    public function mapRegisterRoleToPublisher(string $registeryRole = 'general_user'): string
    {
        return match ($registeryRole) {
            'provider_admin' => 'iati_admin',
            'admin' => 'admin',
            'editor' => 'admin',
            'contributor' => 'general_user',
            default => 'general_user'
        };
    }

    /**
     * Extracts a displayable name from various OIDC claims.
     */
    private function extractName(array $claims): string
    {
        $name = Arr::get($claims, 'name') ?? Arr::get($claims, 'preferred_username');
        if (empty($name)) {
            $givenName = Arr::get($claims, 'given_name');
            $familyName = Arr::get($claims, 'family_name');
            $name = trim("$givenName $familyName");
        }

        return $name ?: 'User-' . substr($claims['sub'] ?? 'unknown', 0, 8);
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

    /**
     * The API testing method also does not need to change. Its dependency
     * is on the session, which this controller is now responsible for managing.
     */
    public function testMyOrgApi()
    {
        try {
            $accessToken = session('oidc_access_token');

            $reportingOrgs = $this->reportingOrgApiService->getReportingOrgDetails($accessToken, session('org_uuid'));

            dd([
                'message' => 'Successfully fetched data from ReportingOrgService!',
                'reporting_orgs' => $reportingOrgs,
            ]);
        } catch (RegisterYourDataApiException $e) {
            dd([
                'error' => 'The API call failed.',
                'message' => $e->getMessage(),
                'statusCode' => $e->getCode(),
                'response_body' => $e->getPrevious()?->response?->body(),
            ]);
        }
    }

    /**
     * Retrieves the API access token from the session.
     * This method's logic does not change.
     */
    public function getApiAccessToken(): string
    {
        $accessToken = session('oidc_access_token');

        if (!$accessToken) {
            throw new Exception('API Access Token not found in session. Please log in again.');
        }

        return $accessToken;
    }

    public function showOrganizationMissingPage()
    {
        return view('auth.onboarding.organization-missing');
    }

//    private function syncOrgUsingUUID(OidcAuthenticationResult $authResult) {
//        $orgUUID = $authResult->claims['org_id'];
//        $orgDataInDatabase =  Organization::where('org_uuid', $authResult->claims['org_id'])->first();
//
//        $orgData = '';
//        if(!$orgDataInDatabase) {
//            $orgData = $this->reportingOrgApiService->getReportingOrgs($authResult->accessToken);
//        }
//
//        dd($orgData);
//    }
}
