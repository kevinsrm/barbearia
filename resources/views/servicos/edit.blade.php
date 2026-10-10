@extends('layouts.admin')

@section('title', 'Editar serviço')

@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('admin.servicos.index') }}">Serviços</a></li>
            <li class="breadcrumb-item active">{{ $servico->nome }}</li>
        </ol>
    </nav>

    <div class="card">
        <div class="card-header bg-white"><strong><i class="bi bi-scissors"></i> {{ $servico->nome }}</strong></div>
        <div class="card-body">
            <form action="{{ route('admin.servicos.update', $servico) }}" method="POST">
                @csrf
                @method('PUT')
                @include('servicos._form')
            </form>
        </div>
    </div>
@endsection
