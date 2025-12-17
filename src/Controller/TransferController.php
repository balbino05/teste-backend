<?php

declare(strict_types=1);

namespace PicPay\Controller;

use PicPay\Service\TransferService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;

class TransferController
{
    public function __construct(
        private TransferService $transferService,
        private LoggerInterface $logger
    ) {
    }

    public function transfer(Request $request, Response $response): Response
    {
        try {
            $data = json_decode($request->getBody()->getContents(), true);

            // Validação dos dados de entrada
            if (!isset($data['value']) || !isset($data['payer']) || !isset($data['payee'])) {
                return $this->errorResponse($response, 'Missing required fields: value, payer, payee', 400);
            }

            $value = (float) $data['value'];
            $payerId = (int) $data['payer'];
            $payeeId = (int) $data['payee'];

            if ($value <= 0) {
                return $this->errorResponse($response, 'Value must be greater than zero', 400);
            }

            // Executa a transferência
            $transaction = $this->transferService->transfer($payerId, $payeeId, $value);

            return $this->successResponse($response, [
                'transaction_id' => $transaction->getId(),
                'status' => $transaction->getStatus()->value,
                'message' => 'Transfer completed successfully',
            ], 201);
        } catch (\DomainException $e) {
            $this->logger->warning('Transfer validation error', [
                'error' => $e->getMessage(),
            ]);

            return $this->errorResponse($response, $e->getMessage(), 400);
        } catch (\Exception $e) {
            $this->logger->error('Transfer error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->errorResponse($response, 'An error occurred while processing the transfer', 500);
        }
    }

    private function successResponse(Response $response, array $data, int $statusCode = 200): Response
    {
        $response->getBody()->write(json_encode($data));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($statusCode);
    }

    private function errorResponse(Response $response, string $message, int $statusCode): Response
    {
        $response->getBody()->write(json_encode([
            'error' => $message,
        ]));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($statusCode);
    }
}

