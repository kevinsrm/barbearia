@extends('layouts.admin')

@section('title', 'Minhas comissões')

@section('content')
    <h1 class="h4 mb-3"><i class="bi bi-percent"></i> Minhas comissões</h1>

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="card card-metrica h-100"><div class="card-body">
                <div class="text-muted small">Comissão do mês</div>
                <div class="valor">@money($totalMes)</div>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card card-metrica h-100"><div class="card-body">
                <div class="text-muted small">A receber (pendente)</div>
                <div class="valor text-warning">@money($totalPendentes)</div>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card card-metrica h-100"><div class="card-body">
                <div class="text-muted small">Já recebido</div>
                <div class="valor text-success">@money($totalPagas)</div>
            </div></div>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <strong>Histórico</strong>
            <span class="small text-muted">Seu percentual: {{ number_format((float) $funcionario->percentualComissaoEfetivo(), 1) }}%</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Data</th><th>Origem</th><th class="text-end">Base de cálculo</th><th class="text-center">%</th><th class="text-end">Valor</th><th>Situação</th><th>Pagamento</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($comissoes as $comissao)
                            <tr>
                                <td class="small">@data($comissao->data)</td>
                                <td class="small text-muted">
                                    @if ($comissao->venda_id)
                                        Venda #{{ $comissao->venda_id }}
                                        @if ($comissao->venda) · {{ $comissao->venda->forma_pagamento_label }} @endif
                                    @elseif ($comissao->agendamento_id)
                                        Agendamento #{{ $comissao->agendamento_id }}
                                    @else
                                        Lançamento manual
                                    @endif
                                </td>
                                <td class="text-end small">@money($comissao->valor_base)</td>
                                <td class="text-center small">{{ number_format((float) $comissao->percentual, 1) }}%</td>
                                <td class="text-end fw-semibold">@money($comissao->valor)</td>
                                <td>
                                    <span class="badge bg-{{ $comissao->status === 'paga' ? 'success' : 'warning' }} badge-status">
                                        {{ App\Models\Comissao::STATUS[$comissao->status] ?? $comissao->status }}
                                    </span>
                                </td>
                                <td class="small text-muted">{{ $comissao->data_pagamento ? $comissao->data_pagamento->format('d/m/Y') : '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">Você ainda não tem comissões lançadas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($comissoes->hasPages())
            <div class="card-footer bg-white">{{ $comissoes->links() }}</div>
        @endif
    </div>
@endsection
