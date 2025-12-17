<?php

declare(strict_types=1);

namespace PicPay\Service;

use Doctrine\DBAL\Connection;
use PicPay\Domain\Transaction;
use PicPay\Domain\User;
use PicPay\Repository\TransactionRepositoryInterface;
use PicPay\Repository\UserRepositoryInterface;
use PicPay\Service\External\AuthorizationServiceInterface;
use PicPay\Service\External\NotificationServiceInterface;
use Psr\Log\LoggerInterface;

class TransferService
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private TransactionRepositoryInterface $transactionRepository,
        private AuthorizationServiceInterface $authorizationService,
        private NotificationServiceInterface $notificationService,
        private Connection $connection,
        private LoggerInterface $logger
    ) {
    }

    public function transfer(int $payerId, int $payeeId, float $value): Transaction
    {
        // Inicia transação do banco de dados
        $this->connection->beginTransaction();

        try {
            // Busca usuários
            $payer = $this->userRepository->findById($payerId);
            $payee = $this->userRepository->findById($payeeId);

            if ($payer === null) {
                throw new \DomainException('Payer not found');
            }

            if ($payee === null) {
                throw new \DomainException('Payee not found');
            }

            // Validações de negócio
            $this->validateTransfer($payer, $payee, $value);

            // Cria transação
            $transaction = new Transaction(
                id: null,
                payerId: $payerId,
                payeeId: $payeeId,
                value: $value
            );

            // Autoriza a transação
            if (!$this->authorizationService->authorize()) {
                $transaction->markAsFailed('Transaction not authorized');
                $this->transactionRepository->save($transaction);
                $this->connection->commit();
                throw new \DomainException('Transaction not authorized');
            }

            // Executa a transferência
            $payer->debit($value);
            $payee->credit($value);

            // Atualiza saldos no banco
            $this->userRepository->updateBalance($payerId, $payer->getBalance());
            $this->userRepository->updateBalance($payeeId, $payee->getBalance());

            // Marca transação como concluída
            $transaction->markAsCompleted('AUT-' . uniqid());

            // Salva transação
            $this->transactionRepository->save($transaction);

            // Commit da transação do banco
            $this->connection->commit();

            // Envia notificação (não bloqueia se falhar)
            try {
                $this->notificationService->notify(
                    $payeeId,
                    sprintf('Você recebeu R$ %.2f de %s', $value, $payer->getName())
                );
            } catch (\Exception $e) {
                $this->logger->warning('Notification failed but transaction completed', [
                    'transaction_id' => $transaction->getId(),
                    'error' => $e->getMessage(),
                ]);
            }

            $this->logger->info('Transfer completed successfully', [
                'transaction_id' => $transaction->getId(),
                'payer_id' => $payerId,
                'payee_id' => $payeeId,
                'value' => $value,
            ]);

            return $transaction;
        } catch (\Exception $e) {
            // Rollback em caso de erro
            $this->connection->rollBack();

            $this->logger->error('Transfer failed', [
                'payer_id' => $payerId,
                'payee_id' => $payeeId,
                'value' => $value,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
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
        if ($payer->getId() === $payee->getId()) {
            throw new \DomainException('Cannot transfer to yourself');
        }
    }
}

