<?php

declare(strict_types=1);

namespace App\Services;

use App\Jobs\SendNotificationJob;
use App\Models\Transaction;
use App\Models\User;
use App\Repositories\UserRepository;
use App\Services\External\AuthorizationServiceInterface;
use App\Services\TransferValidator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransferService
{
    public function __construct(
        private AuthorizationServiceInterface $authorizationService,
        private UserRepository $userRepository,
        private TransferValidator $validator
    ) {
    }

    public function transfer(int $payerId, int $payeeId, float $value): Transaction
    {
        return DB::transaction(function () use ($payerId, $payeeId, $value) {
            $users = $this->lockUsersForTransfer($payerId, $payeeId);
            $payer = $users['payer'];
            $payee = $users['payee'];

            $this->validator->validate($payer, $payee, $value);

            $transaction = $this->createTransaction($payerId, $payeeId, $value);
            $this->authorizeTransaction($transaction);
            $this->executeTransfer($payer, $payee, $value, $transaction);
            $this->finalizeTransfer($transaction, $payer, $payee, $payeeId, $value);

            return $transaction;
        });
    }

    private function lockUsersForTransfer(int $payerId, int $payeeId): array
    {
        $ids = [$payerId, $payeeId];
        sort($ids);

        $firstUser = $this->userRepository->findByIdWithLock($ids[0]);
        $secondUser = $this->userRepository->findByIdWithLock($ids[1]);

        if (!$firstUser || !$secondUser) {
            throw new \DomainException('User not found');
        }

        return [
            'payer' => $firstUser->id === $payerId ? $firstUser : $secondUser,
            'payee' => $firstUser->id === $payeeId ? $firstUser : $secondUser,
        ];
    }

    private function createTransaction(int $payerId, int $payeeId, float $value): Transaction
    {
        return Transaction::create([
            'payer_id' => $payerId,
            'payee_id' => $payeeId,
            'value' => $value,
            'status' => \App\Enums\TransactionStatus::PENDING,
        ]);
    }

    private function authorizeTransaction(Transaction $transaction): void
    {
        try {
            if (!$this->authorizationService->authorize()) {
                $transaction->markAsFailed('Transaction not authorized');
                $transaction->save();
                throw new \DomainException('Transaction not authorized');
            }
        } catch (\DomainException $e) {
            throw $e;
        } catch (\Exception $e) {
            if (!$transaction->isFailed()) {
                $transaction->markAsFailed('Authorization service error: ' . $e->getMessage());
                $transaction->save();
            }
            throw $e;
        }
    }

    private function executeTransfer(User $payer, User $payee, float $value, Transaction $transaction): void
    {
        try {
            $payer->debit($value);
            $payee->credit($value);
        } catch (\Exception $e) {
            if (!$transaction->isFailed()) {
                $transaction->markAsFailed('Transfer execution error: ' . $e->getMessage());
                $transaction->save();
            }
            throw $e;
        }
    }

    private function finalizeTransfer(Transaction $transaction, User $payer, User $payee, int $payeeId, float $value): void
    {
        $this->userRepository->invalidateCache($payer);
        $this->userRepository->invalidateCache($payee);

        $transaction->markAsCompleted('AUT-' . uniqid());
        $transaction->save();

        SendNotificationJob::dispatch(
            $payeeId,
            sprintf('Você recebeu R$ %.2f de %s', $value, $payer->name)
        )->onQueue('notifications');

        Log::info('Transfer completed successfully', [
            'transaction_id' => $transaction->id,
            'payer_id' => $payer->id,
            'payee_id' => $payeeId,
            'value' => $value,
        ]);
    }

}

