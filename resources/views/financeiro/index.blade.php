@extends('layouts.admin')

@section('title', 'Financeiro')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <h1 class="h4 mb-0"><i class="bi bi-cash-stack"></i> Financeiro</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.financeiro.contas') }}" class="btn btn-outline-dark btn-sm"><i class="bi bi-journals"></i> Contas</a>
            <a href="{{ route('admin.financeiro.comissoes') }}" class="btn btn-outline-dark btn-sm"><i class="bi bi-percent"></i> Comissões</a>
            <a href="{{ route('admin.relatorios.financeiro') }}" class="btn btn-dark btn-sm"><i class="bi bi-graph-up"></i> Relatório</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card card-metrica h-100">
                <div class="card-body">
                    <div class="text-muted small">Vendas hoje</div>
                    <div class="valor text-success">@money($vendasHoje)</div>
                    <div class="small text-muted">No mês: @money($vendasMes)</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card card-metrica h-100">
                <div class="card-body">
                    <div class="text-muted small">Compras no mês</div>
                    <div class="valor text-danger">@money($comprasMes)</div>
                    <div class="small text-muted">Entrada de mercadoria</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card card-metrica h-100">
                <div class="card-body">
                    <div class="text-muted small">Comissões pendentes</div>
                    <div class="valor text-warning">@money($comissoesPendentes)</div>
                    <div class="small text-muted">Pagas no mês: @money($comissoesPagasMes)</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card card-metrica h-100">
                <div class="card-body">
                    <div class="text-muted small">Saldo de contas</div>
                    <div class="valor">@money($aReceber - $aPagar)</div>
                    <div class="small text-muted">A receber @money($aReceber) · A pagar @money($aPagar)</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header bg-white"><strong><i class="bi bi-pie-chart"></i> Vendas do mês por forma de pagamento</strong></div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr><th>Forma</th><th class="text-center">Vendas</th><th class="text-end">Total</th><th class="text-end">Participação</th></tr>
                        </thead>
                        <tbody>
                            @php $totalGeral = $porFormaPagamento->sum('total') ?: 1; @endphp
                            @forelse ($porFormaPagamento as $linha)
                                <tr>
                                    <td>{{ $formasPagamento[$linha->forma_pagamento] ?? $linha->forma_pagamento }}</td>
                                    <td class="text-center">{{ $linha->quantidade }}</td>
                                    <td class="text-end">@money($linha->total)</td>
                                    <td class="text-end text-muted">{{ number_format(($linha->total / $totalGeral) * 100, 1) }}%</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-3">Sem vendas neste mês.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <strong><i class="bi bi-clock-history"></i> Últimas vendas</strong>
                    <a href="{{ route('admin.vendas.index') }}" class="btn btn-sm btn-outline-secondary">Ver todas</a>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr><th>#</th><th>Data</th><th>Cliente</th><th>Pagamento</th><th class="text-end">Total</th></tr>
                        </thead>
                        <tbody>
                            @forelse ($ultimasVendas as $venda)
                                <tr>
                                    <td><a href="{{ route('admin.vendas.show', $venda) }}">#{{ $venda->id }}</a></td>
                                    <td class="small">@dataHora($venda->data)</td>
                                    <td class="small">{{ $venda->cliente_nome }}</td>
                                    <td class="small">{{ $venda->forma_pagamento_label }}</td>
                                    <td class="text-end fw-semibold">@money($venda->valor_total)</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-3">Nenhuma venda ainda.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
