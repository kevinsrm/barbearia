@extends('layouts.admin')

@section('title', 'Editar agendamento')

@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('admin.agendamentos.index') }}">Agenda</a></li>
            <li class="breadcrumb-item active">Editar</li>
        </ol>
    </nav>

    <div class="card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <strong><i class="bi bi-calendar-week"></i> Agendamento de {{ $agendamento->cliente_nome_exibicao }}</strong>
            <span class="text-muted small">Criado em @dataHora($agendamento->created_at)</span>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.agendamentos.update', $agendamento) }}" method="POST">
                @csrf
                @method('PUT')
                @include('agendamentos._form')
            </form>
        </div>
    </div>
@endsection
