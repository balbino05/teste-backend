<?php

declare(strict_types=1);

namespace PicPay\Repository;

use PicPay\Domain\User;

interface UserRepositoryInterface
{
    public function findById(int $id): ?User;
    public function findByCpf(string $cpf): ?User;
    public function findByEmail(string $email): ?User;
    public function save(User $user): int;
    public function updateBalance(int $userId, float $balance): void;
}

