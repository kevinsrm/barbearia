@extends('layouts.admin')

@section('title', 'Venda #'.$venda->id)

@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('admin.vendas.index') }}">Vendas</a></li>
            <li class="breadcrumb-item active">#{{ $venda->id }}</li>
        </ol>
    </nav>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <strong><i class="bi bi-receipt"></i> Venda #{{ $venda->id }}</strong>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-secondary" onclick="window.print()"><i class="bi bi-printer"></i> Imprimir</button>
                        @if ($venda->status !== 'cancelada')
                            <form action="{{ route('admin.vendas.cancelar', $venda) }}" method="POST" onsubmit="return confirm('Cancelar esta venda? O estoque será devolvido.');">
                                @csrf
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-x-circle"></i> Cancelar venda</button>
                            </form>
                        @endif
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Item</th>
                                    <th>Tipo</th>
                                    <th class="text-center">Qtd.</th>
                                    <th class="text-end">Unitário</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($venda->itens as $item)
                                    <tr>
                                        <td>{{ $item->descricao }}</td>
                                        <td><span class="badge bg-light text-dark border">{{ $item->tipo }}</span></td>
                                        <td class="text-center">{{ $item->quantidade }}</td>
                                        <td class="text-end">@money($item->valor_unitario)</td>
                                        <td class="text-end fw-semibold">@money($item->valor_total)</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="4" class="text-end">Subtotal</th>
                                    <th class="text-end">@money($venda->itens->sum('valor_total'))</th>
                                </tr>
                                @if ((float) $venda->desconto > 0)
                                    <tr>
                                        <th colspan="4" class="text-end text-danger">Desconto</th>
                                        <th class="text-end text-danger">- @money($venda->desconto)</th>
                                    </tr>
                                @endif
                                <tr>
                                    <th colspan="4" class="text-end fs-5">Total</th>
                                    <th class="text-end fs-5">@money($venda->valor_total)</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            @if ($venda->comissoes->isNotEmpty())
                <div class="card mt-3">
                    <div class="card-header bg-white"><strong><i class="bi bi-percent"></i> Comissões geradas</strong></div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
                                <tr><th>Profissional</th><th class="text-end">Base</th><th class="text-center">%</th><th class="text-end">Valor</th><th>Situação</th></tr>
                            </thead>
                            <tbody>
                                @foreach ($venda->comissoes as $comissao)
                                    <tr>
                                        <td>{{ $comissao->funcionario->pessoa->nome ?? '—' }}</td>
                                        <td class="text-end">@money($comissao->valor_base)</td>
                                        <td class="text-center">{{ number_format((float) $comissao->percentual, 1) }}%</td>
                                        <td class="text-end fw-semibold">@money($comissao->valor)</td>
                                        <td>
                                            <span class="badge bg-{{ $comissao->status === 'paga' ? 'success' : 'warning' }} badge-status">
                                                {{ App\Models\Comissao::STATUS[$comissao->status] ?? $comissao->status }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-white"><strong>Resumo</strong></div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Cliente</span><span>{{ $venda->cliente_nome }}</span></li>
                    <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Profissional</span><span>{{ $venda->funcionario->pessoa->nome ?? '—' }}</span></li>
                    <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Data</span><span>@dataHora($venda->data)</span></li>
                    <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Pagamento</span><span>{{ $venda->forma_pagamento_label }}</span></li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Situação</span>
                        <span class="badge bg-{{ $venda->status === 'cancelada' ? 'secondary' : 'success' }} badge-status">
                            {{ App\Models\Venda::STATUS[$venda->status] ?? $venda->status }}
                        </span>
                    </li>
                    @if ($venda->agendamento)
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Agendamento</span>
                            <a href="{{ route('admin.agendamentos.edit', $venda->agendamento) }}">#{{ $venda->agendamento->id }}</a>
                        </li>
                    @endif
                    @if ($venda->observacoes)
                        <li class="list-group-item"><span class="text-muted d-block">Observações</span>{{ $venda->observacoes }}</li>
                    @endif
                    <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Registrada por</span><span>{{ $venda->usuario->nome ?? '—' }}</span></li>
                </ul>
            </div>

            @if ($venda->contaReceber->isNotEmpty())
                <div class="card mt-3">
                    <div class="card-header bg-white"><strong><i class="bi bi-journals"></i> Conta a receber</strong></div>
                    <ul class="list-group list-group-flush">
                        @foreach ($venda->contaReceber as $conta)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="small">Venc. @data($conta->vencimento)</div>
                                    <span class="badge bg-{{ $conta->status === 'recebida' ? 'success' : 'warning' }} badge-status">
                                        {{ App\Models\ContaReceber::STATUS[$conta->status] ?? $conta->status }}
                                    </span>
                                </div>
                                <strong>@money($conta->valor)</strong>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>
@endsection
