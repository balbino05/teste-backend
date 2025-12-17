<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\TransferService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TransferController extends Controller
{
    public function __construct(
        private TransferService $transferService
    ) {
    }

    public function transfer(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'value' => 'required|numeric|min:0.01',
                'payer' => 'required|integer|exists:users,id',
                'payee' => 'required|integer|exists:users,id',
            ]);

            $transaction = $this->transferService->transfer(
                (int) $request->input('payer'),
                (int) $request->input('payee'),
                (float) $request->input('value')
            );

            return response()->json([
                'transaction_id' => $transaction->id,
                'status' => $transaction->status,
                'message' => 'Transfer completed successfully',
            ], 201);
        } catch (\DomainException $e) {
            Log::warning('Transfer validation error', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => $e->getMessage(),
            ], 400);
        } catch (\Exception $e) {
            Log::error('Transfer error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'An error occurred while processing the transfer',
            ], 500);
        }
    }
}

