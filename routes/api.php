<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AgendamentoController;
use App\Http\Controllers\BarbeiroController;

Route::middleware('auth:sanctum')->group(function () {
    // Agendamento API
    Route::get('/agendamentos/horarios-disponiveis', [AgendamentoController::class, 'getHorariosDisponiveis']);
    Route::post('/agendamentos', [AgendamentoController::class, 'store'])->name('api.agendamentos.store');
    Route::put('/agendamentos/{agendamento}', [AgendamentoController::class, 'update'])->name('api.agendamentos.update');
    Route::delete('/agendamentos/{agendamento}', [AgendamentoController::class, 'destroy'])->name('api.agendamentos.destroy');
    
    // Barbeiro API
    Route::get('/barbeiros/funcionarios', [BarbeiroController::class, 'getFuncionarios']);
    Route::post('/barbeiros/disponibilidade', [BarbeiroController::class, 'atualizarDisponibilidade']);
    
    // Serviços API
    Route::get('/servicos', function() {
        return response()->json(Servico::where('ativo', true)->with('categoria')->get());
    });
});
