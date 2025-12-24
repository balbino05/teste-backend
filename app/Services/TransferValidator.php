<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;

class TransferValidator
{
    public function validate(User $payer, User $payee, float $value): void
    {
        if ($payer->isMerchant()) {
            throw new \DomainException('Merchants cannot send money');
        }

        if (!$payer->hasSufficientBalance($value)) {
            throw new \DomainException('Insufficient balance');
        }

        if ($value <= 0) {
            throw new \DomainException('Transfer value must be greater than zero');
        }
    }
}

