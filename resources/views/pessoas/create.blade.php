@extends('layouts.admin')

@section('title', 'Novo cadastro')

@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('admin.pessoas.index') }}">Pessoas</a></li>
            <li class="breadcrumb-item active">Novo cadastro</li>
        </ol>
    </nav>

    <div class="card">
        <div class="card-header bg-white"><strong><i class="bi bi-person-plus"></i> Novo cadastro</strong></div>
        <div class="card-body">
            <form action="{{ route('admin.pessoas.store') }}" method="POST">
                @csrf
                @include('pessoas._form')
            </form>
        </div>
    </div>
@endsection
