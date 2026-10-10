@extends('layouts.site')

@section('title', 'Sobre')

@section('content')
    <div class="container py-5">
        <div class="row g-4">
            <div class="col-lg-7">
                <h1 class="h3 mb-3">Sobre a {{ config('barbearia.nome') }}</h1>
                <p>
                    Somos uma barbearia dedicada ao cuidado masculino: corte, barba e tratamentos
                    com atendimento pontual, ambiente confortável e profissionais em constante atualização.
                </p>
                <p>
                    Nosso agendamento é online para que você não perca tempo na fila: escolha o profissional,
                    o serviço e o horário. O resto é com a gente.
                </p>

                <div class="row g-3 mt-2">
                    <div class="col-sm-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <h2 class="h6"><i class="bi bi-clock text-marca"></i> Horário de atendimento</h2>
                                <p class="mb-0 small text-muted">
                                    Todos os dias, das {{ config('barbearia.agenda.abertura') }} às {{ config('barbearia.agenda.fechamento') }}.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <h2 class="h6"><i class="bi bi-geo-alt text-marca"></i> Onde estamos</h2>
                                <p class="mb-0 small text-muted">
                                    {{ config('barbearia.endereco') }}<br>{{ config('barbearia.cidade') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card">
                    <div class="card-body">
                        <h2 class="h5 mb-3">Fale com a gente</h2>
                        <p class="mb-2"><i class="bi bi-telephone text-marca"></i> {{ config('barbearia.telefone') }}</p>
                        <p class="mb-2"><i class="bi bi-envelope text-marca"></i> {{ config('barbearia.email') }}</p>
                        @if (config('barbearia.whatsapp'))
                            <p class="mb-2"><i class="bi bi-whatsapp text-marca"></i> {{ config('barbearia.whatsapp') }}</p>
                        @endif
                        @if (config('barbearia.instagram'))
                            <p class="mb-2"><i class="bi bi-instagram text-marca"></i> {{ config('barbearia.instagram') }}</p>
                        @endif
                        <hr>
                        <a href="{{ route('site.agendamento') }}" class="btn btn-marca w-100">
                            <i class="bi bi-calendar-check"></i> Agendar meu horário
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
