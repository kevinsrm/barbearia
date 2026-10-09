@extends('layouts.admin')

@section('title', 'Editar cadastro')

@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('admin.pessoas.index') }}">Pessoas</a></li>
            <li class="breadcrumb-item active">{{ $pessoa->nome }}</li>
        </ol>
    </nav>

    <div class="card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <strong><i class="bi bi-person-gear"></i> {{ $pessoa->nome }}</strong>
            <span class="badge bg-light text-dark border">{{ $pessoa->tipo_label }}</span>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.pessoas.update', $pessoa) }}" method="POST">
                @csrf
                @method('PUT')
                @include('pessoas._form')
            </form>
        </div>
    </div>
@endsection
