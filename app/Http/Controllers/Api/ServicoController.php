<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ServicoRequest;
use App\Models\Servico;
use App\Services\ServicoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class ServicoController extends Controller
{
    public function __construct(private readonly ServicoService $service) {}

    public function index(): JsonResponse
    {
        return response()->json($this->service->listarAtivos());
    }

    public function store(ServicoRequest $request): JsonResponse
    {
        Gate::authorize('create', Servico::class);

        return response()->json($this->service->criar($request->validated()), 201);
    }

    public function show(Servico $servico): JsonResponse
    {
        return response()->json($servico);
    }

    public function update(ServicoRequest $request, Servico $servico): JsonResponse
    {
        Gate::authorize('update', $servico);

        return response()->json($this->service->atualizar($servico, $request->validated()));
    }

    public function destroy(Servico $servico): Response
    {
        Gate::authorize('delete', $servico);

        $this->service->excluir($servico);

        return response()->noContent();
    }
}
