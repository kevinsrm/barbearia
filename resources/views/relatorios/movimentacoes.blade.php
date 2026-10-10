@extends('layouts.admin')

@section('title', 'Movimentações de estoque')

@section('content')
    <h1 class="h4 mb-3"><i class="bi bi-arrow-left-right"></i> Movimentações de estoque</h1>

    @include('relatorios._periodo', ['acao' => route('admin.relatorios.movimentacoes')])

    <div class="card mb-3">
        <div class="card-body py-2">
            <form action="{{ route('admin.relatorios.movimentacoes') }}" method="GET" class="row g-2 align-items-center">
                <input type="hidden" name="de" value="{{ $de->format('Y-m-d') }}">
                <input type="hidden" name="ate" value="{{ $ate->format('Y-m-d') }}">
                <div class="col-md-8">
                    <select class="form-select form-select-sm" name="produto">
                        <option value="">Todos os produtos</option>
                        @foreach ($produtos as $produto)
                            <option value="{{ $produto->id }}" {{ (string) request('produto') === (string) $produto->id ? 'selected' : '' }}>{{ $produto->nome }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <button class="btn btn-sm btn-dark w-100" type="submit"><i class="bi bi-funnel"></i> Filtrar por produto</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Data</th><th>Produto</th><th>Tipo</th><th class="text-end">Qtd.</th><th>Origem</th><th>Responsável</th><th>Observações</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($movimentacoes as $movimentacao)
                            <tr>
                                <td class="small">@dataHora($movimentacao->data)</td>
                                <td class="small">{{ $movimentacao->produto->nome ?? '—' }}</td>
                                <td><span class="badge bg-light text-dark border">{{ $movimentacao->tipo_label }}</span></td>
                                <td class="text-end fw-semibold {{ $movimentacao->quantidade >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $movimentacao->quantidade > 0 ? '+' : '' }}{{ $movimentacao->quantidade }}
                                </td>
                                <td class="small text-muted">
                                    @if ($movimentacao->venda_id) Venda #{{ $movimentacao->venda_id }}
                                    @elseif ($movimentacao->compra_id) Compra #{{ $movimentacao->compra_id }}
                                    @else Manual @endif
                                </td>
                                <td class="small text-muted">{{ $movimentacao->usuario->nome ?? '—' }}</td>
                                <td class="small text-muted">{{ $movimentacao->observacoes }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">Nenhuma movimentação no período.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($movimentacoes->hasPages())
            <div class="card-footer bg-white">{{ $movimentacoes->links() }}</div>
        @endif
    </div>
@endsection
