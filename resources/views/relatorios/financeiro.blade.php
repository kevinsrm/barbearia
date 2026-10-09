@extends('layouts.admin')

@section('title', 'Relatório financeiro')

@section('content')
    <h1 class="h4 mb-3"><i class="bi bi-cash-stack"></i> Relatório financeiro</h1>

    @include('relatorios._periodo', ['acao' => route('admin.relatorios.financeiro')])

    <div class="row g-3 mb-3">
        <div class="col-sm-6 col-xl-3">
            <div class="card card-metrica h-100"><div class="card-body">
                <div class="text-muted small">Vendas</div>
                <div class="valor text-success">@money($totalVendas)</div>
                <div class="small text-muted">{{ $quantidadeVendas }} venda(s)</div>
            </div></div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card card-metrica h-100"><div class="card-body">
                <div class="text-muted small">Compras</div>
                <div class="valor text-danger">@money($totalCompras)</div>
                <div class="small text-muted">Entrada de mercadoria</div>
            </div></div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card card-metrica h-100"><div class="card-body">
                <div class="text-muted small">Comissões</div>
                <div class="valor text-warning">@money($totalComissoes)</div>
                <div class="small text-muted">Geradas no período</div>
            </div></div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card card-metrica h-100"><div class="card-body">
                <div class="text-muted small">Resultado</div>
                <div class="valor {{ $lucro >= 0 ? 'text-success' : 'text-danger' }}">@money($lucro)</div>
                <div class="small text-muted">Ticket médio @money($ticketMedio)</div>
            </div></div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header bg-white"><strong>Por forma de pagamento</strong></div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead class="table-light"><tr><th>Forma</th><th class="text-center">Qtd.</th><th class="text-end">Total</th></tr></thead>
                        <tbody>
                            @forelse ($porFormaPagamento as $linha)
                                <tr>
                                    <td>{{ $formasPagamento[$linha->forma_pagamento] ?? $linha->forma_pagamento }}</td>
                                    <td class="text-center">{{ $linha->quantidade }}</td>
                                    <td class="text-end">@money($linha->total)</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted py-3">Sem vendas no período.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header bg-white"><strong>Vendas por dia</strong></div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 380px; overflow-y: auto;">
                        <table class="table table-sm mb-0">
                            <thead class="table-light sticky-top"><tr><th>Dia</th><th class="text-center">Vendas</th><th class="text-end">Total</th><th style="width: 40%">Participação</th></tr></thead>
                            <tbody>
                                @php $maiorDia = max(1, (float) $porDia->max('total')); @endphp
                                @forelse ($porDia as $dia)
                                    <tr>
                                        <td class="small">@data($dia->dia)</td>
                                        <td class="text-center small">{{ $dia->quantidade }}</td>
                                        <td class="text-end small fw-semibold">@money($dia->total)</td>
                                        <td>
                                            <div class="progress" style="height: 8px">
                                                <div class="progress-bar bg-success" style="width: {{ round(((float) $dia->total / $maiorDia) * 100) }}%"></div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted py-3">Sem vendas no período.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
