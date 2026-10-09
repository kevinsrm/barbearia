@extends('layouts.app')

@section('title', 'Dashboard - Administrativo')

@section('content')
    <div class="row">
        <div class="col-12">
            <h2>Dashboard</h2>
            <p>Bem-vindo ao painel administrativo da Barbearia!</p>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-md-3 mb-4">
            <div class="card border-primary">
                <div class="card-body text-center">
                    <h5 class="card-title"><i class="bi bi-calendar-event"></i> Agendamentos Hoje</h5>
                    <h2 class="card-text text-primary">{{ $agendamentosHoje }}</h2>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-4">
            <div class="card border-success">
                <div class="card-body text-center">
                    <h5 class="card-title"><i class="bi bi-calendar-check"></i> Agendamentos Futuros</h5>
                    <h2 class="card-text text-success">{{ $agendamentosFuturos }}</h2>
                </div>
            </div>
        </n        
        <div class="col-md-3 mb-4">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <h5 class="card-title"><i class="bi bi-currency-dollar"></i> Vendas Hoje</h5>
                    <h2 class="card-text text-warning">R$ {{ number_format($vendasHoje, 2, ',', '.') }}</h2>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-4">
            <div class="card border-danger">
                <div class="card-body text-center">
                    <h5 class="card-title"><i class="bi bi-exclamation-triangle"></i> Estoque Baixo</h5>
                    <h2 class="card-text text-danger">{{ $produtosEstoqueBaixo }}</h2>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h5>Próximos Agendamentos</h5>
                </div>
                <div class="card-body">
                    @php
                        $proximos = Agendamento::with('cliente', 'funcionario')
                            ->where('data', '>=', today())
                            ->orderBy('data')
                            ->limit(5)
                            ->get();
                    @endphp
                    @if($proximos->isEmpty())
                        <p class="text-muted">Nenhum agendamento futuro encontrado.</p>
                    @else
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Cliente</th>
                                    <th>Profissional</th>
                                    <th>Serviço</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($proximos as $ag)
                                    <tr>
                                        <td>{{ $ag->data->format('d/m') }} - {{ $ag->hora }}</td>
                                        <td>{{ $ag->cliente->pessoa->nome }}</td>
                                        <td>{{ $ag->funcionario->pessoa->nome }}</td>
                                        <td>{{ $ag->servicos->first()->servico->nome ?? 'N/A' }}</td>
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
                <div class="card-header bg-light">
                    <h5>Comissões Pendentes</h5>
                </div>
                <div class="card-body">
                    @php
                        $comissoes = Comissao::with('funcionario')
                            ->where('status', 'pendente')
                            ->limit(5)
                            ->get();
                    @endphp
                    @if($comissoes->isEmpty())
                        <p class="text-muted">Nenhuma comissão pendente.</p>
                    @else
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Profissional</th>
                                    <th>Valor</th>
                                    <th>Data</th>
                                </tr>
        
@endsection