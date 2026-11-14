<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\VeiculoRequest;
use App\Models\Veiculo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class VeiculoController extends Controller
{
    public function index(Request $request)
    {
        return response()->json($request->user()->veiculos()->latest()->get());
    }

    public function store(VeiculoRequest $request)
    {
        $veiculo = $request->user()->veiculos()->create($request->validated());

        return response()->json($veiculo, 201);
    }

    public function show(Veiculo $veiculo)
    {
        Gate::authorize('view', $veiculo);

        return response()->json($veiculo);
    }

    public function update(VeiculoRequest $request, Veiculo $veiculo)
    {
        Gate::authorize('update', $veiculo);

        $veiculo->update($request->validated());

        return response()->json($veiculo);
    }

    public function destroy(Veiculo $veiculo)
    {
        Gate::authorize('delete', $veiculo);

        $emAberto = $veiculo->agendamentos()
            ->whereIn('status', ['pendente', 'confirmado'])
            ->exists();

        if ($emAberto) {
            return response()->json(['message' => 'O veículo possui agendamentos em aberto.'], 409);
        }

        $veiculo->delete();

        return response()->noContent();
    }
}
