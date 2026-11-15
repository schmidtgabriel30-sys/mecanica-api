<?php

namespace App\Services;

use App\Exceptions\RegraDeNegocioException;
use App\Models\Agendamento;
use App\Models\User;
use App\Repositories\Contracts\AgendamentoRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

/**
 * Regras de negócio dos agendamentos.
 *
 * - Cliente vê e altera só os próprios agendamentos; admin vê todos.
 * - Só o admin escolhe o mecânico e muda o status (aceitar, iniciar o
 *   atendimento, concluir ou recusar).
 * - O cliente pode reagendar enquanto estiver pendente, ou cancelar.
 * - Status seguem um fluxo:
 *   pendente → confirmado → em atendimento → concluído
 *   (pendente e confirmado também podem ir para cancelado).
 * - Por dia e período: um veículo uma vez, um mecânico uma vez,
 *   e no máximo LIMITE_POR_PERIODO agendamentos.
 */
class AgendamentoService
{
    public const LIMITE_POR_PERIODO = 5;

    public const POR_PAGINA_PADRAO = 10;

    public const POR_PAGINA_MAXIMO = 50;

    public function __construct(private readonly AgendamentoRepositoryInterface $agendamentos) {}

    public function listar(User $user, ?string $status, ?int $porPagina = null): LengthAwarePaginator
    {
        return $this->agendamentos->paginar(
            $user->is_admin ? null : $user->id,
            $status,
            min($porPagina ?: self::POR_PAGINA_PADRAO, self::POR_PAGINA_MAXIMO),
        );
    }

    public function detalhar(Agendamento $agendamento): Agendamento
    {
        return $this->agendamentos->comRelacoes($agendamento);
    }

    public function criar(User $user, array $dados): Agendamento
    {
        if (! $user->is_admin) {
            unset($dados['mecanico_id']);
        }

        $this->validarAgenda(
            $dados['veiculo_id'],
            $dados['data_agendamento'],
            $dados['periodo'],
            $dados['mecanico_id'] ?? null,
        );

        /** @var Agendamento $agendamento */
        $agendamento = $this->agendamentos->create($dados + [
            'user_id' => $user->id,
            'status' => Agendamento::PENDENTE,
        ]);

        return $this->agendamentos->comRelacoes($agendamento);
    }

    public function atualizar(User $user, Agendamento $agendamento, array $dados): Agendamento
    {
        if (in_array($agendamento->status, Agendamento::STATUS_FINAIS, true)) {
            $this->falhar('status', "Agendamento {$agendamento->status} não pode ser alterado.");
        }

        if (! $user->is_admin) {
            unset($dados['mecanico_id']);
            $this->validarPermissoesDoCliente($agendamento, $dados);
        }

        $novoStatus = $dados['status'] ?? $agendamento->status;
        $this->validarTransicao($agendamento->status, $novoStatus);

        if ($novoStatus !== Agendamento::CANCELADO) {
            $this->validarAgenda(
                $agendamento->veiculo_id,
                $dados['data_agendamento'] ?? $agendamento->data_agendamento->toDateString(),
                $dados['periodo'] ?? $agendamento->periodo,
                array_key_exists('mecanico_id', $dados) ? $dados['mecanico_id'] : $agendamento->mecanico_id,
                $agendamento->id,
            );
        }

        $this->agendamentos->update($agendamento, $dados);

        return $this->agendamentos->comRelacoes($agendamento->refresh());
    }

    public function excluir(Agendamento $agendamento): void
    {
        if (in_array($agendamento->status, Agendamento::STATUS_NAO_EXCLUIVEIS, true)) {
            throw new RegraDeNegocioException('Agendamentos em atendimento ou concluídos não podem ser excluídos.');
        }

        $this->agendamentos->delete($agendamento);
    }

    /**
     * O cliente só pode cancelar, ou reagendar enquanto estiver pendente.
     */
    private function validarPermissoesDoCliente(Agendamento $agendamento, array $dados): void
    {
        $cancelando = ($dados['status'] ?? null) === Agendamento::CANCELADO;

        if (isset($dados['status']) && ! $cancelando) {
            $this->falhar('status', 'Você só pode cancelar o agendamento.');
        }

        if (! $cancelando && $agendamento->status !== Agendamento::PENDENTE) {
            $this->falhar('status', 'Só é possível reagendar enquanto o agendamento estiver pendente.');
        }
    }

    private function validarTransicao(string $atual, string $novo): void
    {
        if ($novo !== $atual && ! in_array($novo, Agendamento::TRANSICOES[$atual], true)) {
            $this->falhar('status', "Não é possível mudar de {$atual} para {$novo}.");
        }
    }

    /**
     * Conflitos de agenda no mesmo dia e período.
     */
    private function validarAgenda(int $veiculoId, string $data, string $periodo, ?int $mecanicoId, ?int $ignorarId = null): void
    {
        $dia = Carbon::parse($data)->toDateString();

        if ($this->agendamentos->veiculoOcupado($veiculoId, $dia, $periodo, $ignorarId)) {
            $this->falhar('veiculo_id', 'Este veículo já possui agendamento neste dia e período.');
        }

        if ($this->agendamentos->contarNoPeriodo($dia, $periodo, $ignorarId) >= self::LIMITE_POR_PERIODO) {
            $this->falhar('periodo', 'Este período está lotado para a data escolhida.');
        }

        if ($mecanicoId && $this->agendamentos->mecanicoOcupado($mecanicoId, $dia, $periodo, $ignorarId)) {
            $this->falhar('mecanico_id', 'O mecânico já possui agendamento neste dia e período.');
        }
    }

    /**
     * Devolve 422 no mesmo formato dos erros de validação do Laravel.
     */
    private function falhar(string $campo, string $mensagem): never
    {
        throw ValidationException::withMessages([$campo => $mensagem]);
    }
}
