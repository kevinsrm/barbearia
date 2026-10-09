<?php

use App\Http\Controllers\AgendamentoController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\BarbeiroController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\ConfiguracaoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FinanceiroController;
use App\Http\Controllers\InstallController;
use App\Http\Controllers\PessoaController;
use App\Http\Controllers\ProdutoController;
use App\Http\Controllers\RelatorioController;
use App\Http\Controllers\ServicoController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\VendaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Instalador (só aparece enquanto o sistema não foi instalado)
|--------------------------------------------------------------------------
*/
Route::get('/install', [InstallController::class, 'index'])->name('install.index');
Route::post('/install', [InstallController::class, 'store'])->name('install.store');
Route::get('/install/concluido', [InstallController::class, 'concluido'])->name('install.concluido');

/*
|--------------------------------------------------------------------------
| Site público
|--------------------------------------------------------------------------
*/
Route::get('/', [SiteController::class, 'index'])->name('site.index');
Route::get('/servicos', [SiteController::class, 'servicos'])->name('site.servicos');
Route::get('/sobre', [SiteController::class, 'sobre'])->name('site.sobre');
Route::get('/agendar', [SiteController::class, 'agendamento'])->name('site.agendamento');
Route::post('/agendar', [SiteController::class, 'agendamentoStore'])->name('site.agendar.store');
Route::get('/agendar/horarios', [SiteController::class, 'horarios'])->name('site.horarios');

