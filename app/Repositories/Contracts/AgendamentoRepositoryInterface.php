<?php

namespace App\Repositories\Contracts;

use App\Models\Agendamento;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface AgendamentoRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Lista paginada. Com $userId, traz só os agendamentos daquele usuário.
     */
    public function paginar(?int $userId, ?string $status, int $porPagina): LengthAwarePaginator;

    public function comRelacoes(Agendamento $agendamento): Agendamento;

    public function veiculoOcupado(int $veiculoId, string $dia, string $periodo, ?int $ignorarId = null): bool;

    public function mecanicoOcupado(int $mecanicoId, string $dia, string $periodo, ?int $ignorarId = null): bool;

    public function contarNoPeriodo(string $dia, string $periodo, ?int $ignorarId = null): int;
}
