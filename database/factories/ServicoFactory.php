<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ServicoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nome' => fake()->unique()->words(3, true),
            'descricao' => fake()->sentence(),
            'preco' => fake()->randomFloat(2, 50, 600),
            'duracao_minutos' => fake()->randomElement([30, 45, 60, 90, 120]),
            'ativo' => true,
        ];
    }
}
