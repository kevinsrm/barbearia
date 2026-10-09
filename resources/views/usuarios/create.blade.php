@extends('layouts.admin')

@section('title', 'Novo usuário')

@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('admin.usuarios.index') }}">Usuários</a></li>
            <li class="breadcrumb-item active">Novo</li>
        </ol>
    </nav>

    <div class="card">
        <div class="card-header bg-white"><strong><i class="bi bi-shield-lock"></i> Novo usuário</strong></div>
        <div class="card-body">
            <form action="{{ route('admin.usuarios.store') }}" method="POST">
                @csrf
                @include('usuarios._form')
            </form>
        </div>
    </div>
@endsection
