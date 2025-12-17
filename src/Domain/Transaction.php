<?php

declare(strict_types=1);

namespace PicPay\Domain;

class Transaction
{
    public function __construct(
        private ?int $id,
        private int $payerId,
        private int $payeeId,
        private float $value,
        private TransactionStatus $status = TransactionStatus::PENDING,
        private ?string $authorizationCode = null,
        private ?string $errorMessage = null
    ) {
        if ($value <= 0) {
            throw new \DomainException('Transaction value must be greater than zero');
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPayerId(): int
    {
        return $this->payerId;
    }

    public function getPayeeId(): int
    {
        return $this->payeeId;
    }

    public function getValue(): float
    {
        return $this->value;
    }

    public function getStatus(): TransactionStatus
    {
        return $this->status;
    }

    public function getAuthorizationCode(): ?string
    {
        return $this->authorizationCode;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function markAsCompleted(string $authorizationCode): void
    {
        $this->status = TransactionStatus::COMPLETED;
        $this->authorizationCode = $authorizationCode;
    }

    public function markAsFailed(string $errorMessage): void
    {
        $this->status = TransactionStatus::FAILED;
        $this->errorMessage = $errorMessage;
    }

    public function markAsReversed(): void
    {
        $this->status = TransactionStatus::REVERSED;
    }

    public function isCompleted(): bool
    {
        return $this->status === TransactionStatus::COMPLETED;
    }

    public function isFailed(): bool
    {
        return $this->status === TransactionStatus::FAILED;
    }

    public function isReversed(): bool
    {
        return $this->status === TransactionStatus::REVERSED;
    }
}