/*
|--------------------------------------------------------------------------
| Autenticação
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/cadastro', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/cadastro', [AuthController::class, 'register'])->name('register.post');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| Painel administrativo
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'permission:acesso-painel'])->prefix('admin')->name('admin.')->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    /* Configurações do sistema (apenas administrador) */
    Route::middleware('role:admin')->group(function () {
        Route::get('/configuracoes', [ConfiguracaoController::class, 'index'])->name('configuracoes');
        Route::post('/configuracoes', [ConfiguracaoController::class, 'update'])->name('configuracoes.update');
    });

    /* Cadastros */
    Route::middleware('permission:gerenciar-pessoas')->group(function () {
        Route::resource('pessoas', PessoaController::class)->except('show');
    });

    Route::middleware('permission:gerenciar-usuarios')->group(function () {
        Route::resource('usuarios', UsuarioController::class)->except('show');
    });

    Route::middleware('permission:gerenciar-servicos')->group(function () {
        Route::resource('servicos', ServicoController::class)->except('show');
    });

    Route::middleware('permission:gerenciar-produtos')->group(function () {
        Route::resource('produtos', ProdutoController::class)->except('show');
        Route::post('produtos/{produto}/estoque', [ProdutoController::class, 'movimentarEstoque'])->name('produtos.estoque');
    });

    /* Categorias (serviços e produtos) */
    Route::middleware('permission:gerenciar-servicos')->group(function () {
        Route::post('categorias/{tipo}', [CategoriaController::class, 'store'])->name('categorias.store');
        Route::match(['put', 'patch'], 'categorias/{tipo}/{id}', [CategoriaController::class, 'update'])->name('categorias.update');
        Route::delete('categorias/{tipo}/{id}', [CategoriaController::class, 'destroy'])->name('categorias.destroy');
    });

    /* Agenda */
    Route::middleware('permission:gerenciar-agendamentos')->group(function () {
        Route::get('agendamentos/horarios/disponiveis', [AgendamentoController::class, 'horariosDisponiveis'])->name('agendamentos.horarios');
        Route::resource('agendamentos', AgendamentoController::class)->except('show');
        Route::patch('agendamentos/{agendamento}/status', [AgendamentoController::class, 'atualizarStatus'])->name('agendamentos.status');
    });

    /* Vendas (PDV) */
    Route::middleware('permission:gerenciar-vendas')->group(function () {
        Route::get('vendas', [VendaController::class, 'index'])->name('vendas.index');
        Route::get('vendas/nova', [VendaController::class, 'create'])->name('vendas.create');
        Route::post('vendas', [VendaController::class, 'store'])->name('vendas.store');
        Route::get('vendas/{venda}', [VendaController::class, 'show'])->name('vendas.show');
        Route::post('vendas/{venda}/cancelar', [VendaController::class, 'cancelar'])->name('vendas.cancelar');
    });

    /* Compras de fornecedor */
    Route::middleware('permission:gerenciar-compras')->group(function () {
        Route::get('compras', [CompraController::class, 'index'])->name('compras.index');
        Route::get('compras/nova', [CompraController::class, 'create'])->name('compras.create');
        Route::post('compras', [CompraController::class, 'store'])->name('compras.store');
        Route::get('compras/{compra}', [CompraController::class, 'show'])->name('compras.show');
    });

    /* Financeiro */
    Route::middleware('permission:gerenciar-financeiro')->group(function () {
        Route::get('financeiro', [FinanceiroController::class, 'index'])->name('financeiro.index');

        Route::get('financeiro/contas', [FinanceiroController::class, 'contas'])->name('financeiro.contas');
        Route::post('financeiro/contas/pagar', [FinanceiroController::class, 'criarContaPagar'])->name('financeiro.contas.pagar.store');
        Route::post('financeiro/contas/receber', [FinanceiroController::class, 'criarContaReceber'])->name('financeiro.contas.receber.store');
        Route::post('financeiro/contas/pagar/{conta}/baixa', [FinanceiroController::class, 'pagarConta'])->name('financeiro.contas.pagar.baixa');
        Route::post('financeiro/contas/receber/{conta}/baixa', [FinanceiroController::class, 'receberConta'])->name('financeiro.contas.receber.baixa');
        Route::delete('financeiro/contas/{tipo}/{id}', [FinanceiroController::class, 'excluirConta'])->name('financeiro.contas.destroy');

        Route::get('financeiro/comissoes', [FinanceiroController::class, 'comissoes'])->name('financeiro.comissoes');
        Route::post('financeiro/comissoes/{comissao}/pagar', [FinanceiroController::class, 'pagarComissao'])->name('financeiro.comissoes.pagar');
        Route::post('financeiro/comissoes/{comissao}/estornar', [FinanceiroController::class, 'estornarComissao'])->name('financeiro.comissoes.estornar');
    });

    /* Relatórios */
    Route::middleware('permission:ver-relatorios')->group(function () {
        Route::get('relatorios', [RelatorioController::class, 'index'])->name('relatorios.index');
        Route::get('relatorios/produtos', [RelatorioController::class, 'produtos'])->name('relatorios.produtos');
        Route::get('relatorios/financeiro', [RelatorioController::class, 'financeiro'])->name('relatorios.financeiro');
        Route::get('relatorios/comissoes', [RelatorioController::class, 'comissoes'])->name('relatorios.comissoes');
        Route::get('relatorios/clientes', [RelatorioController::class, 'clientes'])->name('relatorios.clientes');
        Route::get('relatorios/aniversariantes', [RelatorioController::class, 'aniversariantes'])->name('relatorios.aniversariantes');
        Route::get('relatorios/movimentacoes', [RelatorioController::class, 'movimentacoes'])->name('relatorios.movimentacoes');
    });

    /* Área do profissional (barbeiro) */
    Route::middleware('permission:acesso-barbeiro')->prefix('barbeiro')->name('barbeiro.')->group(function () {
        Route::get('/', [BarbeiroController::class, 'index'])->name('index');
        Route::get('/agenda', [BarbeiroController::class, 'agenda'])->name('agenda');
        Route::get('/comissoes', [BarbeiroController::class, 'comissoes'])->name('comissoes');
        Route::post('/disponibilidade', [BarbeiroController::class, 'atualizarDisponibilidade'])->name('disponibilidade');
    });
});
