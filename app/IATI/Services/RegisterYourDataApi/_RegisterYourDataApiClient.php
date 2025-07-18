<?php

namespace App\IATI\Services\RegisterYourDataApi;

use GuzzleHttp\RequestOptions;
use Illuminate\Http\Client\Factory as HttpClient;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Request; // Added for type hinting in $requestCallback
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Log;

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
     * @param callable(PendingRequest): \Illuminate\Http\Client\Response $requestCallback A closure that performs the HTTP call (e.g., fn($request) => $request->post(...)).
     * @param string   $accessToken     The bearer token for authentication.
     * @param bool     $expectDataKey   Whether to extract the 'data' key from the response.
     *
     * @return mixed The result of the API call.
     * @throws RegisterYourDataApiException
     */
    public function executeRequest(callable $requestCallback, string $accessToken, bool $expectDataKey = true): mixed
    {
        try {
            $cleanAccessToken = trim($accessToken);

            $pendingRequest = $this->http
                ->baseUrl($this->baseUrl)
                ->acceptJson()
                ->beforeSending(function (Request $request, array $options) use ($accessToken) {
                    $currentHeaders = $request->headers();

                    $curl = $this->getRequestAsCurl($request, $options);

                    Log::debug('RYDA API Request (cURL)', [
                        'curl_command' => $curl,
                        'url' => $request->url(),
                        'headers' => $currentHeaders,
                        'access_token' => $accessToken,
                    ]);
                })
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $cleanAccessToken,
                ]);

            $response = $requestCallback($pendingRequest)->throw();

            return $expectDataKey ? $response->json('data') : $response->json();
        } catch (RequestException $e) {
            throw RegisterYourDataApiException::fromRequestException($e);
        }
    }

    /**
     * Constructs a rough cURL command string from the Laravel HTTP Request and Guzzle options.
     * (Includes token redaction for security.).
     *
     * @param Request $request The Request object intercepted by beforeSending.
     * @param array $options Guzzle options, containing headers and body data.
     * @return string
     */
    protected function getRequestAsCurl(Request $request, array $options): string
    {
        // Start the command with method and URL
        $curl = "curl -X {$request->method()} '{$request->url()}'";

        // --- 1. Add Headers (FIX: Use the Request object's headers) ---
        // The Request object holds the merged, final headers to be sent.
        $headers = $request->headers();

        foreach ($headers as $name => $values) {
            $lowerName = strtolower($name);

            // Guzzle provides headers as an array of values (even if only one exists)
            foreach ((array) $values as $value) {
                // >>> SECURITY: Redact the Authorization header <<<
                if ($lowerName === 'authorization') {
                    // Check if the value looks like a Bearer token before redacting
                    if (str_starts_with(trim($value), 'Bearer')) {
                        $value = 'Bearer [REDACTED_TOKEN]';
                    } else {
                        $value = '[REDACTED_HEADER_VALUE]';
                    }
                }

                // Ensure Accept header is also correctly included if present (often set by ->acceptJson())

                // Escape value for shell use
                $escapedValue = str_replace("'", "'\\''", $value);
                $curl .= " -H '{$name}: {$escapedValue}'";
            }
        }

        // --- 2. Add Body/Payload ---
        // (Body logic remains correct as it relies on Guzzle options keys)

        // Check for JSON payload (common for acceptJson())
        if (isset($options[RequestOptions::JSON])) {
            $body = json_encode($options[RequestOptions::JSON]);
            $contentType = 'application/json';
        }
        // Check for Form Payload (form_params)
        elseif (isset($options[RequestOptions::FORM_PARAMS])) {
            $body = http_build_query($options[RequestOptions::FORM_PARAMS]);
            $contentType = 'application/x-www-form-urlencoded';
        }
        // Check for Raw Body (Guzzle 'body' option)
        elseif (isset($options[RequestOptions::BODY])) {
            $body = $options[RequestOptions::BODY];
            $contentType = ''; // Content type should already be in headers
        }

        if (isset($body)) {
            // Ensure data is properly escaped for shell usage
            $escapedBody = str_replace("'", "'\\''", $body);

            // Add the data flag
            $curl .= " -d '{$escapedBody}'";

            // If a content type wasn't set through the Laravel client, ensure it's here
            // Note: We need to check $headers (from $request) now, not $options
            if ($contentType && !array_key_exists('Content-Type', $headers)) {
                $curl .= " -H 'Content-Type: {$contentType}'";
            }
        }

        // Add verbose flag for better debugging in the terminal
        $curl .= ' -v';

        return $curl;
    }
}
