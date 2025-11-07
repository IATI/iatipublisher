<?php

declare(strict_types=1);

namespace App\IATI\Services\RegisterYourDataApi;

use App\IATI\Models\Organization\Organization;
use App\IATI\Models\Setting\Setting;
use App\IATI\Models\User\Role;
use App\IATI\Models\User\User;
use Illuminate\Support\Arr;

class IatiDataSyncService
{
    public function syncOrganisationFromClaims(string $uuid, array $data): Organization
    {
        $existingOrg = Organization::where('org_uuid', $uuid)->first();

        $publisherTypeCode = $this->mapPublisherTypeCode($data['organisation_type'] ?? null);
        $name = [['narrative' => $data['human_readable_name'] ?? null, 'language' => 'en']];
        $attributes = [
            'identifier'             => $data['organisation_identifier'],
            'org_uuid'               => $uuid,
            'publisher_id'           => $data['short_name'] ?? null,
            'publisher_name'         => $data['human_readable_name'] ?? null,
            'publisher_type'         => $publisherTypeCode,
            'address'                => $data['address'] ?? null,
            'telephone'              => $data['phone'] ?? null,
            'reporting_org'          => [
                [
                    'ref'                => $data['organisation_identifier'] ?? null,
                    'type'               => $publisherTypeCode,
                    'secondary_reporter' => $this->mapSecondaryReporter($data['reporting_source_type'] ?? null),
                    'narrative'          => $name,
                ],
            ],
            'country'                => $this->mapCountryCode($data['hq_country'] ?? null),
            'iati_status'            => 'pending',
            'org_status'             => 'active',
            'migrated_from_aidsteam' => false,
            'registration_type'      => 'existing_org',
            'registry_approved'      => $data['registry_approved'] ?? false,
        ];

        if (!$existingOrg) {
            $attributes['status'] = 'draft';
            $attributes['is_published'] = false;

            return Organization::create($attributes);
        }

        $existingOrg->fill($attributes);

        if ($existingOrg->isDirty()) {
            $existingOrg->status = 'draft';
            $existingOrg->is_published = $existingOrg->getOriginal('is_published');
            $existingOrg->save();
        }

        return $existingOrg;
    }

    private function mapPublisherTypeCode($publisherType): string|null
    {
        if (!$publisherType) {
            return null;
        }

        $codeList = getCodeList('OrganizationType', 'Organization', false);

        $matches = array_filter(
            $codeList,
            fn ($name) => strtolower($name) === strtolower($publisherType)
        );

        if (!empty($matches)) {
            return (string) array_key_first($matches);
        }

        return null;
    }

    private function mapSecondaryReporter($reportingSourceType): ?bool
    {
        return match ($reportingSourceType) {
            'primary_source'     => false,
            'secondary_reporter' => true,
            default              => null,
        };
    }

    private function mapCountryCode($countryName): string|null
    {
        if (!$countryName) {
            return null;
        }

        $codeList = getCodeList('Region', 'Activity', false);

        $matches = array_filter(
            $codeList,
            fn ($name) => strtolower($name) === strtolower($countryName)
        );

        if (!empty($matches)) {
            return (string) array_key_first($matches);
        }

        return null;
    }

    public function syncSettings(Organization $organization): Setting
    {
        $setting = Setting::where('organization_id', $organization->id)->first();

        $attributes = [
            'organization_id'         => $organization->id,
            'publishing_info'         => [
                'publisher_id'           => $organization->publisher_id,
                'api_token'              => '',
                'publisher_verification' => $organization->registry_approved,
                'token_verification'     => $organization->registry_approved,
            ],
            'default_values'          => [
                'default_currency' => 'USD',
                'default_language' => 'en',
            ],
            'activity_default_values' => [
                'hierarchy'           => '1',
                'humanitarian'        => '0',
                'budget_not_provided' => '',
            ],
        ];

        if (!$setting) {
            return Setting::create($attributes);
        }

        $setting->fill($attributes);

        if ($setting->isDirty()) {
            $setting->save();
        }

        return $setting;
    }

    public function syncUserFromClaims(string $sub, array $claims, int|null $orgId, string $role): User
    {
        // TODO: deprecate this , use 'sub' i.e uuid to query user.
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

    private function mapRegisterRoleToPublisher(string $registryRole = 'general_user'): string
    {
        return match ($registryRole) {
            'provider_admin' => 'iati_admin',
            'admin'          => 'admin',
            'editor'         => 'admin',
            'contributor'    => 'general_user',
            default          => 'general_user'
        };
    }
}
