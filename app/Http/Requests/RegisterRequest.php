<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Rules\CnpjValidation;
use App\Rules\CpfValidation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'cpf' => [
                'required',
                'string',
                Rule::when(
                    fn () => $this->input('user_type') === 'merchant',
                    ['size:14', 'regex:/^\d{14}$/', new CnpjValidation()],
                    ['size:11', 'regex:/^\d{11}$/', new CpfValidation()]
                ),
                'unique:users',
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'user_type' => ['required', 'string', Rule::in(['common', 'merchant'])],
        ];
    }

    public function messages(): array
    {
        $userType = $this->input('user_type');
        $isMerchant = $userType === 'merchant';
        $documentType = $isMerchant ? 'CNPJ' : 'CPF';
        $documentSize = $isMerchant ? '14' : '11';

        return [
            'name.required' => 'O nome é obrigatório',
            'email.required' => 'O email é obrigatório',
            'email.email' => 'O email deve ser válido',
            'email.unique' => 'Este email já está cadastrado',
            'cpf.required' => "O {$documentType} é obrigatório",
            'cpf.size' => "O {$documentType} deve ter {$documentSize} dígitos",
            'cpf.unique' => "Este {$documentType} já está cadastrado",
            'cpf.regex' => "O {$documentType} deve conter apenas números",
            'password.required' => 'A senha é obrigatória',
            'password.min' => 'A senha deve ter no mínimo 8 caracteres',
            'password.confirmed' => 'A confirmação da senha não confere',
            'user_type.required' => 'O tipo de usuário é obrigatório',
            'user_type.in' => 'O tipo de usuário deve ser "common" ou "merchant"',
        ];
    }
}

