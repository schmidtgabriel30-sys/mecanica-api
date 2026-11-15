<?php

namespace App\Repositories\Eloquent;

use App\Models\Agendamento;
use App\Repositories\Contracts\AgendamentoRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class AgendamentoRepository extends BaseRepository implements AgendamentoRepositoryInterface
{
    /**
     * Relações carregadas junto com o agendamento nas respostas.
     * "cliente" traz só id, nome e e-mail de quem solicitou.
     */
    private const RELACOES = ['veiculo', 'servico', 'mecanico', 'cliente:id,name,email'];

    public function __construct(Agendamento $model)
    {
        parent::__construct($model);
    }

    public function paginar(?int $userId, ?string $status, int $porPagina): LengthAwarePaginator
    {
        return Agendamento::with(self::RELACOES)
            ->when($userId, fn (Builder $q) => $q->where('user_id', $userId))
            ->when($status, fn (Builder $q) => $q->where('status', $status))
            ->orderBy('data_agendamento')
            ->paginate($porPagina);
    }

    public function comRelacoes(Agendamento $agendamento): Agendamento
    {
        return $agendamento->load(self::RELACOES);
    }

    public function veiculoOcupado(int $veiculoId, string $dia, string $periodo, ?int $ignorarId = null): bool
    {
        return $this->ativosNoPeriodo($dia, $periodo, $ignorarId)
            ->where('veiculo_id', $veiculoId)
            ->exists();
    }

    public function mecanicoOcupado(int $mecanicoId, string $dia, string $periodo, ?int $ignorarId = null): bool
    {
        return $this->ativosNoPeriodo($dia, $periodo, $ignorarId)
            ->where('mecanico_id', $mecanicoId)
            ->exists();
    }

    public function contarNoPeriodo(string $dia, string $periodo, ?int $ignorarId = null): int
    {
        return $this->ativosNoPeriodo($dia, $periodo, $ignorarId)->count();
    }

    /**
     * Consulta base reaproveitada pelas três verificações acima:
     * agendamentos não cancelados no mesmo dia e período.
     *
     * Usa whereDate para comparar só a data, independente de o banco
     * guardar "2026-11-20" (MySQL) ou "2026-11-20 00:00:00" (SQLite).
     */
    private function ativosNoPeriodo(string $dia, string $periodo, ?int $ignorarId): Builder
    {
        return Agendamento::query()
            ->whereDate('data_agendamento', $dia)
            ->where('periodo', $periodo)
            ->where('status', '!=', Agendamento::CANCELADO)
            ->when($ignorarId, fn (Builder $q) => $q->whereKeyNot($ignorarId));
    }
}
