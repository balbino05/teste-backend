<?php

declare(strict_types=1);

namespace PicPay\Service\External;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;

class AuthorizationService implements AuthorizationServiceInterface
{
    public function __construct(
        private Client $httpClient,
        private string $authorizeUrl,
        private LoggerInterface $logger
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

            $this->logger->info('Authorization service response', [
                'status_code' => $statusCode,
                'body' => $body,
            ]);

            // O serviço retorna 200 com { "message": "Autorizado" } quando autoriza
            if ($statusCode === 200 && isset($body['message']) && $body['message'] === 'Autorizado') {
                return true;
            }

            return false;
        } catch (GuzzleException $e) {
            $this->logger->error('Authorization service error', [
                'message' => $e->getMessage(),
            ]);

            // Em caso de erro, não autorizamos por segurança
            return false;
        }
    }
}

