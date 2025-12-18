<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Usuários comuns
        $commonUser1 = User::create([
            'name' => 'João Silva',
            'cpf' => '12345678901',
            'email' => 'joao@example.com',
            'password' => Hash::make('senha123'),
            'user_type' => 'common',
            'balance' => 1000.00,
        ]);

        $commonUser2 = User::create([
            'name' => 'Maria Santos',
            'cpf' => '98765432100',
            'email' => 'maria@example.com',
            'password' => Hash::make('senha123'),
            'user_type' => 'common',
            'balance' => 500.00,
        ]);

        // Lojistas (usam CNPJ - 14 dígitos)
        $merchant1 = User::create([
            'name' => 'Loja ABC',
            'cpf' => '00000000000191', // CNPJ válido (00.000.000/0001-91)
            'email' => 'loja@example.com',
            'password' => Hash::make('senha123'),
            'user_type' => 'merchant',
            'balance' => 0.00,
        ]);

        $merchant2 = User::create([
            'name' => 'Comércio XYZ',
            'cpf' => '11222333000181', // CNPJ válido (11.222.333/0001-81)
            'email' => 'comercio@example.com',
            'password' => Hash::make('senha123'),
            'user_type' => 'merchant',
            'balance' => 0.00,
        ]);

        $this->command->info('Database seeded successfully!');
        $this->command->info("Common User 1: ID {$commonUser1->id}, Balance: R$ 1000.00");
        $this->command->info("Common User 2: ID {$commonUser2->id}, Balance: R$ 500.00");
        $this->command->info("Merchant 1: ID {$merchant1->id}, Balance: R$ 0.00");
        $this->command->info("Merchant 2: ID {$merchant2->id}, Balance: R$ 0.00");
    }
}

