<?php

namespace App\Repositories\Eloquent;

use App\Models\Mecanico;
use App\Repositories\Contracts\MecanicoRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class MecanicoRepository extends BaseRepository implements MecanicoRepositoryInterface
{
    public function __construct(Mecanico $model)
    {
        parent::__construct($model);
    }

    public function listarAtivos(): Collection
    {
        return Mecanico::where('ativo', true)->orderBy('nome')->get();
    }
}
