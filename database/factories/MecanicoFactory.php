<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class MecanicoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nome' => fake()->name(),
            'especialidade' => fake()->randomElement(['Motor', 'Freios e suspensão', 'Elétrica', 'Injeção eletrônica']),
            'telefone' => fake()->numerify('(##) 9####-####'),
            'ativo' => true,
        ];
    }
}
