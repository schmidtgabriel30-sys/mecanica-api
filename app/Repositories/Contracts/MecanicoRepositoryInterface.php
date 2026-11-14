<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface MecanicoRepositoryInterface extends BaseRepositoryInterface
{
    public function listarAtivos(): Collection;
}
