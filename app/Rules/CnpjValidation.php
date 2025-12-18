<?php

declare(strict_types=1);

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class CnpjValidation implements Rule
{
    private const CNPJ_LENGTH = 14;
    private const FIRST_DIGIT_POSITION = 12;
    private const SECOND_DIGIT_POSITION = 13;
    private const MIN_POSITION = 2;
    private const MAX_POSITION = 9;

    public function passes($attribute, $value): bool
    {
        $cnpj = preg_replace('/[^0-9]/', '', (string) $value);

        if (strlen($cnpj) !== self::CNPJ_LENGTH) {
            return false;
        }

        if ($this->hasAllSameDigits($cnpj)) {
            return false;
        }

        if (!$this->validateFirstDigit($cnpj)) {
            return false;
        }

        return $this->validateSecondDigit($cnpj);
    }

    public function message(): string
    {
        return 'O CNPJ informado é inválido.';
    }

    private function hasAllSameDigits(string $cnpj): bool
    {
        return (bool) preg_match('/(\d)\1{13}/', $cnpj);
    }

    private function validateFirstDigit(string $cnpj): bool
    {
        $expectedDigit = $this->calculateVerifierDigit($cnpj, self::FIRST_DIGIT_POSITION);
        $actualDigit = (int) $cnpj[self::FIRST_DIGIT_POSITION];

        return $expectedDigit === $actualDigit;
    }

    private function validateSecondDigit(string $cnpj): bool
    {
        $expectedDigit = $this->calculateVerifierDigit($cnpj, self::SECOND_DIGIT_POSITION);
        $actualDigit = (int) $cnpj[self::SECOND_DIGIT_POSITION];

        return $expectedDigit === $actualDigit;
    }

    private function calculateVerifierDigit(string $cnpj, int $length): int
    {
        $digits = substr($cnpj, 0, $length);
        $sum = 0;
        $pos = $length - 7;

        for ($i = 0; $i < $length; $i++) {
            $sum += (int) $digits[$i] * $pos--;
            if ($pos < self::MIN_POSITION) {
                $pos = self::MAX_POSITION;
            }
        }

        $remainder = $sum % 11;

        return $remainder < self::MIN_POSITION ? 0 : 11 - $remainder;
    }
}

