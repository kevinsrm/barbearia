@extends('layouts.admin')

@section('title', 'Relatórios')

@section('content')
    <h1 class="h4 mb-3"><i class="bi bi-graph-up"></i> Relatórios</h1>

    <div class="row g-3">
        <div class="col-md-6 col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="h6"><i class="bi bi-cash-stack text-success"></i> Financeiro</h2>
                    <p class="text-muted small">Vendas, compras, comissões e lucro por período.</p>
                    <a href="{{ route('admin.relatorios.financeiro') }}" class="btn btn-sm btn-outline-dark">Abrir relatório</a>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="h6"><i class="bi bi-box text-primary"></i> Estoque e produtos</h2>
                    <p class="text-muted small">Saldo atual, valor de custo e alerta de estoque mínimo.</p>
                    <a href="{{ route('admin.relatorios.produtos') }}" class="btn btn-sm btn-outline-dark">Abrir relatório</a>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="h6"><i class="bi bi-percent text-warning"></i> Comissões por profissional</h2>
                    <p class="text-muted small">Quanto cada barbeiro gerou e quanto ainda está pendente.</p>
                    <a href="{{ route('admin.relatorios.comissoes') }}" class="btn btn-sm btn-outline-dark">Abrir relatório</a>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="h6"><i class="bi bi-people text-info"></i> Clientes</h2>
                    <p class="text-muted small">Ranking de clientes por valor gasto no período.</p>
                    <a href="{{ route('admin.relatorios.clientes') }}" class="btn btn-sm btn-outline-dark">Abrir relatório</a>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="h6"><i class="bi bi-gift text-danger"></i> Aniversariantes do mês</h2>
                    <p class="text-muted small">Lista para ações de fidelização e parabéns.</p>
                    <a href="{{ route('admin.relatorios.aniversariantes') }}" class="btn btn-sm btn-outline-dark">Abrir relatório</a>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="h6"><i class="bi bi-arrow-left-right text-secondary"></i> Movimentações de estoque</h2>
                    <p class="text-muted small">Entradas, saídas e ajustes com origem e responsável.</p>
                    <a href="{{ route('admin.relatorios.movimentacoes') }}" class="btn btn-sm btn-outline-dark">Abrir relatório</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-12">
            <div class="alert alert-light border small mb-0">
                <strong>Resumo rápido:</strong>
                {{ $contasPagarCount }} conta(s) a pagar (@money($contasPagarTotal)) ·
                {{ $contasReceberCount }} a receber (@money($contasReceberTotal)) ·
                {{ $totalClientes }} cliente(s) ·
                {{ $totalProdutos }} produto(s), {{ $estoqueBaixo }} com estoque baixo.
            </div>
        </div>
    </div>
@endsection
