@extends('layouts.app')

@section('title', 'Pessoas - Administrativo')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Pessoas</h2>
        <a href="{{ route('pessoas.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Nova Pessoa
        </a>
    </div>
    
    <div class="card">
        <div class="card-body">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>CPF</th>
                        <th>Email</th>
                        <th>Tipo</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pessoas as $pessoa)
                        <tr>
                            <td>{{ $pessoa->nome }}</td>
                            <td>{{ $pessoa->cpf }}</td>
                            <td>{{ $pessoa->email }}</td>
                            <td>
                                @if($pessoa->tipo_pessoa == 'usuario')
                                    <span class="badge bg-primary">Usuário</span>
                                @elseif($pessoa->tipo_pessoa == 'funcionario')
                                    <span class="badge bg-success">Funcionário</span>
                                @elseif($pessoa->tipo_pessoa == 'cliente')
                                    <span class="badge bg-info">Cliente</span>
                                @else
                                    <span class="badge bg-secondary">Fornecedor</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('pessoas.edit', $pessoa->id) }}" class="btn btn-sm btn-warning">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('pessoas.destroy', $pessoa->id) }}" method="POST" class="d-inline">
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
            {{ $pessoas->links() }}
        </div>
    </div>
@endsection