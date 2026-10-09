@extends('layouts.app')

@section('title', 'Serviços - Administrativo')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Serviços</h2>
        <a href="{{ route('servicos.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Novo Serviço
        </a>
    </div>
    
    <div class="card">
        <div class="card-body">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Categoria</th>
                        <th>Duração</th>
                        <th>Valor</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($servicos as $servico)
                        <tr>
                            <td>{{ $servico->nome }}</td>
                            <td>{{ $servico->categoria->nome ?? 'N/A' }}</td>
                            <td>{{ $servico->duracao }} min</td>
                            <td>R$ {{ number_format($servico->valor, 2, ',', '.') }}</td>
                            <td>
                                <a href="{{ route('servicos.edit', $servico->id) }}" class="btn btn-sm btn-warning">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('servicos.destroy', $servico->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza?');">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $servicos->links() }}
        </div>
    </div>
    
    <div class="mt-4">
        <h4>Categorias</h4>
        <div class="d-flex flex-wrap gap-2">
            @foreach($categorias as $categoria)
                <span class="badge bg-secondary">{{ $categoria->nome }}</span>
            @endforeach
        </div>
    </div>
@endsection