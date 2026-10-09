@extends('layouts.admin')

@section('title', 'Novo produto')

@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('admin.produtos.index') }}">Produtos</a></li>
            <li class="breadcrumb-item active">Novo</li>
        </ol>
    </nav>

    <div class="card">
        <div class="card-header bg-white"><strong><i class="bi bi-box"></i> Novo produto</strong></div>
        <div class="card-body">
            <form action="{{ route('admin.produtos.store') }}" method="POST">
                @csrf
                @include('produtos._form')
            </form>
        </div>
    </div>
@endsection
