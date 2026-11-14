<?php

namespace Tests\Feature;

use App\Models\Agendamento;
use App\Models\User;
use App\Models\Veiculo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VeiculoTest extends TestCase
{
    use RefreshDatabase;

    private function dados(array $extra = []): array
    {
        return array_merge(['marca' => 'Volkswagen', 'modelo' => 'Gol', 'placa' => 'abc-1d23', 'ano' => 2015], $extra);
    }

    public function test_crud_completo_do_proprio_veiculo(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $id = $this->postJson('/api/veiculos', $this->dados())
            ->assertCreated()
            ->assertJsonPath('placa', 'ABC1D23')
            ->json('id');

        $this->getJson('/api/veiculos')->assertOk()->assertJsonCount(1);
        $this->getJson("/api/veiculos/{$id}")->assertOk()->assertJsonPath('modelo', 'Gol');
        $this->putJson("/api/veiculos/{$id}", ['modelo' => 'Gol 1.6'])->assertOk()->assertJsonPath('modelo', 'Gol 1.6');
        $this->deleteJson("/api/veiculos/{$id}")->assertNoContent();
        $this->assertDatabaseMissing('veiculos', ['id' => $id]);
    }

    public function test_lista_apenas_veiculos_do_usuario(): void
    {
        $user = User::factory()->create();
        Veiculo::factory()->create(['user_id' => $user->id]);
        Veiculo::factory()->create();

        $this->actingAs($user)->getJson('/api/veiculos')->assertOk()->assertJsonCount(1);
    }

    public function test_nao_acessa_veiculo_de_outro_usuario(): void
    {
        $outro = Veiculo::factory()->create();
        $this->actingAs(User::factory()->create());

        $this->getJson("/api/veiculos/{$outro->id}")->assertForbidden();
        $this->putJson("/api/veiculos/{$outro->id}", ['modelo' => 'X'])->assertForbidden();
        $this->deleteJson("/api/veiculos/{$outro->id}")->assertForbidden();
    }

    public function test_placa_repetida_retorna_422(): void
    {
        Veiculo::factory()->create(['placa' => 'ABC1D23']);

        $this->actingAs(User::factory()->create())
            ->postJson('/api/veiculos', $this->dados())
            ->assertUnprocessable()
            ->assertJsonValidationErrors('placa');
    }

    public function test_nao_exclui_veiculo_com_agendamento_em_aberto(): void
    {
        $user = User::factory()->create();
        $veiculo = Veiculo::factory()->create(['user_id' => $user->id]);
        Agendamento::factory()->create(['user_id' => $user->id, 'veiculo_id' => $veiculo->id, 'status' => 'pendente']);

        $this->actingAs($user)->deleteJson("/api/veiculos/{$veiculo->id}")->assertStatus(409);
    }
}
