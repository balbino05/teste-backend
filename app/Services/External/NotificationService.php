<?php

declare(strict_types=1);

namespace App\Services\External;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotificationService implements NotificationServiceInterface
{
    public function __construct(
        private string $notifyUrl
    ) {
    }

    public function notify(int $userId, string $message): bool
    {
        try {
            // Em ambiente de desenvolvimento/teste, aceita certificados SSL inválidos
            $httpClient = Http::timeout(5);

            if (app()->environment(['local', 'testing'])) {
                $httpClient = $httpClient->withoutVerifying();
            }

            $response = $httpClient->post($this->notifyUrl, [
                'user_id' => $userId,
                'message' => $message,
            ]);

            $statusCode = $response->status();
            $body = $response->json();

            Log::info('Notification service response', [
                'user_id' => $userId,
                'status_code' => $statusCode,
                'body' => $body,
            ]);

            return $statusCode === 200;
        } catch (\Exception $e) {
            Log::error('Notification service error', [
                'user_id' => $userId,
                'message' => $e->getMessage(),
                'url' => $this->notifyUrl,
            ]);

            return false;
        }
    }
}

