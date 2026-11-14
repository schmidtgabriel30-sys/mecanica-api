<?php

namespace App\Services;

use App\Exceptions\RegraDeNegocioException;
use App\Models\Servico;
use App\Repositories\Contracts\ServicoRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Catálogo de serviços da oficina (gerenciado pelo admin).
 */
class ServicoService
{
    public function __construct(private readonly ServicoRepositoryInterface $servicos) {}

    public function listarAtivos(): Collection
    {
        return $this->servicos->listarAtivos();
    }

    public function criar(array $dados): Servico
    {
        /** @var Servico */
        return $this->servicos->create($dados);
    }

    public function atualizar(Servico $servico, array $dados): Servico
    {
        /** @var Servico */
        return $this->servicos->update($servico, $dados);
    }

    /**
     * Serviço já usado em agendamentos não pode ser apagado, para não
     * perder o histórico. Nesse caso o admin deve desativá-lo.
     */
    public function excluir(Servico $servico): void
    {
        if ($this->servicos->possuiAgendamentos($servico)) {
            throw new RegraDeNegocioException('Serviço já utilizado em agendamentos. Desative-o em vez de excluir.');
        }

        $this->servicos->delete($servico);
    }
}
