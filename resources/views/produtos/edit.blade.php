@extends('layouts.admin')

@section('title', 'Editar produto')

@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('admin.produtos.index') }}">Produtos</a></li>
            <li class="breadcrumb-item active">{{ $produto->nome }}</li>
        </ol>
    </nav>

    <div class="card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <strong><i class="bi bi-box"></i> {{ $produto->nome }}</strong>
            <span class="badge bg-light text-dark border">Estoque atual: {{ $produto->estoque }}</span>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.produtos.update', $produto) }}" method="POST">
                @csrf
                @method('PUT')
                @include('produtos._form')
            </form>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header bg-white"><strong><i class="bi bi-clock-history"></i> Últimas movimentações</strong></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr><th>Data</th><th>Tipo</th><th class="text-end">Qtd.</th><th>Origem</th><th>Observações</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($produto->movimentacoes()->with('usuario')->limit(15)->get() as $movimentacao)
                            <tr>
                                <td class="small">@dataHora($movimentacao->data)</td>
                                <td><span class="badge bg-light text-dark border">{{ $movimentacao->tipo_label }}</span></td>
                                <td class="text-end {{ $movimentacao->quantidade >= 0 ? 'text-success' : 'text-danger' }}">{{ $movimentacao->quantidade > 0 ? '+' : '' }}{{ $movimentacao->quantidade }}</td>
                                <td class="small text-muted">{{ $movimentacao->venda_id ? 'Venda #'.$movimentacao->venda_id : ($movimentacao->compra_id ? 'Compra #'.$movimentacao->compra_id : ($movimentacao->usuario->nome ?? 'Manual')) }}</td>
                                <td class="small text-muted">{{ $movimentacao->observacoes }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-3">Sem movimentações registradas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
