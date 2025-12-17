<?php

declare(strict_types=1);

namespace PicPay\Repository;

use Doctrine\DBAL\Connection;
use PicPay\Domain\Transaction;
use PicPay\Domain\TransactionStatus;

class TransactionRepository implements TransactionRepositoryInterface
{
    public function __construct(
        private Connection $connection
    ) {
    }

    public function save(Transaction $transaction): int
    {
        $data = [
            'payer_id' => $transaction->getPayerId(),
            'payee_id' => $transaction->getPayeeId(),
            'value' => $transaction->getValue(),
            'status' => $transaction->getStatus()->value,
            'authorization_code' => $transaction->getAuthorizationCode(),
            'error_message' => $transaction->getErrorMessage(),
        ];

        if ($transaction->getId() === null) {
            $this->connection->insert('transactions', $data);
            $transactionId = (int) $this->connection->lastInsertId();
            // Atualiza a transação com o ID gerado (via reflection para manter imutabilidade)
            $reflection = new \ReflectionClass($transaction);
            $idProperty = $reflection->getProperty('id');
            $idProperty->setAccessible(true);
            $idProperty->setValue($transaction, $transactionId);
            return $transactionId;
        } else {
            $this->connection->update('transactions', $data, ['id' => $transaction->getId()]);
            return $transaction->getId();
        }
    }

    public function findById(int $id): ?Transaction
    {
        $row = $this->connection->fetchAssociative(
            'SELECT * FROM transactions WHERE id = ?',
            [$id]
        );

        if (!$row) {
            return null;
        }

        return $this->hydrate($row);
    }

    private function hydrate(array $row): Transaction
    {
        $transaction = new Transaction(
            id: (int) $row['id'],
            payerId: (int) $row['payer_id'],
            payeeId: (int) $row['payee_id'],
            value: (float) $row['value'],
            status: TransactionStatus::from($row['status']),
            authorizationCode: $row['authorization_code'],
            errorMessage: $row['error_message']
        );

        return $transaction;
    }
}

