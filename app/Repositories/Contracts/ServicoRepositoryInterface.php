<?php

namespace App\Repositories\Contracts;

use App\Models\Servico;
use Illuminate\Database\Eloquent\Collection;

interface ServicoRepositoryInterface extends BaseRepositoryInterface
{
    public function listarAtivos(): Collection;

    public function possuiAgendamentos(Servico $servico): bool;
}
