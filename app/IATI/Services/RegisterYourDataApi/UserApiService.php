<?php

namespace App\IATI\Services\RegisterYourDataApi;

use Illuminate\Http\Client\PendingRequest;

/**
 * Service class for interacting with the 'Users' endpoints
 * of the IATI Register Your Data API. Method names are aligned with the OpenAPI specification.
 */
class UserApiService
{
    /**
     * The constructor injects the low-level API client that handles raw requests.
     */
    public function __construct(private _RegisterYourDataApiClient $apiClient)
    {
    }

    /**
     * Apply for a user to be associated with an organisation.
     * Corresponds to: POST /users/{uid}/reporting-org.
     *
     * Note: Trailing slashes added to all endpoints to prevent 307 redirects.
     *
     * @param string $accessToken The user's API access token.
     * @param string $userId The UUID of the user.
     * @param string $organisationId The UUID of the organisation the user wants to join.
     * @return void
     * @throws RegisterYourDataApiException
     */
    public function addUserToOrganisation(string $accessToken, string $userId, string $organisationId): void
    {
        $payload = ['oid' => $organisationId];

        // This endpoint returns a BaseResponse with no 'data' key.
        $this->apiClient->executeRequest(
            fn (PendingRequest $request) => $request->post("users/{$userId}/reporting-org", $payload),
            $accessToken,
            false
        );
    }

    /**
     * Change the role for a user within a given organisation.
     * Corresponds to: PUT /users/{uid}/reporting-org/{oid}.
     *
     * @param string $accessToken The user's API access token.
     * @param string $userId The UUID of the user whose role is changing.
     * @param string $organisationId The UUID of the organisation.
     * @param string $role The new role for the user (e.g., 'admin', 'editor').
     * @return void
     * @throws RegisterYourDataApiException
     */
    public function updateUserRoleInOrganisation(string $accessToken, string $userId, string $organisationId, string $role): void
    {
        $payload = ['role' => $role];

        // This endpoint returns a BaseResponse with no 'data' key.
        $this->apiClient->executeRequest(
            fn (PendingRequest $request) => $request->put("users/{$userId}/reporting-org/{$organisationId}", $payload),
            $accessToken,
            false
        );
    }

    /**
     * Remove a user from an organisation.
     * Corresponds to: DELETE /users/{uid}/reporting-org/{oid}.
     *
     * @param string $accessToken The user's API access token.
     * @param string $userId The UUID of the user to remove.
     * @param string $organisationId The UUID of the organisation.
     * @return void
     * @throws RegisterYourDataApiException
     */
    public function removeUserFromOrganisation(string $accessToken, string $userId, string $organisationId): void
    {
        // This endpoint returns a BaseResponse with no 'data' key.
        $this->apiClient->executeRequest(
            fn (PendingRequest $request) => $request->delete("users/{$userId}/reporting-org/{$organisationId}"),
            $accessToken,
            false
        );
    }
}
