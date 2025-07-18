<?php

namespace App\IATI\Services\RegisterYourDataApi;

use Illuminate\Http\Client\PendingRequest;

/**
 * Service class for interacting with the 'Miscellaneous' endpoints
 * of the IATI Register Your Data API. Method names are aligned with the OpenAPI specification.
 */
class MiscellaneousApiService
{
    /**
     * The constructor injects the low-level API client that handles raw requests.
     */
    public function __construct(private _RegisterYourDataApiClient $apiClient)
    {
    }

    /**
     * Get a list of all licences available in IATI.
     * Corresponds to: GET /licences.
     *
     * @param string $accessToken The user's API access token.
     * @return array A list of licence data.
     * @throws RegisterYourDataApiException
     */
    public function getLicences(string $accessToken): array
    {
        // According to the YAML, this endpoint's success response is a direct array,
        // not nested under a 'data' key.
        return $this->apiClient->executeRequest(
            fn (PendingRequest $request) => $request->get('licences'),
            $accessToken,
            false
        );
    }

    /**
     * Check if the provided credentials grant access to the API.
     * Corresponds to: GET /access-check.
     *
     * @param string $accessToken The user's API access token.
     * @return bool Returns true if the access check is successful.
     * @throws RegisterYourDataApiException If access is denied (e.g., 401, 403).
     */
    public function checkAccess(string $accessToken): bool
    {
        // This endpoint's purpose is to succeed or fail. A 200 OK means access is granted.
        // Any other status will throw an exception.
        $this->apiClient->executeRequest(
            fn (PendingRequest $request) => $request->get('access-check'),
            $accessToken,
            false
        );

        return true;
    }
}
