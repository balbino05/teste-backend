<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'cpf',
        'email',
        'password',
        'user_type',
        'balance',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
    ];

    public function isMerchant(): bool
    {
        return $this->user_type === 'merchant';
    }

    public function isCommon(): bool
    {
        return $this->user_type === 'common';
    }

    public function hasSufficientBalance(float $amount): bool
    {
        return (float) $this->balance >= $amount;
    }

    public function debit(float $amount): void
    {
        if (!$this->hasSufficientBalance($amount)) {
            throw new \DomainException('Insufficient balance');
        }

        $this->balance = (float) $this->balance - $amount;
    }

    public function credit(float $amount): void
    {
        if ($amount <= 0) {
            throw new \DomainException('Amount must be greater than zero');
        }

        $this->balance = (float) $this->balance + $amount;
    }
}

