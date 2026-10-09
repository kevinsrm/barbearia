@extends('layouts.app')

@section('title', 'Relatórios - Administrativo')

@section('content')
    <div class="row">
        <div class="col-12">
            <h2>Relatórios</h2>
            <p>Gerencie relatórios e análises do seu negócio.</p>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-md-4 mb-4">
            <div class="card border-primary text-center">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-box-seam"></i> Produtos</h5>
                    <p class="card-text">Relatório de estoque, categorias e valores</p>
                    <a href="{{ route('relatorios.produtos') }}" class="btn btn-outline-primary mt-2">Acessar</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card border-success text-center">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-currency-dollar"></i> Financeiro</h5>
                    <p class="card-text">DRE simplificado, lucro e movimentações</p>
                    <a href="{{ route('relatorios.financeiro') }}" class="btn btn-outline-success mt-2">Acessar</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card border-info text-center">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-people"></i> Comissões</h5>
                    <p class="card-text">Comissões por barbeiro e histórico</p>
                    <a href="{{ route('relatorios.comissoes') }}" class="btn btn-outline-info mt-2">Acessar</a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h5>Contas a Pagar/Receber</h5>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Tipo</th>
                                <th>Quantidade</th>
                                <th>Total</th>
                                <th>Ver</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span class="badge bg-danger">Pagar</span></td>
                                <td>{{ $contasPagarCount ?? 0 }}</td>
                                <td>R$ {{ number_format($contasPagarTotal ?? 0, 2, ',', '.') }}</td>
                                <td><a href="{{ route('relatorios.contas') }}" class="btn btn-sm btn-outline-primary">Ver</a></td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-success">Receber</span></td>
                                <td>{{ $contasReceberCount ?? 0 }}</td>
                                <td>R$ {{ number_format($contasReceberTotal ?? 0, 2, ',', '.') }}</td>
                                <td><a href="{{ route('relatorios.contas') }}" class="btn btn-sm btn-outline-primary">Ver</a></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h5>Aniversariantes do Mês</h5>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Data Nasc.</th>
                                <th>Ver</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($aniversariantes->isEmpty())
                                <tr><td colspan="3" class="text-muted">Nenhum aniversariante este mês.</td></tr>
                            @else
                                @foreach($aniversariantes as $aniv)
                                    <tr>
                                        <td>{{ $aniv->nome }}</td>
                                        <td>{{ $aniv->data_nascimento->format('d/m') }}</td>
                                        <td>
                                            <a href="{{ route('pessoas.edit', $aniv->id) }}" class="btn btn-sm btn-outline-primary">Ver</a>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection