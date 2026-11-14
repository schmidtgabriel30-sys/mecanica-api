<?php

namespace App\Repositories\Eloquent;

use App\Models\Agendamento;
use App\Models\User;
use App\Models\Veiculo;
use App\Repositories\Contracts\VeiculoRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class VeiculoRepository extends BaseRepository implements VeiculoRepositoryInterface
{
    public function __construct(Veiculo $model)
    {
        parent::__construct($model);
    }

    public function listarDoUsuario(User $user): Collection
    {
        return $user->veiculos()->latest()->get();
    }

    public function possuiAgendamentosEmAberto(Veiculo $veiculo): bool
    {
        return $veiculo->agendamentos()
            ->whereIn('status', Agendamento::STATUS_EM_ABERTO)
            ->exists();
    }
}
