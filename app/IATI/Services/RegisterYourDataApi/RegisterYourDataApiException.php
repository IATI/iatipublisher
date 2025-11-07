<?php

namespace App\IATI\Services\RegisterYourDataApi;

use Exception;
use Illuminate\Http\Client\RequestException;

class RegisterYourDataApiException extends Exception
{
    /**
     * Create a new exception instance from a Laravel HTTP Client exception.
     */
    public static function fromRequestException(RequestException $e): self
    {
        // Attempt to parse a more specific error message from the JSON response body.
        $errorMessage = $e->response->json('error.error_msg')
            ?? $e->response->json('error_description')
            ?? 'An unknown API error occurred.';

        $statusCode = $e->response->status();

        return new self("API Error (HTTP {$statusCode}): {$errorMessage}", $statusCode, $e);
    }
}
