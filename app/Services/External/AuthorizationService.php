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
        // Em ambiente local/teste, permite mockar a autorização via variável de ambiente
        if (app()->environment(['local', 'testing']) && env('MOCK_AUTHORIZATION', false)) {
            Log::info('Authorization service mocked (always authorized)', [
                'environment' => app()->environment(),
            ]);
            return true;
        }

        try {
            // Em ambiente de desenvolvimento/teste, aceita certificados SSL inválidos
            $httpClient = Http::timeout(5);

            if (app()->environment(['local', 'testing'])) {
                $httpClient = $httpClient->withoutVerifying();
            }

            $response = $httpClient->get($this->authorizeUrl);

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
                'url' => $this->authorizeUrl,
            ]);

            return false;
        }
    }
}

