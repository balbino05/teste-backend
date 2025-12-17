<?php

declare(strict_types=1);

namespace App\Services;

use App\Jobs\SendNotificationJob;
use App\Models\Transaction;
use App\Models\User;
use App\Repositories\UserRepository;
use App\Services\External\AuthorizationServiceInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransferService
{
    public function __construct(
        private AuthorizationServiceInterface $authorizationService,
        private UserRepository $userRepository
    ) {
    }

    public function transfer(int $payerId, int $payeeId, float $value): Transaction
    {
        return DB::transaction(function () use ($payerId, $payeeId, $value) {
            // Lock pessimista para prevenir race conditions
            $payer = User::where('id', $payerId)->lockForUpdate()->first();
            $payee = User::where('id', $payeeId)->lockForUpdate()->first();

            if (!$payer || !$payee) {
                throw new \DomainException('User not found');
            }

            // Validações de negócio
            $this->validateTransfer($payer, $payee, $value);

            // Cria transação
            $transaction = Transaction::create([
                'payer_id' => $payerId,
                'payee_id' => $payeeId,
                'value' => $value,
                'status' => \App\Enums\TransactionStatus::PENDING,
            ]);

            // Autoriza a transação
            if (!$this->authorizationService->authorize()) {
                $transaction->markAsFailed('Transaction not authorized');
                $transaction->save();
                throw new \DomainException('Transaction not authorized');
            }

            // Executa a transferência usando métodos do modelo
            // Lock já garante que não há race condition, mas métodos validam também
            $payer->debit($value);
            $payee->credit($value);

            // Invalida cache dos usuários (passando os objetos para evitar query extra)
            $this->userRepository->invalidateCache($payer);
            $this->userRepository->invalidateCache($payee);

            // Marca transação como concluída
            $transaction->markAsCompleted('AUT-' . uniqid());
            $transaction->save();

            // Envia notificação via fila (assíncrono, não bloqueia)
            SendNotificationJob::dispatch(
                $payeeId,
                sprintf('Você recebeu R$ %.2f de %s', $value, $payer->name)
            )->onQueue('notifications');

            Log::info('Transfer completed successfully', [
                'transaction_id' => $transaction->id,
                'payer_id' => $payerId,
                'payee_id' => $payeeId,
                'value' => $value,
            ]);

            return $transaction;
        });
    }

    private function validateTransfer(User $payer, User $payee, float $value): void
    {
        // Lojistas não podem enviar dinheiro
        if ($payer->isMerchant()) {
            throw new \DomainException('Merchants cannot send money');
        }

        // Valida saldo
        if (!$payer->hasSufficientBalance($value)) {
            throw new \DomainException('Insufficient balance');
        }

        // Valida valor (já validado no FormRequest, mas mantido como segurança adicional)
        if ($value <= 0) {
            throw new \DomainException('Transfer value must be greater than zero');
        }
    }
}

