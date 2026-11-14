<?php

namespace App\Services;

use App\Models\Mecanico;
use App\Repositories\Contracts\MecanicoRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Equipe de mecânicos (gerenciada pelo admin).
 */
class MecanicoService
{
    public function __construct(private readonly MecanicoRepositoryInterface $mecanicos) {}

    public function listarAtivos(): Collection
    {
        return $this->mecanicos->listarAtivos();
    }

    public function criar(array $dados): Mecanico
    {
        /** @var Mecanico */
        return $this->mecanicos->create($dados);
    }

    public function atualizar(Mecanico $mecanico, array $dados): Mecanico
    {
        /** @var Mecanico */
        return $this->mecanicos->update($mecanico, $dados);
    }

    public function excluir(Mecanico $mecanico): void
    {
        $this->mecanicos->delete($mecanico);
    }
}
