<?php

declare(strict_types=1);

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class CnpjValidation implements Rule
{
    public function passes($attribute, $value): bool
    {
        $cnpj = preg_replace('/[^0-9]/', '', $value);

        if (strlen($cnpj) !== 14) {
            return false;
        }

        // Verifica se todos os dígitos são iguais
        if (preg_match('/(\d)\1{13}/', $cnpj)) {
            return false;
        }

        // Valida primeiro dígito verificador
        $length = 12;
        $digits = substr($cnpj, 0, $length);
        $sum = 0;
        $pos = $length - 7;

        for ($i = 0; $i < $length; $i++) {
            $sum += (int) $digits[$i] * $pos--;
            if ($pos < 2) {
                $pos = 9;
            }
        }

        $result = $sum % 11 < 2 ? 0 : 11 - ($sum % 11);

        if ($result !== (int) $cnpj[$length]) {
            return false;
        }

        // Valida segundo dígito verificador
        $length = 13;
        $digits = substr($cnpj, 0, $length);
        $sum = 0;
        $pos = $length - 7;

        for ($i = 0; $i < $length; $i++) {
            $sum += (int) $digits[$i] * $pos--;
            if ($pos < 2) {
                $pos = 9;
            }
        }

        $result = $sum % 11 < 2 ? 0 : 11 - ($sum % 11);

        return $result === (int) $cnpj[$length];
    }

    public function message(): string
    {
        return 'O CNPJ informado é inválido.';
    }
}

