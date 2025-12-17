<?php

declare(strict_types=1);

namespace App\Services\External;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AuthorizationService implements AuthorizationServiceInterface
{
    public function __construct(
        private string $authorizeUrl
    ) {
    }

    public function authorize(): bool
    {
        try {
            $response = Http::timeout(5)->get($this->authorizeUrl);

            $statusCode = $response->status();
            $body = $response->json();

            Log::info('Authorization service response', [
                'status_code' => $statusCode,
                'body' => $body,
            ]);

            if ($statusCode === 200 && isset($body['message']) && $body['message'] === 'Autorizado') {
                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Authorization service error', [
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }
}

