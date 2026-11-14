<?php

namespace Database\Seeders;

use App\Models\Agendamento;
use App\Models\Mecanico;
use App\Models\Servico;
use App\Models\User;
use App\Models\Veiculo;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Administrador',
            'email' => 'admin@aurum.com',
            'password' => '12345678',
            'is_admin' => true,
        ]);

        $cliente = User::factory()->create([
            'name' => 'João Silva',
            'email' => 'cliente@aurum.com',
            'password' => '12345678',
        ]);

        $servicos = collect([
            ['nome' => 'Troca de óleo e filtro', 'descricao' => 'Lubrificação do motor', 'preco' => 149.90, 'duracao_minutos' => 45],
            ['nome' => 'Check-up completo de 40 itens', 'descricao' => 'Freios, suspensão, fluidos e correias', 'preco' => 99.90, 'duracao_minutos' => 60],
            ['nome' => 'Diagnóstico eletrônico', 'descricao' => 'Leitura dos sistemas eletrônicos', 'preco' => 89.90, 'duracao_minutos' => 40],
            ['nome' => 'Revisão preventiva completa', 'descricao' => 'Óleo, filtro, check-up e diagnóstico', 'preco' => 249.90, 'duracao_minutos' => 120],
        ])->map(fn (array $s) => Servico::create($s));

        $mecanicos = Mecanico::factory(3)->create();

        $veiculos = Veiculo::factory(2)->create(['user_id' => $cliente->id]);

        foreach ($veiculos as $i => $veiculo) {
            Agendamento::factory()->create([
                'user_id' => $cliente->id,
                'veiculo_id' => $veiculo->id,
                'servico_id' => $servicos->random()->id,
                'mecanico_id' => $mecanicos->random()->id,
                'data_agendamento' => now()->addDays($i + 2)->toDateString(),
                'periodo' => 'manha',
            ]);
        }
    }
}
