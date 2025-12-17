<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'value' => 'required|numeric|min:0.01|max:999999.99',
            'payer' => 'required|integer|exists:users,id|different:payee',
            'payee' => 'required|integer|exists:users,id',
        ];
    }

    public function messages(): array
    {
        return [
            'value.required' => 'O valor é obrigatório',
            'value.numeric' => 'O valor deve ser um número',
            'value.min' => 'O valor mínimo é R$ 0,01',
            'value.max' => 'O valor máximo é R$ 999.999,99',
            'payer.required' => 'O pagador é obrigatório',
            'payer.exists' => 'Pagador não encontrado',
            'payer.different' => 'Não é possível transferir para si mesmo',
            'payee.required' => 'O recebedor é obrigatório',
            'payee.exists' => 'Recebedor não encontrado',
        ];
    }
}

