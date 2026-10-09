@extends('layouts.app')

@section('title', 'Relatório de Produtos - Relatórios')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Relatório de Produtos</h2>
        <div class="d-flex gap-2">
            <a href="{{ route('relatorios.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <h5>Valor Total em Estoque</h5>
                <span class="badge bg-success">R$ {{ number_format($totalEstoqueValor, 2, ',', '.') }}</span>
            </div>
        </div>
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
                        <th>Valor Unitário</th>
                        <th>Valor Total</th>
                        <th>Status</th>
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
                            <td>R$ {{ number_format(($produto->preco_venda * ($produto->estoque ?? 0)), 2, ',', '.') }}</td>
                            <td>
                                <span class="badge {{ $produto->ativo ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $produto->ativo ? 'Ativo' : 'Inativo' }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $produtos->links() }}
        </div>
    </div>
@endsection