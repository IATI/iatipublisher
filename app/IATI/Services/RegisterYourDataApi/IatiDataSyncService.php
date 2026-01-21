<?php

declare(strict_types=1);

namespace App\IATI\Services\RegisterYourDataApi;

use App\Constants\Enums;
use App\IATI\Models\Organization\Organization;
use App\IATI\Models\Setting\Setting;
use App\IATI\Models\User\Role;
use App\IATI\Models\User\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class IatiDataSyncService
{
    public function __construct(private ReportingOrgApiService $reportingOrgApiService)
    {
    }

    public function syncOrganizationDownstream(string $uuid, array $data): Organization
    {
        if ($uuid) {
            $existingOrg = Organization::where('uuid', $uuid)->first();
        }

        if (!$existingOrg && !empty($data['organisation_identifier'])) {
            $existingOrg = Organization::where('identifier', $data['organisation_identifier'])->first();
        }

        if (!$existingOrg && !empty($data['short_name'])) {
            $existingOrg = Organization::where('publisher_id', $data['short_name'])->first();
        }

        if (!$existingOrg && !empty($data['human_readable_name'])) {
            $existingOrg = Organization::where('publisher_name', $data['human_readable_name'])->first();
        }

        $publisherTypeCode = $this->mapPublisherTypeCode(data_get($data, 'organisation_type'));
        $name = [['narrative' => data_get($data, 'human_readable_name'), 'language' => 'en']];

        $attributes = [
            'identifier'             => !empty($data['organisation_identifier']) ? $data['organisation_identifier'] : '-',
            'uuid'                   => $uuid,
            'publisher_id'           => data_get($data, 'short_name'),
            'publisher_name'         => data_get($data, 'human_readable_name'),
            'publisher_type'         => $publisherTypeCode,
            'address'                => data_get($data, 'address'),
            'telephone'              => data_get($data, 'phone'),
            'name'                   => $name,
            'reporting_org'          => [
                [
                    'ref'                => data_get($data, 'organisation_identifier'),
                    'type'               => $publisherTypeCode,
                    'secondary_reporter' => $this->mapSecondaryReporter(data_get($data, 'reporting_source_type')),
                    'narrative'          => $name,
                ],
            ],
            'country'                => $this->mapCountryCode(data_get($data, 'hq_country')),
            'iati_status'            => 'pending',
            'org_status'             => 'active',
            'migrated_from_aidsteam' => false,
            'registration_type'      => Enums::EXISTING_ORG,
            'registry_approved'      => data_get($data, 'registry_approved', false),
            'data_license'           => data_get($data, 'default_licence_id'),
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
            $existingOrg->saveQuietly();
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

    private function mapSecondaryReporter($reportingSourceType): string
    {
        return match ($reportingSourceType) {
            'primary_source'     => '0',
            'secondary_source'   => '1',
            default              => '',
        };
    }

    private function mapCountryCode($countryName): string|null
    {
        if (!$countryName) {
            return null;
        }

        $codeList = getCodeList('Country', 'Activity', false);

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
        ];

        if (!$setting) {
            $attributes[] = [
                'default_values'          => [
                    'default_currency' => '',
                    'default_language' => '',
                ],
                'activity_default_values' => [
                    'hierarchy'           => '',
                    'humanitarian'        => '',
                    'budget_not_provided' => '',
                ],
            ];

            return Setting::create($attributes);
        }

        $setting->fill($attributes);

        if ($setting->isDirty()) {
            $setting->save();
        }

        return $setting;
    }

    public function syncUserFromClaims(string $uuid, array $claims, int|null $orgId, string $publisherUserRole): User
    {
        $user = User::where('email', data_get($claims, 'email'))->first();

        if ($user) {
            $user->update([
                'email'              => Arr::get($claims, 'email'),
                'full_name'          => Arr::get($claims, 'family_name'),
                'username'           => Str::lower(Str::random(5)),
                'last_logged_in'     => now(),
                'language_preference'=> explode(' ', Arr::get($claims, 'iatiPreferredLanguage', 'en'))[0] ?? 'en',
                'organization_id'    => $orgId,
                'role_id'            => Role::where('role', $publisherUserRole)->value('id'),
            ]);
        } else {
            $user = User::create([
                'uuid'                     => $uuid,
                'email'                   => Arr::get($claims, 'email'),
                'username'                => Str::lower(Str::random(5)),
                'password'                => null,
                'full_name'               => Arr::get($claims, 'family_name'),
                'address'                 => Arr::get($claims, 'address'),
                'is_active'               => true,
                'email_verified_at'       => now(),
                'role_id'                 => Role::where('role', $publisherUserRole)->value('id'),
                'status'                  => true,
                'language_preference'     => explode(' ', Arr::get($claims, 'iatiPreferredLanguage', 'en'))[0] ?? 'en',
                'last_logged_in'          => now(),
                'sign_on_method'          => 'oidc',
                'organization_id'         => $orgId,
                'migrated_from_aidstream' => false,
            ]);
        }

        return $user;
    }

    /**
     * Maps registry role against system roles.
     */
    public function mapRegisterRoleToPublisher(string $registryRole = 'admin'): string
    {
        return match ($registryRole) {
            'provider_admin' => 'iati_admin',
            'iati_superadmin' => 'iati_admin',
//            'admin'          => 'admin',
//            'editor'         => 'admin',
//            'contributor'    => 'admin',
            default          => 'admin'
        };
    }

    /**
     * Orchestrates the building of the API PATCH payload and sends the update request.
     */
    public function syncOrganizationUpstream(Organization $organization, array $dirtyAttributes): bool
    {
        $apiPayload = $this->buildReportingOrgApiPayload($organization, $dirtyAttributes);

        if (empty($apiPayload)) {
            return true;
        }

        $accessToken = session('oidc_access_token');

        $this->reportingOrgApiService->updateReportingOrg($accessToken, $organization->uuid, $apiPayload);

        return true;
    }

    /**
     * Reverse maps the IATI organisation type code (e.g., '10') back to the API label (e.g., 'Regional NGO').
     */
    private function mapPublisherCodeToLabel(?string $publisherTypeCode): ?string
    {
        if (!$publisherTypeCode) {
            return null;
        }

        $codeList = getCodeList('OrganizationType', 'Organization', false);

        return Arr::get($codeList, $publisherTypeCode);
    }

    /**
     * Reverse maps the internal country code (e.g., 'CO') back to the API country name.
     */
    private function mapCountryCodeToLabel(?string $countryCode): ?string
    {
        if (!$countryCode) {
            return null;
        }

        $codeList = getCodeList('Country', 'Activity', false);

        return Arr::get($codeList, $countryCode);
    }

    /**
     * Reverse maps the internal boolean secondary_reporter flag to the API label.
     */
    private function mapSecondaryReporterToLabel(?string $isSecondaryReporter): string|null
    {
        if ($isSecondaryReporter === '0') {
            return 'primary_source';
        }

        if ($isSecondaryReporter === '1') {
            return 'secondary_source';
        }

        return null;
    }

    /**
     * Builds the external API PATCH payload by reverse-mapping internal model attributes.
     */
    private function buildReportingOrgApiPayload(Organization $organization, array $dirtyAttributes): array
    {
        $payload = [];

        if (Arr::has($dirtyAttributes, 'name')) {
            $payload['human_readable_name'] = $organization->name[0]['narrative'];
        }

        if (Arr::has($dirtyAttributes, 'address')) {
            $payload['address'] = $organization->address;
        }

        if (Arr::has($dirtyAttributes, 'telephone')) {
            $payload['phone'] = $organization->telephone;
        }

        if (Arr::has($dirtyAttributes, 'data_license')) {
            $payload['default_licence_id'] = $organization->data_license;
        }

        if (Arr::has($dirtyAttributes, 'country')) {
            $payload['hq_country'] = $this->mapCountryCodeToLabel($organization->country);
        }

        if (Arr::has($dirtyAttributes, 'reporting_org')) {
            $reportingOrgData = $organization->reporting_org;

            $isSecondary = Arr::get($reportingOrgData, '0.secondary_reporter');
            $organisationTypeCode = ($organization->publisher_type ?? $reportingOrgData[0]['type']) ?? null;
            $payload['reporting_source_type'] = $this->mapSecondaryReporterToLabel($isSecondary);
            $payload['organisation_type'] = $this->mapPublisherCodeToLabel($organisationTypeCode ? (string) ($organisationTypeCode) : null);
        }

        return $payload;
    }
}
