<?php

namespace App\IATI\Services\RegisterYourDataApi;

use Illuminate\Http\Client\PendingRequest;

/**
 * Service class for interacting with the 'Reporting Orgs' endpoints
 * of the IATI Register Your Data API. Method names are aligned with the OpenAPI specification.
 */
class ReportingOrgApiService
{
    public function __construct(private _RegisterYourDataApiClient $apiClient)
    {
    }

    /**
     * Get a list of all reporting orgs the user has access to.
     * Corresponds to: GET /reporting-orgs.
     *
     * Note: Trailing slash is required to prevent 307 redirect that strips auth header.
     */
    public function getReportingOrgs(string $accessToken, array $queryParams = []): array
    {
        return $this->apiClient->executeRequest(
            fn (PendingRequest $request) => $request->get('reporting-orgs', $queryParams),
            $accessToken
        );
    }

    /**
     * Create a new reporting organisation.
     * Corresponds to: POST /reporting-orgs.
     */
    public function createReportingOrg(string $accessToken, array $data): array
    {
        return $this->apiClient->executeRequest(
            fn (PendingRequest $request) => $request->post('reporting-orgs', $data),
            $accessToken
        );
    }

    /**
     * Get detailed information about a specific organisation.
     * Corresponds to: GET /reporting-orgs/{oid}.
     */
    public function getReportingOrgDetails(string $accessToken, string $orgUUID, array $queryParams = []): array
    {
        return $this->apiClient->executeRequest(
            fn (PendingRequest $request) => $request->get("reporting-orgs/{$orgUUID}", $queryParams),
            $accessToken
        );
    }

    /**
     * Update metadata for an existing organisation.
     * Corresponds to: PATCH /reporting-orgs/{oid}.
     */
    public function updateReportingOrg(string $accessToken, string $orgUUID, array $data): array
    {
        return $this->apiClient->executeRequest(
            fn (PendingRequest $request) => $request->patch("reporting-orgs/{$orgUUID}", $data),
            $accessToken
        );
    }

    /**
     * Delete an organisation.
     * Corresponds to: DELETE /reporting-orgs/{oid}.
     */
    public function deleteReportingOrg(string $accessToken, string $orgUUID): void
    {
        $this->apiClient->executeRequest(
            fn (PendingRequest $request) => $request->delete("reporting-orgs/{$orgUUID}"),
            $accessToken,
            false
        );
    }

    /**
     * Get a list of users associated with a specific organisation.
     * Corresponds to: GET /reporting-orgs/{oid}/users.
     */
    public function getUsersForOrganisation(string $accessToken, string $orgUUID): array
    {
        return $this->apiClient->executeRequest(
            fn (PendingRequest $request) => $request->get("reporting-orgs/{$orgUUID}/users"),
            $accessToken
        );
    }

    /**
     * Get a list of datasets associated with a specific organisation.
     * Corresponds to: GET /reporting-orgs/{oid}/datasets.
     */
    public function getDatasetsForOrganisation(string $accessToken, string $orgUUID, array $queryParams = []): array
    {
        return $this->apiClient->executeRequest(
            fn (PendingRequest $request) => $request->get("reporting-orgs/{$orgUUID}/datasets", $queryParams),
            $accessToken
        );
    }
}
