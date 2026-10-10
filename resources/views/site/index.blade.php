@extends('layouts.site')

@section('title', 'Início')

@section('content')
    <section class="hero py-5">
        <div class="container py-4">
            <div class="row align-items-center">
                <div class="col-lg-7">
                    <h1 class="display-5 fw-bold">Seu estilo, no horário que você quiser.</h1>
                    <p class="lead mt-3 mb-4 text-white-50">
                        Agende online em menos de um minuto e escolha o profissional de sua preferência.
                        Corte, barba e tratamentos com atendimento pontual.
                    </p>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('site.agendamento') }}" class="btn btn-marca btn-lg px-4">
                            <i class="bi bi-calendar-check"></i> Agendar horário
                        </a>
                        <a href="{{ route('site.servicos') }}" class="btn btn-outline-light btn-lg px-4">
                            Ver serviços e preços
                        </a>
                    </div>
                    <div class="mt-4 d-flex flex-wrap gap-4 small text-white-50">
                        <span><i class="bi bi-clock text-marca"></i> {{ config('barbearia.agenda.abertura') }} às {{ config('barbearia.agenda.fechamento') }}</span>
                        <span><i class="bi bi-telephone text-marca"></i> {{ config('barbearia.telefone') }}</span>
                        <span><i class="bi bi-geo-alt text-marca"></i> {{ config('barbearia.cidade') }}</span>
                    </div>
                </div>
                <div class="col-lg-5 d-none d-lg-block text-center">
                    <i class="bi bi-scissors" style="font-size: 12rem; color: rgba(255,255,255,.12);"></i>
                </div>
            </div>
        </div>
    </section>

    <section class="container py-5">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <div>
                <h2 class="h3 mb-1">Serviços mais pedidos</h2>
                <p class="text-muted mb-0">Valores atualizados e duração média de cada atendimento.</p>
            </div>
            <a href="{{ route('site.servicos') }}" class="btn btn-outline-secondary btn-sm">Ver todos</a>
        </div>

        @if ($destaques->isEmpty())
            <div class="alert alert-light border text-center py-5">
                <i class="bi bi-scissors fs-1 d-block mb-2 text-muted"></i>
                Ainda não há serviços cadastrados. Volte em breve!
            </div>
        @else
            <div class="row g-3">
                @foreach ($destaques as $servico)
                    <div class="col-md-6 col-lg-4">
                        <div class="card card-servico h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <h5 class="card-title mb-1">{{ $servico->nome }}</h5>
                                    <span class="badge bg-light text-dark border">{{ $servico->duracao }} min</span>
                                </div>
                                @if ($servico->categoria)
                                    <div class="small text-muted mb-2"><i class="bi bi-tag"></i> {{ $servico->categoria->nome }}</div>
                                @endif
                                <p class="card-text text-muted small">{{ $servico->descricao ?: 'Atendimento realizado por profissionais experientes.' }}</p>
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <strong class="text-marca fs-5">@money($servico->valor)</strong>
                                    <a href="{{ route('site.agendamento', ['servico' => $servico->id]) }}" class="btn btn-sm btn-marca">Agendar</a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    <section class="bg-white py-5 border-top">
        <div class="container">
            <h2 class="h3 text-center mb-4">Como funciona</h2>
            <div class="row g-4 text-center">
                <div class="col-md-4">
                    <i class="bi bi-calendar-plus fs-1 text-marca"></i>
                    <h5 class="mt-2">1. Escolha o horário</h5>
                    <p class="text-muted small mb-0">Selecione o serviço, o profissional e o horário livre que preferir.</p>
                </div>
                <div class="col-md-4">
                    <i class="bi bi-person-check fs-1 text-marca"></i>
                    <h5 class="mt-2">2. Confirmação rápida</h5>
                    <p class="text-muted small mb-0">A barbearia confirma seu agendamento e você recebe o lembrete.</p>
                </div>
                <div class="col-md-4">
                    <i class="bi bi-star fs-1 text-marca"></i>
                    <h5 class="mt-2">3. Atendimento pontual</h5>
                    <p class="text-muted small mb-0">Chegou, foi atendido. Sem fila e sem espera desnecessária.</p>
                </div>
            </div>
        </div>
    </section>
@endsection
