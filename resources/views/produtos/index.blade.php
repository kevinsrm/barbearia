@extends('layouts.app')

@section('title', 'Produtos - Administrativo')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Produtos</h2>
        <a href="{{ route('produtos.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Novo Produto
        </a>
    </div>
    
    <div class="card">
        <div class="card-body">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nome</th>
                        <th>Categoria</th>
                        <th>Estoque</th>
                        <th>Valor</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($produtos as $produto)
                        <tr>
                            <td>{{ $produto->codigo }}</td>
                            <td>{{ $produto->nome }}</td>
                            <td>{{ $produto->categoria->nome ?? 'N/A' }}</td>
                            <td>
                                @php
                                    $estoque = $produto->estoque ?? 0;
                                    $minimo = $produto->estoque_minimo;
                                    if ($estoque <= $minimo) {
                                        echo '<span class="badge bg-danger">' . $estoque . ' (Crítico)</span>';
                                    } else {
                                        echo '<span class="badge bg-success">' . $estoque . '</span>';
                                    }
                                @endphp
                            </td>
                            <td>R$ {{ number_format($produto->preco_venda, 2, ',', '.') }}</td>
                            <td>
                                <a href="{{ route('produtos.edit', $produto->id) }}" class="btn btn-sm btn-warning">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('produtos.destroy', $produto->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza?');">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $produtos->links() }}
        </div>
    </div>
    
    <div class="mt-4">
        <h4>Categorias</h4>
        <div class="d-flex flex-wrap gap-2">
            @foreach($categorias as $categoria)
                <span class="badge bg-secondary">{{ $categoria->nome }}</span>
            @endforeach
        </div>
    </div>
@endsection