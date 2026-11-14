<?php

namespace App\Repositories\Eloquent;

use App\Models\Servico;
use App\Repositories\Contracts\ServicoRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ServicoRepository extends BaseRepository implements ServicoRepositoryInterface
{
    public function __construct(Servico $model)
    {
        parent::__construct($model);
    }

    public function listarAtivos(): Collection
    {
        return Servico::where('ativo', true)->orderBy('nome')->get();
    }

    public function possuiAgendamentos(Servico $servico): bool
    {
        return $servico->agendamentos()->exists();
    }
}
