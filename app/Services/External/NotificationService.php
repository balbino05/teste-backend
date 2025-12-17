<?php

declare(strict_types=1);

namespace App\Services\External;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class NotificationService implements NotificationServiceInterface
{
    public function __construct(
        private Client $httpClient,
        private string $notifyUrl
    ) {
    }

    public function notify(int $userId, string $message): bool
    {
        try {
            $response = $this->httpClient->post($this->notifyUrl, [
                'json' => [
                    'user_id' => $userId,
                    'message' => $message,
                ],
                'timeout' => 5,
                'http_errors' => false,
            ]);

            $statusCode = $response->getStatusCode();
            $body = json_decode($response->getBody()->getContents(), true);

            Log::info('Notification service response', [
                'user_id' => $userId,
                'status_code' => $statusCode,
                'body' => $body,
            ]);

            return $statusCode === 200;
        } catch (GuzzleException $e) {
            Log::error('Notification service error', [
                'user_id' => $userId,
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }
}

