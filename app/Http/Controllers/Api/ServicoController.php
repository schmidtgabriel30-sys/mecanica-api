<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ServicoRequest;
use App\Models\Servico;
use Illuminate\Support\Facades\Gate;

class ServicoController extends Controller
{
    public function index()
    {
        return response()->json(Servico::where('ativo', true)->orderBy('nome')->get());
    }

    public function store(ServicoRequest $request)
    {
        Gate::authorize('create', Servico::class);

        return response()->json(Servico::create($request->validated()), 201);
    }

    public function show(Servico $servico)
    {
        return response()->json($servico);
    }

    public function update(ServicoRequest $request, Servico $servico)
    {
        Gate::authorize('update', $servico);

        $servico->update($request->validated());

        return response()->json($servico);
    }

    public function destroy(Servico $servico)
    {
        Gate::authorize('delete', $servico);

        if ($servico->agendamentos()->exists()) {
            return response()->json([
                'message' => 'Serviço já utilizado em agendamentos. Desative-o em vez de excluir.',
            ], 409);
        }

        $servico->delete();

        return response()->noContent();
    }
}
