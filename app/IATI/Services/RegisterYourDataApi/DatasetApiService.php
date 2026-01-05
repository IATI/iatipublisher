<?php

namespace App\IATI\Services\RegisterYourDataApi;

use Illuminate\Http\Client\PendingRequest;

/**
 * Service class for interacting with the 'Datasets' endpoints
 * of the IATI Register Your Data API. Method names are aligned with the OpenAPI specification.
 */
class DatasetApiService
{
    /**
     * The constructor injects the low-level API client that handles raw requests.
     */
    public function __construct(private _RegisterYourDataApiClient $apiClient)
    {
    }

    /**
     * Add a new dataset to an organisation.
     * Corresponds to: POST /datasets.
     *
     * Note: Trailing slashes added to all endpoints to prevent 307 redirects.
     *
     * @param string $accessToken The user's API access token.
     * @param array $data The metadata for the new dataset, including 'owner_organisation_id'.
     * @return array The newly created dataset's data.
     * @throws RegisterYourDataApiException
     */
    public function createDataset(string $accessToken, array $data): array
    {
        return $this->apiClient->executeRequest(
            fn (PendingRequest $request) => $request->post('datasets', $data),
            $accessToken
        );
    }

    /**
     * Get detailed information about a specific dataset.
     * Corresponds to: GET /datasets/{did}.
     *
     * @param string $accessToken The user's API access token.
     * @param string $datasetId The UUID of the dataset.
     * @return array The detailed dataset data.
     * @throws RegisterYourDataApiException
     */
    public function getDatasetDetails(string $accessToken, string $datasetId): array
    {
        return $this->apiClient->executeRequest(
            fn (PendingRequest $request) => $request->get("datasets/{$datasetId}"),
            $accessToken
        );
    }

    /**
     * Update metadata for an existing dataset.
     * Corresponds to: PATCH /datasets/{did}.
     *
     * @param string $accessToken The user's API access token.
     * @param string $datasetId The UUID of the dataset to update.
     * @param array $data The metadata fields to update.
     * @return array The updated dataset's data.
     * @throws RegisterYourDataApiException
     */
    public function updateDataset(string $accessToken, string $datasetId, array $data): array
    {
        return $this->apiClient->executeRequest(
            fn (PendingRequest $request) => $request->patch("datasets/{$datasetId}", $data),
            $accessToken
        );
    }

    /**
     * Delete a dataset.
     * Corresponds to: DELETE /datasets/{did}.
     *
     * @param string $accessToken The user's API access token.
     * @param string $datasetId The UUID of the dataset to delete.

     * @throws RegisterYourDataApiException
     */
    public function deleteDataset(string $accessToken, string $datasetId)
    {
        return $this->apiClient->executeRequest(
            fn (PendingRequest $request) => $request->delete("datasets/{$datasetId}"),
            $accessToken,
            false
        );
    }
}
