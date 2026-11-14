<?php

namespace Database\Factories;

use App\Models\Agendamento;
use App\Models\Mecanico;
use App\Models\Servico;
use App\Models\User;
use App\Models\Veiculo;
use Illuminate\Database\Eloquent\Factories\Factory;

class AgendamentoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'veiculo_id' => Veiculo::factory(),
            'servico_id' => Servico::factory(),
            'mecanico_id' => Mecanico::factory(),
            'data_agendamento' => fake()->dateTimeBetween('+1 day', '+30 days')->format('Y-m-d'),
            'periodo' => fake()->randomElement(Agendamento::PERIODOS),
            'status' => 'pendente',
            'observacoes' => fake()->optional()->sentence(),
        ];
    }
}
