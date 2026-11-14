<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\VeiculoRequest;
use App\Models\Veiculo;
use App\Services\VeiculoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

/**
 * Camada HTTP dos veículos: valida (Request), autoriza (Policy) e delega
 * a regra de negócio ao VeiculoService.
 */
class VeiculoController extends Controller
{
    public function __construct(private readonly VeiculoService $service) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json($this->service->listar($request->user()));
    }

    public function store(VeiculoRequest $request): JsonResponse
    {
        return response()->json($this->service->criar($request->user(), $request->validated()), 201);
    }

    public function show(Veiculo $veiculo): JsonResponse
    {
        Gate::authorize('view', $veiculo);

        return response()->json($veiculo);
    }

    public function update(VeiculoRequest $request, Veiculo $veiculo): JsonResponse
    {
        Gate::authorize('update', $veiculo);

        return response()->json($this->service->atualizar($veiculo, $request->validated()));
    }

    public function destroy(Veiculo $veiculo): Response
    {
        Gate::authorize('delete', $veiculo);

        $this->service->excluir($veiculo);

        return response()->noContent();
    }
}
