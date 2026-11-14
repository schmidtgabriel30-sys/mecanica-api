<?php

namespace Tests\Unit;

use App\Exceptions\RegraDeNegocioException;
use App\Models\Agendamento;
use App\Models\User;
use App\Repositories\Contracts\AgendamentoRepositoryInterface;
use App\Services\AgendamentoService;
use Illuminate\Validation\ValidationException;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

/**
 * Testa as regras do AgendamentoService sem banco de dados: o repositório
 * é substituído por um "dublê" (mock). Isso só é possível porque o Service
 * depende da interface do repositório, e não do Eloquent diretamente.
 */
class AgendamentoServiceTest extends TestCase
{
    private AgendamentoRepositoryInterface&MockInterface $repo;

    private AgendamentoService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repo = Mockery::mock(AgendamentoRepositoryInterface::class);
        $this->service = new AgendamentoService($this->repo);
    }

    private function dados(): array
    {
        return ['veiculo_id' => 1, 'servico_id' => 1, 'data_agendamento' => '2030-01-10', 'periodo' => 'manha'];
    }

    private function cliente(): User
    {
        return new User(['name' => 'Cliente']);
    }

    public function test_recusa_periodo_lotado(): void
    {
        $this->repo->shouldReceive('veiculoOcupado')->andReturnFalse();
        $this->repo->shouldReceive('contarNoPeriodo')->andReturn(AgendamentoService::LIMITE_POR_PERIODO);
        $this->repo->shouldNotReceive('create');

        $this->expectException(ValidationException::class);
        $this->service->criar($this->cliente(), $this->dados());
    }

    public function test_recusa_veiculo_ja_agendado_no_periodo(): void
    {
        $this->repo->shouldReceive('veiculoOcupado')->with(1, '2030-01-10', 'manha', null)->andReturnTrue();
        $this->repo->shouldNotReceive('create');

        $this->expectException(ValidationException::class);
        $this->service->criar($this->cliente(), $this->dados());
    }

    public function test_cliente_nao_define_mecanico_e_agendamento_nasce_pendente(): void
    {
        $this->repo->shouldReceive('veiculoOcupado')->andReturnFalse();
        $this->repo->shouldReceive('contarNoPeriodo')->andReturn(0);
        $this->repo->shouldNotReceive('mecanicoOcupado');
        $this->repo->shouldReceive('create')
            ->once()
            ->withArgs(fn (array $d) => ! isset($d['mecanico_id']) && $d['status'] === Agendamento::PENDENTE)
            ->andReturn(new Agendamento);
        $this->repo->shouldReceive('comRelacoes')->andReturnArg(0);

        $this->service->criar($this->cliente(), $this->dados() + ['mecanico_id' => 9]);
    }

    public function test_nao_exclui_agendamento_concluido(): void
    {
        $agendamento = new Agendamento(['status' => Agendamento::CONCLUIDO]);
        $this->repo->shouldNotReceive('delete');

        $this->expectException(RegraDeNegocioException::class);
        $this->service->excluir($agendamento);
    }

    public function test_nao_altera_agendamento_cancelado(): void
    {
        $agendamento = new Agendamento(['status' => Agendamento::CANCELADO]);
        $this->repo->shouldNotReceive('update');

        $this->expectException(ValidationException::class);
        $this->service->atualizar($this->cliente(), $agendamento, ['periodo' => 'tarde']);
    }
}
