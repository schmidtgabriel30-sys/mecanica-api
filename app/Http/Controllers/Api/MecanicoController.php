<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MecanicoRequest;
use App\Models\Mecanico;
use Illuminate\Support\Facades\Gate;

class MecanicoController extends Controller
{
    public function index()
    {
        return response()->json(Mecanico::where('ativo', true)->orderBy('nome')->get());
    }

    public function store(MecanicoRequest $request)
    {
        Gate::authorize('create', Mecanico::class);

        return response()->json(Mecanico::create($request->validated()), 201);
    }

    public function show(Mecanico $mecanico)
    {
        return response()->json($mecanico);
    }

    public function update(MecanicoRequest $request, Mecanico $mecanico)
    {
        Gate::authorize('update', $mecanico);

        $mecanico->update($request->validated());

        return response()->json($mecanico);
    }

    public function destroy(Mecanico $mecanico)
    {
        Gate::authorize('delete', $mecanico);

        $mecanico->delete();

        return response()->noContent();
    }
}
