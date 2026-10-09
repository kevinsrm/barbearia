@extends('layouts.admin')

@section('title', 'Relatório de comissões')

@section('content')
    <h1 class="h4 mb-3"><i class="bi bi-percent"></i> Comissões por profissional</h1>

    @include('relatorios._periodo', ['acao' => route('admin.relatorios.comissoes')])

    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <div class="card card-metrica"><div class="card-body">
                <div class="text-muted small">Total gerado no período</div>
                <div class="valor">@money($totalGeral)</div>
            </div></div>
        </div>
        <div class="col-md-6">
            <div class="card card-metrica"><div class="card-body">
                <div class="text-muted small">Total já pago</div>
                <div class="valor text-success">@money($totalPago)</div>
                <div class="small text-muted">Em aberto: @money($totalGeral - $totalPago)</div>
            </div></div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header bg-white"><strong>Resumo por profissional</strong></div>
        <div class="card-body p-0">
            <table class="table table-sm mb-0">
                <thead class="table-light">
                    <tr><th>Profissional</th><th class="text-center">Atendimentos</th><th class="text-end">Total</th><th class="text-end">Pago</th><th class="text-end">Em aberto</th></tr>
                </thead>
                <tbody>
                    @forelse ($porFuncionario as $linha)
                        <tr>
                            <td>{{ $linha->funcionario->pessoa->nome ?? 'Profissional #'.$linha->funcionario_id }}</td>
                            <td class="text-center">{{ $linha->quantidade }}</td>
                            <td class="text-end fw-semibold">@money($linha->total)</td>
                            <td class="text-end text-success">@money($linha->pago)</td>
                            <td class="text-end text-warning">@money($linha->total - $linha->pago)</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-3">Sem comissões no período.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-white"><strong>Detalhamento</strong></div>
        <div class="card-body p-0">
            <div class="table-responsive" style="max-height: 420px; overflow-y: auto;">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light sticky-top">
                        <tr><th>Data</th><th>Profissional</th><th>Origem</th><th class="text-end">Base</th><th class="text-center">%</th><th class="text-end">Valor</th><th>Situação</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($detalhes as $comissao)
                            <tr>
                                <td class="small">@data($comissao->data)</td>
                                <td class="small">{{ $comissao->funcionario_nome }}</td>
                                <td class="small">{{ $comissao->venda_id ? 'Venda #'.$comissao->venda_id : '—' }}</td>
                                <td class="text-end small">@money($comissao->valor_base)</td>
                                <td class="text-center small">{{ number_format((float) $comissao->percentual, 1) }}%</td>
                                <td class="text-end fw-semibold">@money($comissao->valor)</td>
                                <td><span class="badge bg-{{ $comissao->status === 'paga' ? 'success' : 'warning' }} badge-status">{{ App\Models\Comissao::STATUS[$comissao->status] ?? $comissao->status }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-3">Sem lançamentos.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
