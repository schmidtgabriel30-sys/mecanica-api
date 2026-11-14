<?php

namespace App\Providers;

use App\Repositories\Contracts\AgendamentoRepositoryInterface;
use App\Repositories\Contracts\MecanicoRepositoryInterface;
use App\Repositories\Contracts\ServicoRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Contracts\VeiculoRepositoryInterface;
use App\Repositories\Eloquent\AgendamentoRepository;
use App\Repositories\Eloquent\MecanicoRepository;
use App\Repositories\Eloquent\ServicoRepository;
use App\Repositories\Eloquent\UserRepository;
use App\Repositories\Eloquent\VeiculoRepository;
use Illuminate\Support\ServiceProvider;

/**
 * Liga cada interface de repositório à sua implementação Eloquent.
 *
 * Os Services dependem só das interfaces. Para trocar a forma de acesso
 * aos dados, basta mudar a implementação aqui.
 */
class RepositoryServiceProvider extends ServiceProvider
{
    public array $bindings = [
        UserRepositoryInterface::class => UserRepository::class,
        VeiculoRepositoryInterface::class => VeiculoRepository::class,
        ServicoRepositoryInterface::class => ServicoRepository::class,
        MecanicoRepositoryInterface::class => MecanicoRepository::class,
        AgendamentoRepositoryInterface::class => AgendamentoRepository::class,
    ];
}
