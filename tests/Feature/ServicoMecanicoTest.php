<?php

namespace Tests\Feature;

use App\Models\Agendamento;
use App\Models\Mecanico;
use App\Models\Servico;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServicoMecanicoTest extends TestCase
{
    use RefreshDatabase;

    public function test_lista_de_servicos_e_publica_e_so_mostra_ativos(): void
    {
        Servico::factory()->create(['ativo' => true]);
        Servico::factory()->create(['ativo' => false]);

        $this->getJson('/api/servicos')->assertOk()->assertJsonCount(1);
    }

    public function test_cliente_nao_gerencia_servicos_nem_mecanicos(): void
    {
        $this->actingAs(User::factory()->create());

        $this->postJson('/api/servicos', ['nome' => 'X', 'preco' => 10, 'duracao_minutos' => 30])->assertForbidden();
        $this->postJson('/api/mecanicos', ['nome' => 'X'])->assertForbidden();
    }

    public function test_admin_faz_crud_de_servico(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        $id = $this->postJson('/api/servicos', ['nome' => 'Alinhamento', 'preco' => 120, 'duracao_minutos' => 60])
            ->assertCreated()->json('id');
        $this->putJson("/api/servicos/{$id}", ['preco' => 130])->assertOk()->assertJsonPath('preco', '130.00');
        $this->deleteJson("/api/servicos/{$id}")->assertNoContent();
    }

    public function test_nao_exclui_servico_em_uso(): void
    {
        $agendamento = Agendamento::factory()->create();
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        $this->deleteJson("/api/servicos/{$agendamento->servico_id}")->assertStatus(409);
    }

    public function test_admin_faz_crud_de_mecanico(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        $id = $this->postJson('/api/mecanicos', ['nome' => 'Carlos', 'especialidade' => 'Freios'])
            ->assertCreated()->json('id');
        $this->getJson('/api/mecanicos')->assertOk()->assertJsonCount(1);
        $this->putJson("/api/mecanicos/{$id}", ['telefone' => '11999990000'])->assertOk();
        $this->deleteJson("/api/mecanicos/{$id}")->assertNoContent();
        $this->assertDatabaseMissing('mecanicos', ['id' => $id]);
    }

    public function test_lista_de_mecanicos_mostra_so_ativos(): void
    {
        Mecanico::factory()->create(['ativo' => true]);
        Mecanico::factory()->create(['ativo' => false]);

        $this->actingAs(User::factory()->create())->getJson('/api/mecanicos')->assertOk()->assertJsonCount(1);
    }
}
