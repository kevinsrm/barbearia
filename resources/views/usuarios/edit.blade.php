@extends('layouts.admin')

@section('title', 'Editar usuário')

@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('admin.usuarios.index') }}">Usuários</a></li>
            <li class="breadcrumb-item active">{{ $usuario->nome }}</li>
        </ol>
    </nav>

    <div class="card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <strong><i class="bi bi-person-gear"></i> {{ $usuario->nome }}</strong>
            <span class="text-muted small">Último acesso: @dataHora($usuario->ultimo_acesso)</span>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.usuarios.update', $usuario) }}" method="POST">
                @csrf
                @method('PUT')
                @include('usuarios._form')
            </form>
        </div>
    </div>
@endsection
