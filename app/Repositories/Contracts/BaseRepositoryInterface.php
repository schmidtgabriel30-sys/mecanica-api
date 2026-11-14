<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Model;

/**
 * Operações de persistência comuns a todos os repositórios.
 */
interface BaseRepositoryInterface
{
    public function create(array $data): Model;

    public function update(Model $model, array $data): Model;

    public function delete(Model $model): void;
}
