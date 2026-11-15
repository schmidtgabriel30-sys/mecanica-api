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
        // Administrador fixo: usuário AdminA, senha AdminA.
        $this->call(AdminSeeder::class);

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

        foreach ([
            ['Carlos Pereira', 'Motor e injeção eletrônica', '11988887777'],
            ['Ana Souza', 'Freios e suspensão', '11977776666'],
            ['Roberto Lima', 'Elétrica automotiva', '11966665555'],
        ] as [$nome, $especialidade, $telefone]) {
            Mecanico::create(['nome' => $nome, 'especialidade' => $especialidade, 'telefone' => $telefone]);
        }

        $maria = User::factory()->create([
            'name' => 'Maria Oliveira',
            'email' => 'maria@aurum.com',
            'password' => '12345678',
        ]);

        // Solicitações pendentes, aguardando o admin aceitar.
        $pedidos = [
            [$cliente, 'Volkswagen', 'Gol', 'Revisão preventiva completa', 2, 'manha', 'Luz do motor acendeu no painel.'],
            [$cliente, 'Honda', 'Civic', 'Troca de óleo e filtro', 3, 'tarde', null],
            [$maria, 'Fiat', 'Argo', 'Diagnóstico eletrônico', 2, 'tarde', 'Barulho ao frear.'],
        ];

        foreach ($pedidos as [$dono, $marca, $modelo, $servico, $dias, $periodo, $obs]) {
            $veiculo = Veiculo::factory()->create([
                'user_id' => $dono->id,
                'marca' => $marca,
                'modelo' => $modelo,
            ]);

            Agendamento::factory()->create([
                'user_id' => $dono->id,
                'veiculo_id' => $veiculo->id,
                'servico_id' => $servicos->firstWhere('nome', $servico)->id,
                'mecanico_id' => null,
                'data_agendamento' => now()->addDays($dias)->toDateString(),
                'periodo' => $periodo,
                'status' => 'pendente',
                'observacoes' => $obs,
            ]);
        }
    }
}
