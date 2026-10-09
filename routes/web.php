<?php

use Illuminate\Support\Facades\Route;

Route::get('/', [App\Http\Controllers\SiteController::class, 'index'])->name('site.index');
Route::get('/u', [App\Http\Controllers\SiteController::class, 'u'])->name('site.index');
Route::get('/agendamento', [App\Http\Controllers\AgendamentoController::class, 'index'])->name('agendamento.index');
Route::get('/servicos', [App\Http\Controllers\SiteController::class, 'servicos'])->name('site.servicos');
Route::get('/sobre', [App\Http\Controllers\SiteController::class, 'sobre'])->name('site.sobre');

// Auth routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [App\Http\Controllers\Auth\AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [App\Http\Controllers\Auth\AuthController::class, 'login']);
    Route::get('/register', [App\Http\Controllers\Auth\AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [App\Http\Controllers\Auth\AuthController::class, 'register']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [App\Http\Controllers\Auth\AuthController::class, 'logout'])->name('logout');
    
    // Dashboard
    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
    
    // Pessoas
    Route::resource('/pessoas', App\Http\Controllers\PessoaController::class)->middleware('permission:gerenciar-pessoas');
    
    // Serviços
    Route::get('/servicos/manage', [App\Http\Controllers\ServicoController::class, 'index'])->name('servicos.manage');
    Route::resource('/servicos', App\Http\Controllers\ServicoController::class)->middleware('permission:gerenciar-servicos');
    
    // Produtos
    Route::resource('/produtos', App\Http\Controllers\ProdutoController::class)->middleware('permission:gerenciar-produtos');
    
    // Agendamentos
    Route::resource('/agendamentos', App\Http\Controllers\AgendamentoController::class)->middleware('permission:gerenciar-agendamentos');
    Route::get('/agendamentos/create', [App\Http\Controllers\AgendamentoController::class, 'create'])->name('agendamentos.create');
    
    // Financeiro
    Route::get('/financeiro', [App\Http\Controllers\FinanceiroController::class, 'index'])->name('financeiro.index');
    Route::get('/financeiro/vendas', [App\Http\Controllers\FinanceiroController::class, 'vendas'])->name('financeiro.vendas');
    Route::get('/financeiro/compras', [App\Http\Controllers\FinanceiroController::class, 'compras'])->name('financeiro.compras');
    Route::get('/financeiro/comissoes', [App\Http\Controllers\FinanceiroController::class, 'comissoes'])->name('financeiro.comissoes');
    
    // Relatórios
    Route::get('/relatorios', [App\Http\Controllers\RelatorioController::class, 'index'])->name('relatorios.index');
    Route::get('/relatorios/produtos', [App\Http\Controllers\RelatorioController::class, 'produtos'])->name('relatorios.produtos');
    Route::get('/relatorios/financeiro', [App\Http\Controllers\RelatorioController::class, 'financeiro'])->name('relatorios.financeiro');
    Route::get('/relatorios/comissoes', [App\Http\Controllers\RelatorioController::class, 'comissoes'])->name('relatorios.comissoes');
    
    // Área do Barbeiro
    Route::middleware('permission:acesso-barbeiro')->group(function () {
        Route::get('/barbeiro', [App\Http\Controllers\BarbeiroController::class, 'index'])->name('barbeiro.index');
        Route::get('/barbeiro/agenda', [App\Http\Controllers\BarbeiroController::class, 'agenda'])->name('barbeiro.agenda');
        Route::get('/barbeiro/comissoes', [App\Http\Controllers\BarbeiroController::class, 'comissoes'])->name('barbeiro.comissoes');
    });
});