<?php

namespace App\IATI\Services\RegisterYourDataApi;

use Illuminate\Http\Client\Factory as HttpClient;
use Illuminate\Http\Client\RequestException;

/**
 * A low-level client responsible for all direct HTTP communication
 * with the Register Your Data API. This class should not be used
 * directly by controllers; use the resource-specific services instead.
 */
class _RegisterYourDataApiClient
{
    private HttpClient $http;

    private string $baseUrl;

    public function __construct()
    {
        $this->http = new HttpClient();
        $this->baseUrl = config('services.registry_api.base_url');
    }

    /**
     * Executes an API request with consistent authentication and error handling.
     *
     * @param callable $requestCallback A closure that performs the HTTP call.
     * @param string   $accessToken     The bearer token for authentication.
     * @param bool     $expectDataKey   Whether to extract the 'data' key from the response.
     *
     * @return mixed The result of the API call.
     * @throws RegisterYourDataApiException
     */
    public function executeRequest(callable $requestCallback, string $accessToken, bool $expectDataKey = true): mixed
    {
        try {
            $request = $this->http->withToken($accessToken)->baseUrl($this->baseUrl)->acceptJson();

            $response = $requestCallback($request)->throw();

            return $expectDataKey ? $response->json('data') : $response->json();
        } catch (RequestException $e) {
            throw RegisterYourDataApiException::fromRequestException($e);
        }
    }
}
