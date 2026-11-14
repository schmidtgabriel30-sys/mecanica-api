<?php

namespace App\Services;

use App\Exceptions\RegraDeNegocioException;
use App\Models\User;
use App\Models\Veiculo;
use App\Repositories\Contracts\VeiculoRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Regras dos veículos: cada cliente gerencia apenas os seus.
 */
class VeiculoService
{
    public function __construct(private readonly VeiculoRepositoryInterface $veiculos) {}

    public function listar(User $user): Collection
    {
        return $this->veiculos->listarDoUsuario($user);
    }

    public function criar(User $user, array $dados): Veiculo
    {
        /** @var Veiculo */
        return $this->veiculos->create($dados + ['user_id' => $user->id]);
    }

    public function atualizar(Veiculo $veiculo, array $dados): Veiculo
    {
        /** @var Veiculo */
        return $this->veiculos->update($veiculo, $dados);
    }

    public function excluir(Veiculo $veiculo): void
    {
        if ($this->veiculos->possuiAgendamentosEmAberto($veiculo)) {
            throw new RegraDeNegocioException('O veículo possui agendamentos em aberto.');
        }

        $this->veiculos->delete($veiculo);
    }
}
