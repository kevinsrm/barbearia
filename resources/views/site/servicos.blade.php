@extends('layouts.site')

@section('title', 'Serviços e preços')

@section('content')
    <div class="container py-5">
        <h1 class="h3 mb-1">Serviços e preços</h1>
        <p class="text-muted">Todos os serviços podem ser agendados online.</p>

        @forelse ($categorias as $nome => $servicos)
            <h2 class="h5 mt-4 mb-3"><i class="bi bi-tag text-marca"></i> {{ $nome }}</h2>
            <div class="row g-3">
                @foreach ($servicos as $servico)
                    <div class="col-md-6 col-lg-4">
                        <div class="card card-servico h-100">
                            <div class="card-body d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-start">
                                    <h5 class="h6 mb-1">{{ $servico->nome }}</h5>
                                    <span class="badge bg-light text-dark border">{{ $servico->duracao }} min</span>
                                </div>
                                <p class="text-muted small flex-grow-1">{{ $servico->descricao }}</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <strong class="text-marca">@money($servico->valor)</strong>
                                    <a href="{{ route('site.agendamento', ['servico' => $servico->id]) }}" class="btn btn-sm btn-outline-dark">Agendar</a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @empty
            <div class="alert alert-light border text-center py-5">
                <i class="bi bi-scissors fs-1 d-block mb-2 text-muted"></i>
                Nenhum serviço publicado ainda.
            </div>
        @endforelse
    </div>
@endsection
