@extends('layouts.admin')

@section('title', 'Compra #'.$compra->id)

@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('admin.compras.index') }}">Compras</a></li>
            <li class="breadcrumb-item active">#{{ $compra->id }}</li>
        </ol>
    </nav>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-white">
                    <strong><i class="bi bi-box-seam"></i> Compra #{{ $compra->id }}</strong>
                    <span class="text-muted small ms-2">@data($compra->data) · {{ $compra->fornecedor_nome }}</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Produto</th>
                                    <th class="text-center">Qtd.</th>
                                    <th class="text-end">Custo unit.</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($compra->itens as $item)
                                    <tr>
                                        <td>
                                            {{ $item->descricao }}
                                            @if ($item->produto)
                                                <div class="small text-muted">Estoque atual: {{ $item->produto->estoque }}</div>
                                            @endif
                                        </td>
                                        <td class="text-center">{{ $item->quantidade }}</td>
                                        <td class="text-end">@money($item->valor_unitario)</td>
                                        <td class="text-end fw-semibold">@money($item->valor_total)</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="3" class="text-end fs-5">Total</th>
                                    <th class="text-end fs-5">@money($compra->valor_total)</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-white"><strong>Resumo</strong></div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Fornecedor</span><span>{{ $compra->fornecedor_nome }}</span></li>
                    <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Data</span><span>@data($compra->data)</span></li>
                    <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Pagamento</span><span class="text-capitalize">{{ $compra->forma_pagamento }}</span></li>
                    <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Prazo</span><span>{{ $compra->prazo_pagamento }} dias</span></li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Situação</span>
                        <span class="badge bg-{{ $compra->status === 'recebida' ? 'success' : 'warning' }} badge-status">
                            {{ App\Models\Compra::STATUS[$compra->status] ?? $compra->status }}
                        </span>
                    </li>
                    @if ($compra->observacoes)
                        <li class="list-group-item"><span class="text-muted d-block">Observações</span>{{ $compra->observacoes }}</li>
                    @endif
                </ul>
            </div>

            @if ($compra->contaPagar->isNotEmpty())
                <div class="card mt-3">
                    <div class="card-header bg-white"><strong><i class="bi bi-journals"></i> Contas a pagar geradas</strong></div>
                    <ul class="list-group list-group-flush">
                        @foreach ($compra->contaPagar as $conta)
                            <li class="list-group-item d-flex justify-content-between">
                                <div>
                                    <div class="small">Venc. @data($conta->vencimento)</div>
                                    <span class="badge bg-{{ $conta->status === 'paga' ? 'success' : 'warning' }} badge-status">{{ App\Models\ContaPagar::STATUS[$conta->status] ?? $conta->status }}</span>
                                </div>
                                <strong>@money($conta->valor)</strong>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card mt-3">
                <div class="card-header bg-white"><strong><i class="bi bi-clock-history"></i> Entradas de estoque</strong></div>
                <ul class="list-group list-group-flush">
                    @forelse ($compra->movimentacoes as $movimentacao)
                        <li class="list-group-item d-flex justify-content-between small">
                            <span>{{ $movimentacao->produto->nome ?? 'Produto' }}</span>
                            <span class="text-success">+{{ $movimentacao->quantidade }}</span>
                        </li>
                    @empty
                        <li class="list-group-item text-muted small">Compra ainda não recebida — o estoque não foi alterado.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
@endsection
