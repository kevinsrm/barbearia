@extends('layouts.admin')

@section('title', 'Produtos')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <h1 class="h4 mb-0"><i class="bi bi-box"></i> Produtos e estoque</h1>
        <a href="{{ route('admin.produtos.create') }}" class="btn btn-dark btn-sm"><i class="bi bi-plus-lg"></i> Novo produto</a>
    </div>

    <div class="row g-3">
        <div class="col-lg-9">
            <div class="card mb-3">
                <div class="card-body py-2">
                    <form action="{{ route('admin.produtos.index') }}" method="GET" class="row g-2 align-items-center">
                        <div class="col-md-5">
                            <input type="search" class="form-control form-control-sm" name="busca" value="{{ request('busca') }}" placeholder="Nome ou código">
                        </div>
                        <div class="col-md-4">
                            <select class="form-select form-select-sm" name="categoria">
                                <option value="">Todas as categorias</option>
                                @foreach ($categorias as $categoria)
                                    <option value="{{ $categoria->id }}" {{ (string) request('categoria') === (string) $categoria->id ? 'selected' : '' }}>{{ $categoria->nome }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 d-flex gap-1 align-items-center">
                            <div class="form-check mb-0">
                                <input class="form-check-input" type="checkbox" name="estoque_baixo" value="1" id="estoque_baixo" {{ request('estoque_baixo') ? 'checked' : '' }}>
                                <label class="form-check-label small" for="estoque_baixo">Estoque baixo</label>
                            </div>
                            <button class="btn btn-sm btn-dark" type="submit"><i class="bi bi-search"></i></button>
                            <a href="{{ route('admin.produtos.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x-lg"></i></a>
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
                                    <th>Produto</th>
                                    <th>Categoria</th>
                                    <th class="text-end">Custo</th>
                                    <th class="text-end">Venda</th>
                                    <th class="text-center">Estoque</th>
                                    <th>Situação</th>
                                    <th class="text-end">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($produtos as $produto)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $produto->nome }}</div>
                                            <div class="small text-muted">{{ $produto->codigo ?: 'sem código' }}</div>
                                        </td>
                                        <td class="small">{{ $produto->categoria->nome ?? '—' }}</td>
                                        <td class="text-end small">@money($produto->preco_custo)</td>
                                        <td class="text-end fw-semibold">@money($produto->preco_venda)</td>
                                        <td class="text-center">
                                            <span class="badge {{ $produto->estoque_baixo ? 'bg-danger' : 'bg-light text-dark border' }}">
                                                {{ $produto->estoque }} {{ $produto->unidade }}
                                            </span>
                                            <div class="small text-muted">mín. {{ $produto->estoque_minimo }}</div>
                                        </td>
                                        <td>
                                            @if ($produto->ativo)
                                                <span class="badge bg-success-subtle text-success border border-success-subtle">Ativo</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary border">Inativo</span>
                                            @endif
                                        </td>
                                        <td class="text-end text-nowrap">
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-primary" title="Ajustar estoque" data-bs-toggle="modal" data-bs-target="#modalEstoque"
                                                        data-produto="{{ $produto->id }}" data-nome="{{ $produto->nome }}" data-estoque="{{ $produto->estoque }}">
                                                    <i class="bi bi-box-arrow-in-down"></i>
                                                </button>
                                                <a href="{{ route('admin.produtos.edit', $produto) }}" class="btn btn-outline-secondary" title="Editar"><i class="bi bi-pencil"></i></a>
                                                <form action="{{ route('admin.produtos.destroy', $produto) }}" method="POST" onsubmit="return confirm('Remover {{ $produto->nome }}?');">
                                                    @csrf @method('DELETE')
                                                    <button class="btn btn-outline-danger" title="Excluir"><i class="bi bi-trash"></i></button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="text-center text-muted py-4">Nenhum produto cadastrado.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if ($produtos->hasPages())
                    <div class="card-footer bg-white">{{ $produtos->links() }}</div>
                @endif
            </div>
        </div>

        <div class="col-lg-3">
            <div class="card mb-3">
                <div class="card-header bg-white"><strong><i class="bi bi-tags"></i> Categorias</strong></div>
                <div class="card-body">
                    <form action="{{ route('admin.categorias.store', 'produto') }}" method="POST" class="row g-2 mb-3">
                        @csrf
                        <div class="col-8">
                            <input type="text" class="form-control form-control-sm" name="nome" placeholder="Nova categoria" required>
                        </div>
                        <div class="col-4">
                            <button class="btn btn-sm btn-dark w-100" type="submit"><i class="bi bi-plus-lg"></i></button>
                        </div>
                    </form>
                    <ul class="list-group">
                        @forelse ($categorias as $categoria)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span class="small">{{ $categoria->nome }}</span>
                                <form action="{{ route('admin.categorias.destroy', ['produto', $categoria->id]) }}" method="POST" onsubmit="return confirm('Remover categoria?');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" title="Excluir"><i class="bi bi-trash"></i></button>
                                </form>
                            </li>
                        @empty
                            <li class="list-group-item text-muted small">Nenhuma categoria.</li>
                        @endforelse
                    </ul>
                </div>
            </div>

            @if ($totalEstoqueBaixo > 0)
                <div class="alert alert-warning small">
                    <i class="bi bi-exclamation-triangle"></i>
                    {{ $totalEstoqueBaixo }} produto(s) com estoque abaixo do mínimo.
                </div>
            @endif
        </div>
    </div>

    <div class="modal fade" id="modalEstoque" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content" method="POST" id="formEstoque">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Ajustar estoque — <span id="estoqueNome"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-3">Saldo atual: <strong id="estoqueAtual">0</strong></p>
                    <div class="mb-3">
                        <label class="form-label" for="tipo">Tipo de movimentação</label>
                        <select class="form-select" id="tipo" name="tipo" required>
                            <option value="entrada">Entrada (compra/devolução)</option>
                            <option value="saida">Saída (perda/brinde)</option>
                            <option value="ajuste">Ajuste de inventário (define o saldo)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="quantidade">Quantidade</label>
                        <input type="number" class="form-control" id="quantidade" name="quantidade" min="0" value="1" required>
                    </div>
                    <div class="mb-0">
                        <label class="form-label" for="observacoes">Observação</label>
                        <input type="text" class="form-control" id="observacoes" name="observacoes" maxlength="180" placeholder="Ex.: contagem de estoque">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-dark">Salvar movimentação</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
const urlBase = @json(url('/'));

document.getElementById('modalEstoque').addEventListener('show.bs.modal', function (evento) {
    const botao = evento.relatedTarget;
    document.getElementById('estoqueNome').textContent = botao.dataset.nome;
    document.getElementById('estoqueAtual').textContent = botao.dataset.estoque;
    document.getElementById('formEstoque').action = urlBase + '/admin/produtos/' + botao.dataset.produto + '/estoque';
});
</script>
@endpush
