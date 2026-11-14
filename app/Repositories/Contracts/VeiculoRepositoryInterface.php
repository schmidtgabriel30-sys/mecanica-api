<?php

namespace App\Repositories\Contracts;

use App\Models\User;
use App\Models\Veiculo;
use Illuminate\Database\Eloquent\Collection;

interface VeiculoRepositoryInterface extends BaseRepositoryInterface
{
    public function listarDoUsuario(User $user): Collection;

    public function possuiAgendamentosEmAberto(Veiculo $veiculo): bool;
}
