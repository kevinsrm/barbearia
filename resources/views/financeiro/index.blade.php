@extends('layouts.app')

@section('title', 'Financeiro - Administrativo')

@section('content')
    <div class="row">
        <div class="col-12">
            <h2>Financeiro</h2>
            <p>Controle de vendas, compras, comissões e contas.</p>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-md-6 mb-4">
            <div class="card border-primary">
                <div class="card-body text-center">
                    <h5 class="card-title"><i class="bi bi-cart"></i> Vendas do Dia</h5>
                    <h2 class="card-text text-primary">R$ {{ number_format($vendasHoje ?? 0, 2, ',', '.') }}</h2>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-4">
            <div class="card border-success">
                <div class="card-body text-center">
                    <h5 class="card-title"><i class="bi bi-cash-stack"></i> Comissões Pendentes</h5>
                    <h2 class="card-text text-success">R$ {{ number_format($comissoesPendentes ?? 0, 2, ',', '.') }}</h2>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5>Vendas Recentes</h5>
                    <a href="{{ route('financeiro.vendas') }}" class="btn btn-sm btn-outline-primary">Ver todas</a>
                </div>
                <div class="card-body p-0">
                    @if($vendasRecentes->isEmpty())
                        <p class="text-muted p-3">Nenhuma venda recente.</p>
                    @else
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Cliente</th>
                                    <th>Valor</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($vendasRecentes as $venda)
                                    <tr>
                                        <td>{{ $venda->data->format('d/m') }}</td>
                                        <td>{{ $venda->cliente->pessoa->nome }}</td>
                                        <td>R$ {{ number_format($venda->valor_total, 2, ',', '.') }}</td>
                                        <td>
                                            <span class="badge {{ $venda->status == 'concluida' ? 'bg-success' : 'bg-warning' }}">
                                                {{ ucfirst($venda->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5>Comissões Pendentes</h5>
                    <a href="{{ route('financeiro.comissoes') }}" class="btn btn-sm btn-outline-primary">Ver todas</a>
                </div>
                <div class="card-body p-0">
                    @if($comissoesPendentesList->isEmpty())
                        <p class="text-muted p-3">Nenhuma comissão pendente.</p>
                    @else
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Profissional</th>
                                    <th>Valor</th>
                                    <th>Data</th>
                                    <th>Ação</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($comissoesPendentesList as $comissao)
                                    <tr>
                                        <td>{{ $comissao->funcionario->pessoa->nome }}</td>
                                        <td>R$ {{ number_format($comissao->valor, 2, ',', '.') }}</td>
                                        <td>{{ $comissao->data->format('d/m/Y') }}</td>
                                        <td>
                                            <form action="{{ route('financeiro.pagar-comissao', $comissao->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success">Pagar</button>
                                            </form>
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