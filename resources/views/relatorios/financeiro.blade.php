@extends('layouts.app')

@section('title', 'Relatório Financeiro - Relatórios')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Demonstrativo de Lucro (DRE)</h2>
        <div class="d-flex gap-2">
            <a href="{{ route('relatorios.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>
    </div>
    
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-primary text-center">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-cart"></i> Total Vendas</h5>
                    <h2 class="card-text text-primary">R$ {{ number_format($totalVendas, 2, ',', '.') }}</h2>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card border-warning text-center">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-box-seam"></i> Total Compras</h5>
                    <h2 class="card-text text-warning">R$ {{ number_format($totalCompras, 2, ',', '.') }}</h2>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card border-info text-center">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-coin"></i> Comissões Pagas</h5>
                    <h2 class="card-text text-info">R$ {{ number_format($totalComissoes, 2, ',', '.') }}</h2>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-body text-center">
            <h4>Demonstrativo de Resultado</h4>
            <div class="mt-3">
                @php
                    $lucro = $lucro ?? 0;
                    $class = $lucro > 0 ? 'text-success' : 'text-danger';
                    $icon = $lucro > 0 ? 'bi bi-emoji-smile' : 'bi bi-emoji-frown';
                @endphp
                <h2 class="{{ $class }} mb-3">
                    <i class="{{ $icon }} fs-1"></i><br>
                    R$ {{ number_format(abs($lucro), 2, ',', '.') }}<br>
                    @if($lucro > 0)
                        <small>Lucro</small>
                    @else
                        <small>Prejuízo</small>
                    @endif
                </h2>
            </div>
        </div>
    </div>
@endsection