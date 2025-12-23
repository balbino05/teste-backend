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
            // Lock pessimista com ordem determinística para prevenir deadlocks
            // Sempre lockar pelo menor ID primeiro para evitar deadlocks em transferências simultâneas
            $ids = [$payerId, $payeeId];
            sort($ids);

            $firstUser = User::where('id', $ids[0])->lockForUpdate()->first();
            $secondUser = User::where('id', $ids[1])->lockForUpdate()->first();

            if (!$firstUser || !$secondUser) {
                throw new \DomainException('User not found');
            }

            // Identifica payer e payee após lock e validação
            $payer = $firstUser->id === $payerId ? $firstUser : $secondUser;
            $payee = $firstUser->id === $payeeId ? $firstUser : $secondUser;

            // Validações de negócio
            $this->validateTransfer($payer, $payee, $value);

            // Cria transação
            $transaction = Transaction::create([
                'payer_id' => $payerId,
                'payee_id' => $payeeId,
                'value' => $value,
                'status' => \App\Enums\TransactionStatus::PENDING,
            ]);

            try {
                // Autoriza a transação
                if (!$this->authorizationService->authorize()) {
                    $transaction->markAsFailed('Transaction not authorized');
                    $transaction->save();
                    throw new \DomainException('Transaction not authorized');
                }
            } catch (\Exception $e) {
                // Garante que transação seja marcada como failed em caso de erro na autorização
                if (!$transaction->isFailed()) {
                    $transaction->markAsFailed('Authorization service error: ' . $e->getMessage());
                    $transaction->save();
                }
                throw $e;
            }

            // Executa a transferência usando métodos do modelo
            // Lock já garante que não há race condition, mas métodos validam também
            try {
                $payer->debit($value);
                $payee->credit($value);
            } catch (\Exception $e) {
                // Em caso de erro no débito/crédito, marca transação como failed antes de relançar
                if (!$transaction->isFailed()) {
                    $transaction->markAsFailed('Transfer execution error: ' . $e->getMessage());
                    $transaction->save();
                }
                throw $e;
            }

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

