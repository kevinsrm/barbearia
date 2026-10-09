@extends('layouts.app')

@section('title', 'Serviços - Barbearia')

@section('content')
    <div class="container mt-5">
        <h2 class="text-center mb-5">Nossos Serviços</h2>
        
        <div class="row">
            @foreach($servicos as $servico)
                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="bi bi-scissors fs-1 text-primary"></i>
                            </div>
                            <h5 class="card-title">{{ $servico->nome }}</h5>
                            <p class="card-text text-muted">{{ $servico->descricao }}</p>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <span class="text-primary fw-bold">R$ {{ number_format($servico->valor, 2, ',', '.') }}</span>
                                <span class="text-muted"><i class="bi bi-clock"></i> {{ $servico->duracao }} min</span>
                            </div>
                            <div class="mt-3">
                                <a href="/agendamento" class="btn btn-primary btn-sm w-100">Agendar Este Serviço</a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="container mt-5 bg-light py-5">
        <div class="row">
            <div class="col-md-4 text-center">
                <i class="bi bi-clock fs-2 text-primary mb-3"></i>
                <h4>Atendimento</h4>
                <p>Seg-Sex: 09:00 - 20:00</p>
                <p>Sábados: 09:00 - 18:00</p>
            </div>
            <div class="col-md-4 text-center">
                <i class="bi bi-award fs-2 text-primary mb-3"></i>
                <h4>Profissionais</h4>
                <p>Equipe qualificada e experiente</p>
            </div>
            <div class="col-md-4 text-center">
                <i class="bi bi-telephone fs-2 text-primary mb-3"></i>
                <h4>Contato</h4>
                <p>(11) 1234-5678</p>
                <p>contato@barbearia.com</p>
            </div>
        </div>
    </div>
@endsection