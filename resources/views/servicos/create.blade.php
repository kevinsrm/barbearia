@extends('layouts.admin')

@section('title', 'Novo serviço')

@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('admin.servicos.index') }}">Serviços</a></li>
            <li class="breadcrumb-item active">Novo</li>
        </ol>
    </nav>

    <div class="card">
        <div class="card-header bg-white"><strong><i class="bi bi-scissors"></i> Novo serviço</strong></div>
        <div class="card-body">
            <form action="{{ route('admin.servicos.store') }}" method="POST">
                @csrf
                @include('servicos._form')
            </form>
        </div>
    </div>
@endsection
