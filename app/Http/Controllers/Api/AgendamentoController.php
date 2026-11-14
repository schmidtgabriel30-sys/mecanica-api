<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AgendamentoRequest;
use App\Models\Agendamento;
use App\Services\AgendamentoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

/**
 * Camada HTTP dos agendamentos. As regras (conflitos de agenda, fluxo de
 * status, permissões do cliente) ficam no AgendamentoService.
 */
class AgendamentoController extends Controller
{
    public function __construct(private readonly AgendamentoService $service) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json($this->service->listar($request->user(), $request->query('status')));
    }

    public function store(AgendamentoRequest $request): JsonResponse
    {
        return response()->json($this->service->criar($request->user(), $request->validated()), 201);
    }

    public function show(Agendamento $agendamento): JsonResponse
    {
        Gate::authorize('view', $agendamento);

        return response()->json($this->service->detalhar($agendamento));
    }

    public function update(AgendamentoRequest $request, Agendamento $agendamento): JsonResponse
    {
        Gate::authorize('update', $agendamento);

        return response()->json($this->service->atualizar($request->user(), $agendamento, $request->validated()));
    }

    public function destroy(Agendamento $agendamento): Response
    {
        Gate::authorize('delete', $agendamento);

        $this->service->excluir($agendamento);

        return response()->noContent();
    }
}
