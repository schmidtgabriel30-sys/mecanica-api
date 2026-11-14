<?php

use App\Http\Controllers\Api\AgendamentoController;
use App\Http\Controllers\Api\MecanicoController;
use App\Http\Controllers\Api\ServicoController;
use App\Http\Controllers\Api\VeiculoController;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/teste', function () {
    return response()->json(['mensagem' => 'A API da oficina está online e a funcionar! 🚀']);
});

// Públicas
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
Route::apiResource('servicos', ServicoController::class)->only(['index', 'show']);

// Protegidas (Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn (Request $request) => $request->user());
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::apiResource('servicos', ServicoController::class)->except(['index', 'show']);
    Route::apiResource('mecanicos', MecanicoController::class);
    Route::apiResource('veiculos', VeiculoController::class);
    Route::apiResource('agendamentos', AgendamentoController::class);
});
