@extends('layouts.admin')

@section('title', 'Novo agendamento')

@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('admin.agendamentos.index') }}">Agenda</a></li>
            <li class="breadcrumb-item active">Novo agendamento</li>
        </ol>
    </nav>

    <div class="card">
        <div class="card-header bg-white"><strong><i class="bi bi-calendar-plus"></i> Novo agendamento</strong></div>
        <div class="card-body">
            <form action="{{ route('admin.agendamentos.store') }}" method="POST">
                @csrf
                @include('agendamentos._form')
            </form>
        </div>
    </div>
@endsection
