<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MecanicoRequest;
use App\Models\Mecanico;
use App\Services\MecanicoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class MecanicoController extends Controller
{
    public function __construct(private readonly MecanicoService $service) {}

    public function index(): JsonResponse
    {
        return response()->json($this->service->listarAtivos());
    }

    public function store(MecanicoRequest $request): JsonResponse
    {
        Gate::authorize('create', Mecanico::class);

        return response()->json($this->service->criar($request->validated()), 201);
    }

    public function show(Mecanico $mecanico): JsonResponse
    {
        return response()->json($mecanico);
    }

    public function update(MecanicoRequest $request, Mecanico $mecanico): JsonResponse
    {
        Gate::authorize('update', $mecanico);

        return response()->json($this->service->atualizar($mecanico, $request->validated()));
    }

    public function destroy(Mecanico $mecanico): Response
    {
        Gate::authorize('delete', $mecanico);

        $this->service->excluir($mecanico);

        return response()->noContent();
    }
}
