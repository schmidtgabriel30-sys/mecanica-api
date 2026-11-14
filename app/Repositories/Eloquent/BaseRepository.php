<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

/**
 * Implementação reutilizável do CRUD básico com Eloquent.
 *
 * Cada repositório concreto informa o seu Model no construtor e herda
 * create/update/delete, escrevendo apenas as consultas específicas.
 */
abstract class BaseRepository implements BaseRepositoryInterface
{
    public function __construct(protected Model $model) {}

    public function create(array $data): Model
    {
        return $this->model->newQuery()->create($data);
    }

    public function update(Model $model, array $data): Model
    {
        $model->update($data);

        return $model;
    }

    public function delete(Model $model): void
    {
        $model->delete();
    }
}
