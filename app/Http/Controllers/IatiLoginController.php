<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\IATI\Models\User\Role;
use App\IATI\Models\User\User;
use App\IATI\Services\Audit\AuditService;
use App\IATI\Services\User\UserService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Jumbojett\OpenIDConnectClient;

class IatiLoginController extends Controller
{
    protected AuditService $auditService;

    protected UserService  $userService;

    public function __construct(AuditService $auditService, UserService $userService)
    {
        $this->auditService = $auditService;
        $this->userService = $userService;
    }

    /**
     * @throws \Jumbojett\OpenIDConnectClientException
     */
    public function redirectToProvider(): void
    {
        $this->getClient()->authenticate();
    }

    private function getClient(): OpenIDConnectClient
    {
        $config = config('services.oidc');

        $oidc = new OpenIDConnectClient(
            provider_url: $config['issuer'],
            client_id: $config['client_id'],
            client_secret: $config['client_secret']
        );

        $oidc->setRedirectURL(route('login.iati.callback'));
        $oidc->addScope([
            'openid',
            'email',
            'address',
            'phone',
            'groups',
            'roles',
            'role',
        ]);

        //        $oidc->addAuthParam([
        //            'audience' => 'https://registry.iatistandard.org/api/v1'
        //        ]);

        //        $oidc->addAuthParam(['prompt' => 'consent']);

        return $oidc;
    }

    public function handleProviderCallback(): RedirectResponse
    {
        try {
            $oidc = $this->getClient();

            if (!$oidc->authenticate()) {
                return redirect()->route('login')->withErrors(['message' => 'Authentication failed']);
            }

            $idToken = $oidc->getIdToken();
            $accessToken = $oidc->getAccessToken();
            $claims = $oidc->getVerifiedClaims();

            logger('idToken');
            logger($idToken);

            logger('$accessToken');
            logger($accessToken);

            $sub = $claims->sub ?? null;

            if (!$sub) {
                throw new Exception('Subject claim not found in token');
            }

            $roles = $this->extractRoles($oidc, $claims);

            if (empty($roles)) {
                logger('EMPTY MA');
                logger($roles);

                $apiRoles = $this->getUserRolesFromAPI($accessToken, $sub);

                // Dump the API response for debugging
                logger([
                    'message' => 'Roles not found in tokens, trying API',
                    'access_token' => $accessToken,
                    'sub' => $sub,
                    'api_roles' => $apiRoles,
                    'id_token_claims' => (array) $claims,
                    'access_token_claims' => $this->parseJwtClaims($accessToken),
                ]);
            } else {
                logger('NOT EMPTY');
                logger($roles);
            }

            // Aahiley lai comment this,
            // $this->validateUserPermissions($roles);

            session(['oidc_id_token' => $idToken]);

            $user = $this->findOrCreateUser($sub, (array) ($claims ?? []), $roles);

            session([
                'user_roles' => $roles,
                'has_registry_access' => in_array('iati_register_your_data', $roles),
                'is_super_admin' => in_array('iati_superadmin', $roles),
            ]);

            Auth::login($user);

            return redirect()->intended('/');
        } catch (Exception $e) {
            logger()->error('OIDC Authentication Failed: ' . $e->getMessage());

            return redirect()->route('login')->withErrors($e->getMessage());
        }
    }

    private function extractRoles($oidc, $claims): array
    {
        $roles = [];

        $accessToken = $oidc->getAccessToken();
        if ($accessToken) {
            $tokenClaims = $this->parseJwtClaims($accessToken);
            if (isset($tokenClaims['roles'])) {
                $roles = $tokenClaims['roles'];
            }
        }

        if (empty($roles)) {
            $idToken = $oidc->getIdToken();
            if ($idToken) {
                $idTokenClaims = $this->parseJwtClaims($idToken);
                if (isset($idTokenClaims['roles'])) {
                    $roles = $idTokenClaims['roles'];
                }
            }
        }

        if (empty($roles) && isset($claims->roles)) {
            $roles = is_array($claims->roles) ? $claims->roles : [$claims->roles];
        }

        if (empty($roles)) {
            $possibleRoleClaims = ['role', 'groups', 'authorities', 'permissions'];

            foreach ($possibleRoleClaims as $claimName) {
                if (isset($claims->$claimName)) {
                    $roles = is_array($claims->$claimName) ? $claims->$claimName : [$claims->$claimName];
                    break;
                }
            }
        }

        logger()->info('Extracted roles for user', ['sub' => $claims->sub, 'roles' => $roles]);

        return is_array($roles) ? $roles : [];
    }

    private function parseJwtClaims(string $jwt): array
    {
        try {
            $parts = explode('.', $jwt);
            if (count($parts) !== 3) {
                return [];
            }

            $payload = base64_decode(str_replace(['-', '_'], ['+', '/'], $parts[1]));

            return json_decode($payload, true) ?: [];
        } catch (Exception $e) {
            logger()->error('Failed to parse JWT claims: ' . $e->getMessage());

            return [];
        }
    }

    private function validateUserPermissions(array $roles): void
    {
        $hasRegistryAccess = in_array('iati_register_your_data', $roles);
        $isSuperAdmin = in_array('iati_superadmin', $roles);

        if (!$hasRegistryAccess && !$isSuperAdmin) {
            throw new Exception('User does not have required permissions to access the registry. Required roles: iati_register_your_data or iati_superadmin');
        }
    }

