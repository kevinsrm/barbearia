@extends('layouts.admin')

@section('title', 'Vendas')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h1 class="h4 mb-0"><i class="bi bi-bag-check"></i> Vendas</h1>
            <span class="text-muted small">Total do período filtrado: <strong>@money($totalPeriodo)</strong></span>
        </div>
        <a href="{{ route('admin.vendas.create') }}" class="btn btn-dark btn-sm"><i class="bi bi-plus-lg"></i> Nova venda</a>
    </div>

    <div class="card mb-3">
        <div class="card-body py-2">
            <form action="{{ route('admin.vendas.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-3">
                    <label class="form-label small mb-1" for="busca">Cliente ou nº da venda</label>
                    <input type="search" class="form-control form-control-sm" id="busca" name="busca" value="{{ request('busca') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small mb-1" for="data">Data</label>
                    <input type="date" class="form-control form-control-sm" id="data" name="data" value="{{ request('data') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-1" for="forma_pagamento">Pagamento</label>
                    <select class="form-select form-select-sm" id="forma_pagamento" name="forma_pagamento">
                        <option value="">Todas</option>
                        @foreach ($formasPagamento as $valor => $rotulo)
                            <option value="{{ $valor }}" {{ request('forma_pagamento') === $valor ? 'selected' : '' }}>{{ $rotulo }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-1" for="status">Situação</label>
                    <select class="form-select form-select-sm" id="status" name="status">
                        <option value="">Todas</option>
                        @foreach ($statusDisponiveis as $valor => $rotulo)
                            <option value="{{ $valor }}" {{ request('status') === $valor ? 'selected' : '' }}>{{ $rotulo }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-1">
                    <button class="btn btn-sm btn-dark flex-grow-1" type="submit"><i class="bi bi-search"></i></button>
                    <a href="{{ route('admin.vendas.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x-lg"></i></a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 70px">#</th>
                            <th>Data</th>
                            <th>Cliente</th>
                            <th>Profissional</th>
                            <th>Itens</th>
                            <th>Pagamento</th>
                            <th class="text-end">Total</th>
                            <th>Situação</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($vendas as $venda)
                            <tr>
                                <td class="fw-semibold">#{{ $venda->id }}</td>
                                <td class="small">@dataHora($venda->data)</td>
                                <td class="small">{{ $venda->cliente_nome }}</td>
                                <td class="small">{{ $venda->funcionario->pessoa->nome ?? '—' }}</td>
                                <td class="small text-muted">{{ $venda->itens->count() }} item(ns)</td>
                                <td class="small">{{ $venda->forma_pagamento_label }}</td>
                                <td class="text-end fw-semibold">@money($venda->valor_total)</td>
                                <td>
                                    @php $cores = ['concluida' => 'success', 'pendente' => 'warning', 'cancelada' => 'secondary']; @endphp
                                    <span class="badge bg-{{ $cores[$venda->status] ?? 'secondary' }} badge-status">
                                        {{ App\Models\Venda::STATUS[$venda->status] ?? $venda->status }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('admin.vendas.show', $venda) }}" class="btn btn-sm btn-outline-dark"><i class="bi bi-eye"></i></a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center text-muted py-4">Nenhuma venda registrada neste período.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($vendas->hasPages())
            <div class="card-footer bg-white">{{ $vendas->links() }}</div>
        @endif
    </div>
@endsection
