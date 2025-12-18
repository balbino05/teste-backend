<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'cpf' => fake()->numerify('###########'),
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'user_type' => 'common',
            'balance' => 0.00,
        ];
    }

    public function merchant(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_type' => 'merchant',
            'cpf' => fake()->numerify('##############'), // CNPJ tem 14 dígitos
        ]);
    }
}

