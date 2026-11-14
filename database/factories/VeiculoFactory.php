<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class VeiculoFactory extends Factory
{
    public function definition(): array
    {
        $carros = [
            'Volkswagen' => ['Gol', 'Polo', 'T-Cross'],
            'Fiat' => ['Argo', 'Mobi', 'Toro'],
            'Chevrolet' => ['Onix', 'Tracker', 'S10'],
            'Toyota' => ['Corolla', 'Yaris', 'Hilux'],
            'Honda' => ['Civic', 'HR-V', 'Fit'],
        ];

        $marca = fake()->randomElement(array_keys($carros));

        return [
            'user_id' => User::factory(),
            'marca' => $marca,
            'modelo' => fake()->randomElement($carros[$marca]),
            'placa' => strtoupper(fake()->unique()->bothify('???#?##')),
            'ano' => fake()->numberBetween(2008, 2026),
        ];
    }
}
