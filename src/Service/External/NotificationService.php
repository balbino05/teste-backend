<?php

declare(strict_types=1);

namespace PicPay\Service\External;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;

class NotificationService implements NotificationServiceInterface
{
    public function __construct(
        private Client $httpClient,
        private string $notifyUrl,
        private LoggerInterface $logger
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

            $this->logger->info('Notification service response', [
                'user_id' => $userId,
                'status_code' => $statusCode,
                'body' => $body,
            ]);

            // O serviço pode estar indisponível, mas não falhamos a transação por isso
            return $statusCode === 200;
        } catch (GuzzleException $e) {
            $this->logger->error('Notification service error', [
                'user_id' => $userId,
                'message' => $e->getMessage(),
            ]);

            // Não falhamos a transação se a notificação falhar
            return false;
        }
    }
}

