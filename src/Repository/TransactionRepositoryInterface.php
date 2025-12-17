<?php

declare(strict_types=1);

namespace PicPay\Repository;

use PicPay\Domain\Transaction;

interface TransactionRepositoryInterface
{
    public function save(Transaction $transaction): int;
    public function findById(int $id): ?Transaction;
}

