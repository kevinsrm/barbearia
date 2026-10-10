@extends('layouts.admin')

@section('title', 'Relatório de estoque')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <h1 class="h4 mb-0"><i class="bi bi-box"></i> Estoque e produtos</h1>
        <form action="{{ route('admin.relatorios.produtos') }}" method="GET" class="d-flex gap-2">
            <div class="form-check mt-1">
                <input class="form-check-input" type="checkbox" name="estoque_baixo" value="1" id="baixo" {{ request('estoque_baixo') ? 'checked' : '' }} onchange="this.form.submit()">
                <label class="form-check-label small" for="baixo">Somente estoque baixo</label>
            </div>
            <button class="btn btn-sm btn-outline-secondary" type="button" onclick="window.print()"><i class="bi bi-printer"></i></button>
        </form>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="card card-metrica"><div class="card-body">
                <div class="text-muted small">Itens em estoque</div>
                <div class="valor">{{ $totalItens }}</div>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card card-metrica"><div class="card-body">
                <div class="text-muted small">Valor a custo</div>
                <div class="valor">@money($totalCusto)</div>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card card-metrica"><div class="card-body">
                <div class="text-muted small">Valor potencial de venda</div>
                <div class="valor text-success">@money($totalVenda)</div>
            </div></div>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Produto</th><th>Categoria</th><th>Código</th>
                            <th class="text-center">Saldo</th><th class="text-center">Mínimo</th>
                            <th class="text-end">Custo</th><th class="text-end">Venda</th>
                            <th class="text-end">Total custo</th><th>Situação</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($produtos as $produto)
                            <tr>
                                <td>{{ $produto->nome }}</td>
                                <td class="small text-muted">{{ $produto->categoria->nome ?? '—' }}</td>
                                <td class="small text-muted">{{ $produto->codigo ?: '—' }}</td>
                                <td class="text-center fw-semibold">{{ $produto->estoque }}</td>
                                <td class="text-center small">{{ $produto->estoque_minimo }}</td>
                                <td class="text-end small">@money($produto->preco_custo)</td>
                                <td class="text-end small">@money($produto->preco_venda)</td>
                                <td class="text-end">@money((float) $produto->preco_custo * (int) $produto->estoque)</td>
                                <td>
                                    @if ($produto->estoque_baixo)
                                        <span class="badge bg-danger badge-status">Repor</span>
                                    @else
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">Ok</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center text-muted py-4">Nenhum produto cadastrado.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
