<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\TransferRequest;
use App\Services\TransferService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class TransferController extends Controller
{
    public function __construct(
        private TransferService $transferService
    ) {
    }

    public function transfer(TransferRequest $request): JsonResponse
    {
        try {
            $transaction = $this->transferService->transfer(
                (int) $request->input('payer'),
                (int) $request->input('payee'),
                (float) $request->input('value')
            );

            return response()->json([
                'transaction_id' => $transaction->id,
                'status' => $transaction->status->value,
                'message' => 'Transfer completed successfully',
            ], 201);
        } catch (\DomainException $e) {
            try {
                Log::warning('Transfer validation error', [
                    'error' => $e->getMessage(),
                ]);
            } catch (\Exception $logException) {
                // Ignora erros de log
            }

            return response()->json([
                'error' => $e->getMessage(),
            ], 400);
        } catch (\Exception $e) {
            try {
                Log::error('Transfer error', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            } catch (\Exception $logException) {
                // Ignora erros de log
            }

            return response()->json([
                'error' => 'An error occurred while processing the transfer',
            ], 500);
        }
    }
}

