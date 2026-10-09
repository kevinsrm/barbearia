@extends('layouts.app')

@section('title', 'Contas - Financeiro')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Contas a Pagar e Receber</h2>
        <div class="d-flex gap-2">
            <a href="{{ route('financeiro.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Voltar ao Dashboard
            </a>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card border-danger">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5>Contas a Pagar</h5>
                    <span class="badge bg-danger">{{ $contasPagar->total() }} pendentes</span>
                </div>
                <div class="card-body p-0">
                    @if($contasPagar->isEmpty())
                        <p class="text-muted p-3">Nenhuma conta a pagar pendente.</p>
                    @else
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Descrição</th>
                                    <th>Fornecedor</th>
                                    <th>Valor</th>
                                    <th>Vencimento</th>
                                    <th>Ação</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($contasPagar as $conta)
                                    <tr>
                                        <td>{{ $conta->descricao }}</td>
                                        <td>{{ $conta->fornecedor->pessoa->nome }}</td>
                                        <td>R$ {{ number_format($conta->valor, 2, ',', '.') }}</td>
                                        <td>{{ $conta->vencimento->format('d/m/Y') }}</td>
                                        <td>
                                            <span class="badge bg-warning">Pendente</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-4">
            <div class="card border-success">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5>Contas a Receber</h5>
                    <span class="badge bg-success">{{ $contasReceber->total() }} pendentes</span>
                </div>
                <div class="card-body p-0">
                    @if($contasReceber->isEmpty())
                        <p class="text-muted p-3">Nenhuma conta a receber pendente.</p>
                    @else
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Descrição</th>
                                    <th>Cliente</th>
                                    <th>Valor</th>
                                    <th>Vencimento</th>
                                    <th>Ação</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($contasReceber as $conta)
                                    <tr>
                                        <td>{{ $conta->descricao }}</td>
                                        <td>{{ $conta->cliente->pessoa->nome }}</td>
                                        <td>R$ {{ number_format($conta->valor, 2, ',', '.') }}</td>
                                        <td>{{ $conta->vencimento->format('d/m/Y') }}</td>
                                        <td>
                                            <span class="badge bg-warning">Pendente</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection