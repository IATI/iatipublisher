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
        if (!$e->response) {
            return new self(
                '##### API Error: No response received from Register Your Data API. #####',
                0,
                $e
            );
        }

        $statusCode = $e->response->status();
        $body = $e->response->json();

        $errorMessage =
            data_get($body, 'error.error_msg')
            ?? data_get($body, 'error_description')
            ?? data_get($body, 'error')
            ?? data_get($body, 'message')
            ?? $e->response->body()
            ?? 'An unknown API error occurred.';

        return new self(
            "##### API Error (HTTP {$statusCode}): {$errorMessage} #####",
            0,
            $e
        );
    }
}
