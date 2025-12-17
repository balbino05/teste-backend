<?php

declare(strict_types=1);

namespace App\Services;

use App\Jobs\SendNotificationJob;
use App\Models\Transaction;
use App\Models\User;
use App\Repositories\UserRepository;
use App\Services\External\AuthorizationServiceInterface;
use Illuminate\Support\Facades\Cache;
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
            // Busca usuários (com cache)
            $payer = $this->userRepository->findById($payerId);
            $payee = $this->userRepository->findById($payeeId);

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
                'status' => 'pending',
            ]);

            // Autoriza a transação
            if (!$this->authorizationService->authorize()) {
                $transaction->markAsFailed('Transaction not authorized');
                $transaction->save();
                throw new \DomainException('Transaction not authorized');
            }

            // Executa a transferência
            $payer->debit($value);
            $payee->credit($value);

            // Salva os saldos atualizados
            $payer->save();
            $payee->save();

            // Invalida cache dos usuários
            $this->userRepository->invalidateCache($payerId);
            $this->userRepository->invalidateCache($payeeId);

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

        // Valida valor
        if ($value <= 0) {
            throw new \DomainException('Transfer value must be greater than zero');
        }

        // Não pode transferir para si mesmo
        if ($payer->id === $payee->id) {
            throw new \DomainException('Cannot transfer to yourself');
        }
    }
}

