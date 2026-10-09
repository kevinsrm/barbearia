@extends('layouts.admin')

@section('title', 'Compras')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h1 class="h4 mb-0"><i class="bi bi-box-seam"></i> Compras de fornecedor</h1>
            <span class="text-muted small">Total registrado: <strong>@money($totalPeriodo)</strong></span>
        </div>
        <a href="{{ route('admin.compras.create') }}" class="btn btn-dark btn-sm"><i class="bi bi-plus-lg"></i> Nova compra</a>
    </div>

    <div class="card mb-3">
        <div class="card-body py-2">
            <form action="{{ route('admin.compras.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-5">
                    <select class="form-select form-select-sm" name="fornecedor">
                        <option value="">Todos os fornecedores</option>
                        @foreach ($fornecedores as $fornecedor)
                            <option value="{{ $fornecedor->id }}" {{ (string) request('fornecedor') === (string) $fornecedor->id ? 'selected' : '' }}>
                                {{ $fornecedor->pessoa->nome ?? 'Fornecedor' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <select class="form-select form-select-sm" name="status">
                        <option value="">Todas as situações</option>
                        @foreach (App\Models\Compra::STATUS as $valor => $rotulo)
                            <option value="{{ $valor }}" {{ request('status') === $valor ? 'selected' : '' }}>{{ $rotulo }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-1">
                    <button class="btn btn-sm btn-dark flex-grow-1" type="submit"><i class="bi bi-search"></i> Filtrar</button>
                    <a href="{{ route('admin.compras.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x-lg"></i></a>
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
                            <th>Fornecedor</th>
                            <th class="text-center">Itens</th>
                            <th>Pagamento</th>
                            <th class="text-end">Total</th>
                            <th>Situação</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($compras as $compra)
                            <tr>
                                <td class="fw-semibold">#{{ $compra->id }}</td>
                                <td class="small">@data($compra->data)</td>
                                <td class="small">{{ $compra->fornecedor_nome }}</td>
                                <td class="text-center small">{{ $compra->itens->count() }}</td>
                                <td class="small text-capitalize">{{ $compra->forma_pagamento }}</td>
                                <td class="text-end fw-semibold">@money($compra->valor_total)</td>
                                <td>
                                    @php $cores = ['recebida' => 'success', 'pendente' => 'warning', 'cancelada' => 'secondary']; @endphp
                                    <span class="badge bg-{{ $cores[$compra->status] ?? 'secondary' }} badge-status">
                                        {{ App\Models\Compra::STATUS[$compra->status] ?? $compra->status }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('admin.compras.show', $compra) }}" class="btn btn-sm btn-outline-dark"><i class="bi bi-eye"></i></a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted py-4">Nenhuma compra registrada.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($compras->hasPages())
            <div class="card-footer bg-white">{{ $compras->links() }}</div>
        @endif
    </div>
@endsection
