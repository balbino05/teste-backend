<?php

declare(strict_types=1);

namespace App\Services\External;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class AuthorizationService implements AuthorizationServiceInterface
{
    public function __construct(
        private Client $httpClient,
        private string $authorizeUrl
    ) {
    }

    public function authorize(): bool
    {
        try {
            $response = $this->httpClient->get($this->authorizeUrl, [
                'timeout' => 5,
                'http_errors' => false,
            ]);

            $statusCode = $response->getStatusCode();
            $body = json_decode($response->getBody()->getContents(), true);

            Log::info('Authorization service response', [
                'status_code' => $statusCode,
                'body' => $body,
            ]);

            if ($statusCode === 200 && isset($body['message']) && $body['message'] === 'Autorizado') {
                return true;
            }

            return false;
        } catch (GuzzleException $e) {
            Log::error('Authorization service error', [
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }
}