    private function findOrCreateUser(string $sub, array $claims, array $roles): User
    {
        $email = Arr::get($claims, 'email') ?? 'temp_' . $sub . '@noemail.local';

        $user = User::where('sub', $sub)->first();

        if (!$user) {
            $user = User::where('email', $email)->first();

            if ($user && !$user->sub) {
                $user->sub = $sub;
                $user->save();
            }
        }

        if (!$user) {
            // Determine role based on IATI roles from SSO
            $roleId = $this->determineUserRole($roles, $claims);

            $user = User::create([
                'email'                   => $email,
                'password'                => null,
                'username'                => $this->extractName($claims),
                'full_name'               => $this->extractName($claims),
                'address'                 => Arr::get($claims, 'address'),
                'organization_id'         => 150,
                'is_active'               => true,
                'email_verified_at'       => now(),
                'role_id'                 => $roleId,
                'status'                  => true,
                'language_preference'     => 'en',
                'migrated_from_aidstream' => false,
                'created_at'              => now(),
                'updated_at'              => now(),
                'registration_method'     => 'existing_org',
                'last_logged_in'          => now(),
                'sub'                     => Arr::get($claims, 'sub'),
                'preferred_username'      => Arr::get($claims, 'preferred_username'),
                'given_name'              => Arr::get($claims, 'given_name'),
                'family_name'             => Arr::get($claims, 'family_name'),
                'locale'                  => Arr::get($claims, 'locale'),
                'picture'                 => Arr::get($claims, 'picture'),
                'sign_on_method'          => 'oidc',
            ]);
        } else {
            // Update existing user's role if needed
            $newRoleId = $this->determineUserRole($roles, $claims);
            if ($user->role_id !== $newRoleId) {
                $user->role_id = $newRoleId;
                $user->last_logged_in = now();
                $user->save();
            }
        }

        return $user;
    }

    private function determineUserRole(array $roles, array $claims): int
    {
        // If user has iati_superadmin role
        if (in_array('iati_superadmin', $roles)) {
            $role = Role::where('role', 'iati_admin')->first();
            if ($role) {
                return $role->id;
            }
        }

        // Check if user is from IATI organization (fallback to existing logic)
        if (Arr::get($claims, 'org_handle') === 'iati') {
            $role = Role::where('role', 'iati_admin')->first();
            if ($role) {
                return $role->id;
            }
        }

        // Default role for users with iati_register_your_data permission
        $role = Role::where('role', 'admin')->first();

        return $role ? $role->id : 1;
    }

    private function extractName($claims): string
    {
        return Arr::get($claims, 'name') ?? Arr::get($claims, 'preferred_username') ?? Arr::get(
            $claims,
            'given_name'
        ) ?? Arr::get($claims, 'family_name') ?? 'User-' . substr($claims['sub'], 0, 8);
    }

    /**
     * @return RedirectResponse
     *
     * @throws \Jumbojett\OpenIDConnectClientException
     */
    public function logout(): RedirectResponse
    {
        $idToken = session('oidc_id_token');
        $config = config('services.oidc');

        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();

        if ($idToken && !empty($config['logout_endpoint']) && !empty($config['logout_redirect_uri'])) {
            $this->getClient()->signOut($idToken, $config['logout_redirect_uri']);
        }

        return redirect('/');
    }

    public function getApiAccessToken($userAccessToken)
    {
        $config = config('services.oidc');

        $response = Http::asForm()->post($config['token_endpoint'], [
            'grant_type'         => 'urn:ietf:params:oauth:grant-type:token-exchange',
            'client_id'          => $config['client_id'],
            'client_secret'      => $config['client_secret'],
            'subject_token'      => $userAccessToken, // Token from OIDC login
            'subject_token_type' => 'urn:ietf:params:oauth:token-type:access_token',
            'audience'           => 'https://registry.iatistandard.org/api/v1',
            'scope'              => 'register_your_data ryd_org:read ryd_org:create ryd_dataset:read ryd_dataset:update ryd_org:user_admin',
        ]);

        return $response->json()['access_token'];
    }

    /**
     * Helper method to check if current user has specific IATI role.
     */
    public function hasRole(string $role): bool
    {
        $userRoles = session('user_roles', []);

        return in_array($role, $userRoles);
    }

    /**
     * Helper method to get all user roles from session.
     */
    public function getUserRoles(): array
    {
        return session('user_roles', []);
    }

    /**
     * Make a direct API call to Asgardeo to get user roles
     * This is a fallback if roles are not available in tokens.
     */
    private function getUserRolesFromAPI($accessToken, $userId): array
    {
        try {
            $config = config('services.oidc');

            // Try different Asgardeo API endpoints that might contain role information
            $endpoints = [
                // Standard userinfo endpoint
                $config['userinfo_endpoint'],
                $config['token_endpoint'],
//                // Asgardeo specific endpoints (these might need adjustment)
//                $config['issuer'] . '/scim2/Users/' . $userId,
//                $config['issuer'] . '/me',
            ];

            foreach ($endpoints as $endpoint) {
                logger()->info("Trying to fetch roles from: {$endpoint}");

                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Accept' => 'application/json',
                ])->get($endpoint);

                if ($response->successful()) {
                    $userData = $response->json();
                    logger()->info("API Response from {$endpoint}", $userData);

                    // Check various possible locations for roles
                    $possibleRoleFields = ['roles', 'role', 'groups', 'authorities', 'permissions', 'entitlements'];

                    foreach ($possibleRoleFields as $field) {
                        if (isset($userData[$field]) && !empty($userData[$field])) {
                            $roles = is_array($userData[$field]) ? $userData[$field] : [$userData[$field]];
                            logger()->info("Found roles in API field: {$field}", $roles);

                            return $roles;
                        }
                    }
                } else {
                    logger()->warning("Failed to fetch from {$endpoint}: " . $response->status());
                }
            }
        } catch (Exception $e) {
            logger()->error('Failed to fetch user roles from API: ' . $e->getMessage());
        }

        return [];
    }
}
