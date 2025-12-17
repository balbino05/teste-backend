<?php

declare(strict_types=1);

namespace PicPay\Domain;

class User
{
    public function __construct(
        private ?int $id,
        private string $name,
        private string $cpf,
        private string $email,
        private string $password,
        private UserType $userType,
        private float $balance = 0.0
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCpf(): string
    {
        return $this->cpf;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getUserType(): UserType
    {
        return $this->userType;
    }

    public function getBalance(): float
    {
        return $this->balance;
    }

    public function isMerchant(): bool
    {
        return $this->userType === UserType::MERCHANT;
    }

    public function isCommon(): bool
    {
        return $this->userType === UserType::COMMON;
    }

    public function hasSufficientBalance(float $amount): bool
    {
        return $this->balance >= $amount;
    }

    public function debit(float $amount): void
    {
        if (!$this->hasSufficientBalance($amount)) {
            throw new \DomainException('Insufficient balance');
        }

        $this->balance -= $amount;
    }

    public function credit(float $amount): void
    {
        if ($amount <= 0) {
            throw new \DomainException('Amount must be greater than zero');
        }

        $this->balance += $amount;
    }
}

