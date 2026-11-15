<?php

namespace Tests\Feature;

use App\Models\Agendamento;
use App\Models\Mecanico;
use App\Models\Servico;
use App\Models\User;
use App\Models\Veiculo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgendamentoTest extends TestCase
{
    use RefreshDatabase;

    private User $cliente;

    private Veiculo $veiculo;

    private Servico $servico;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cliente = User::factory()->create();
        $this->veiculo = Veiculo::factory()->create(['user_id' => $this->cliente->id]);
        $this->servico = Servico::factory()->create(['ativo' => true]);
    }

    private function dados(array $extra = []): array
    {
        return array_merge([
            'veiculo_id' => $this->veiculo->id,
            'servico_id' => $this->servico->id,
            'data_agendamento' => now()->addDays(3)->toDateString(),
            'periodo' => 'manha',
            'observacoes' => 'Barulho no freio',
        ], $extra);
    }

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    public function test_cliente_faz_crud_do_proprio_agendamento(): void
    {
        $this->actingAs($this->cliente);

        $id = $this->postJson('/api/agendamentos', $this->dados())
            ->assertCreated()
            ->assertJsonPath('status', 'pendente')
            ->assertJsonPath('data_agendamento', now()->addDays(3)->toDateString())
            ->assertJsonPath('veiculo.id', $this->veiculo->id)
            ->json('id');

        $this->getJson('/api/agendamentos')->assertOk()->assertJsonPath('total', 1);
        $this->getJson("/api/agendamentos/{$id}")->assertOk()->assertJsonPath('servico.id', $this->servico->id);
        $this->putJson("/api/agendamentos/{$id}", ['periodo' => 'tarde'])->assertOk()->assertJsonPath('periodo', 'tarde');
        $this->deleteJson("/api/agendamentos/{$id}")->assertNoContent();
    }

    public function test_cliente_so_ve_os_proprios_e_admin_ve_todos(): void
    {
        Agendamento::factory()->create(['user_id' => $this->cliente->id, 'veiculo_id' => $this->veiculo->id]);
        Agendamento::factory()->create();

        $this->actingAs($this->cliente)->getJson('/api/agendamentos')->assertJsonPath('total', 1);
        $this->actingAs($this->admin())->getJson('/api/agendamentos')->assertJsonPath('total', 2);
    }

    public function test_filtra_por_status(): void
    {
        Agendamento::factory()->create(['user_id' => $this->cliente->id, 'veiculo_id' => $this->veiculo->id, 'status' => 'pendente']);
        Agendamento::factory()->create(['user_id' => $this->cliente->id, 'veiculo_id' => $this->veiculo->id, 'status' => 'cancelado']);

        $this->actingAs($this->cliente)->getJson('/api/agendamentos?status=cancelado')->assertJsonPath('total', 1);
    }

    public function test_nao_acessa_agendamento_de_outro_usuario(): void
    {
        $outro = Agendamento::factory()->create();
        $this->actingAs($this->cliente);

        $this->getJson("/api/agendamentos/{$outro->id}")->assertForbidden();
        $this->deleteJson("/api/agendamentos/{$outro->id}")->assertForbidden();
    }

    public function test_nao_usa_veiculo_de_outro_usuario(): void
    {
        $alheio = Veiculo::factory()->create();

        $this->actingAs($this->cliente)
            ->postJson('/api/agendamentos', $this->dados(['veiculo_id' => $alheio->id]))
            ->assertUnprocessable()->assertJsonValidationErrors('veiculo_id');
    }

    public function test_data_no_passado_e_periodo_invalido(): void
    {
        $this->actingAs($this->cliente)
            ->postJson('/api/agendamentos', $this->dados(['data_agendamento' => now()->subDay()->toDateString(), 'periodo' => 'madrugada']))
            ->assertUnprocessable()->assertJsonValidationErrors(['data_agendamento', 'periodo']);
    }

    public function test_mesmo_veiculo_no_mesmo_dia_e_periodo(): void
    {
        $this->actingAs($this->cliente);
        $this->postJson('/api/agendamentos', $this->dados())->assertCreated();

        $this->postJson('/api/agendamentos', $this->dados())
            ->assertUnprocessable()->assertJsonValidationErrors('veiculo_id');
    }

    public function test_periodo_lotado(): void
    {
        $dia = now()->addDays(3)->toDateString();
        Agendamento::factory(5)->create(['data_agendamento' => $dia, 'periodo' => 'manha']);

        $this->actingAs($this->cliente)->postJson('/api/agendamentos', $this->dados())
            ->assertUnprocessable()->assertJsonValidationErrors('periodo');
    }

    public function test_mecanico_ocupado_no_periodo(): void
    {
        $mecanico = Mecanico::factory()->create(['ativo' => true]);
        $dia = now()->addDays(3)->toDateString();
        Agendamento::factory()->create(['data_agendamento' => $dia, 'periodo' => 'manha', 'mecanico_id' => $mecanico->id]);
        $novo = Agendamento::factory()->create(['data_agendamento' => $dia, 'periodo' => 'manha', 'mecanico_id' => null]);

        $this->actingAs($this->admin())
            ->putJson("/api/agendamentos/{$novo->id}", ['mecanico_id' => $mecanico->id])
            ->assertUnprocessable()->assertJsonValidationErrors('mecanico_id');
    }

    public function test_cliente_nao_escolhe_mecanico_nem_confirma(): void
    {
        $mecanico = Mecanico::factory()->create(['ativo' => true]);
        $this->actingAs($this->cliente);

        $id = $this->postJson('/api/agendamentos', $this->dados(['mecanico_id' => $mecanico->id]))
            ->assertCreated()->assertJsonPath('mecanico_id', null)->json('id');

        $this->putJson("/api/agendamentos/{$id}", ['status' => 'confirmado'])
            ->assertUnprocessable()->assertJsonValidationErrors('status');
    }

    public function test_cliente_cancela_o_proprio_agendamento(): void
    {
        $this->actingAs($this->cliente);
        $id = $this->postJson('/api/agendamentos', $this->dados())->json('id');

        $this->putJson("/api/agendamentos/{$id}", ['status' => 'cancelado'])->assertOk()->assertJsonPath('status', 'cancelado');
    }

    public function test_cliente_nao_reagenda_depois_de_confirmado(): void
    {
        $agendamento = Agendamento::factory()->create([
            'user_id' => $this->cliente->id, 'veiculo_id' => $this->veiculo->id, 'status' => 'confirmado',
        ]);

        $this->actingAs($this->cliente)
            ->putJson("/api/agendamentos/{$agendamento->id}", ['periodo' => 'noite'])
            ->assertUnprocessable()->assertJsonValidationErrors('status');
    }

    public function test_fluxo_do_admin_aceita_atende_e_conclui(): void
    {
        $mecanico = Mecanico::factory()->create(['ativo' => true]);
        $agendamento = Agendamento::factory()->create(['status' => 'pendente', 'mecanico_id' => null]);
        $this->actingAs($this->admin());

        $this->putJson("/api/agendamentos/{$agendamento->id}", ['status' => 'confirmado', 'mecanico_id' => $mecanico->id])
            ->assertOk()->assertJsonPath('status', 'confirmado')->assertJsonPath('mecanico.id', $mecanico->id);

        // não pula etapas: confirmado → concluído direto é recusado
        $this->putJson("/api/agendamentos/{$agendamento->id}", ['status' => 'concluido'])
            ->assertUnprocessable()->assertJsonValidationErrors('status');

        $this->putJson("/api/agendamentos/{$agendamento->id}", ['status' => 'em_atendimento'])
            ->assertOk()->assertJsonPath('status', 'em_atendimento');

        // em atendimento não pode ser cancelado nem excluído
        $this->putJson("/api/agendamentos/{$agendamento->id}", ['status' => 'cancelado'])->assertUnprocessable();
        $this->deleteJson("/api/agendamentos/{$agendamento->id}")->assertStatus(409);

        $this->putJson("/api/agendamentos/{$agendamento->id}", ['status' => 'concluido'])
            ->assertOk()->assertJsonPath('status', 'concluido');

        // concluído não pode mais ser alterado nem excluído
        $this->putJson("/api/agendamentos/{$agendamento->id}", ['periodo' => 'tarde'])->assertUnprocessable();
        $this->deleteJson("/api/agendamentos/{$agendamento->id}")->assertStatus(409);
    }

    public function test_admin_ve_quem_solicitou(): void
    {
        Agendamento::factory()->create(['user_id' => $this->cliente->id, 'veiculo_id' => $this->veiculo->id]);

        $this->actingAs($this->admin())->getJson('/api/agendamentos')
            ->assertJsonPath('data.0.cliente.name', $this->cliente->name)
            ->assertJsonPath('data.0.cliente.email', $this->cliente->email)
            ->assertJsonMissingPath('data.0.cliente.password');
    }

    public function test_admin_pode_pedir_mais_itens_por_pagina(): void
    {
        Agendamento::factory(12)->create();

        $this->actingAs($this->admin())->getJson('/api/agendamentos?per_page=30')
            ->assertJsonPath('per_page', 30)->assertJsonCount(12, 'data');
        $this->actingAs($this->admin())->getJson('/api/agendamentos?per_page=500')
            ->assertJsonPath('per_page', 50);
    }

    public function test_transicao_invalida_de_status(): void
    {
        $agendamento = Agendamento::factory()->create(['status' => 'pendente']);

        $this->actingAs($this->admin())
            ->putJson("/api/agendamentos/{$agendamento->id}", ['status' => 'concluido'])
            ->assertUnprocessable()->assertJsonValidationErrors('status');
    }

    public function test_veiculo_e_servico_nao_mudam_na_edicao(): void
    {
        $agendamento = Agendamento::factory()->create(['user_id' => $this->cliente->id, 'veiculo_id' => $this->veiculo->id]);

        $this->actingAs($this->cliente)
            ->putJson("/api/agendamentos/{$agendamento->id}", ['servico_id' => $this->servico->id])
            ->assertUnprocessable()->assertJsonValidationErrors('servico_id');
    }
}
