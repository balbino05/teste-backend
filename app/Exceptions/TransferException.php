<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

class TransferException extends Exception
{
    public static function insufficientBalance(): self
    {
        return new self('Insufficient balance', 400);
    }

    public static function merchantCannotSend(): self
    {
        return new self('Merchants cannot send money', 400);
    }

    public static function notAuthorized(): self
    {
        return new self('Transaction not authorized', 400);
    }

    public static function userNotFound(): self
    {
        return new self('User not found', 404);
    }

    public static function invalidAmount(): self
    {
        return new self('Transfer value must be greater than zero', 400);
    }
}

