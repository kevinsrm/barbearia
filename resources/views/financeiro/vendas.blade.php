@extends('layouts.app')

@section('title', 'Vendas - Financeiro')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Vendas</h2>
        <div class="d-flex gap-2">
            <a href="{{ route('financeiro.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Voltar ao Dashboard
            </a>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <h5>Total de Vendas</h5>
                <span class="badge bg-success">R$ {{ number_format($totalVendas, 2, ',', '.') }}</span>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-body">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Cliente</th>
                        <th>Profissional</th>
                        <th>Valor Total</th>
                        <th>Forma de Pagamento</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($vendas as $venda)
                        <tr>
                            <td>{{ $venda->data->format('d/m/Y') }}</td>
                            <td>{{ $venda->cliente->pessoa->nome }}</td>
                            <td>{{ $venda->funcionario->pessoa->nome }}</td>
                            <td>R$ {{ number_format($venda->valor_total, 2, ',', '.') }}</td>
                            <td>
                                @if($venda->forma_pagamento == 'dinheiro')
                                    <span class="badge bg-secondary">Dinheiro</span>
                                @elseif($venda->forma_pagamento == 'cartao')
                                    <span class="badge bg-primary">Cartão</span>
                                @else
                                    <span class="badge bg-light">Outro</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $venda->status == 'concluida' ? 'bg-success' : 'bg-warning' }}">
                                    {{ ucfirst($venda->status) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('agendamentos.edit', $venda->id) }}" class="btn btn-sm btn-warning">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $vendas->links() }}
        </div>
    </div>
@endsection