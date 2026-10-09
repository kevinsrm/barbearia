@extends('layouts.app')

@section('title', 'Comissões - Financeiro')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Comissões Pendentes</h2>
        <div class="d-flex gap-2">
            <a href="{{ route('financeiro.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Voltar ao Dashboard
            </a>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <h5>Total Pendente</h5>
                <span class="badge bg-success">R$ {{ number_format($totalComissoes, 2, ',', '.') }}</span>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-body">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Profissional</th>
                        <th>Venda</th>
                        <th>Valor</th>
                        <th>Data</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($comissoes as $comissao)
                        <tr>
                            <td>{{ $comissao->funcionario->pessoa->nome }}</td>
                            <td>
                                @if($comissao->venda)
                                    <a href="{{ route('agendamentos.edit', $comissao->venda->id) }}" class="text-decoration-none">
                                        Agendamento #{{ $comissao->venda->id }}
                                    </a>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>R$ {{ number_format($comissao->valor, 2, ',', '.') }}</td>
                            <td>{{ $comissao->data->format('d/m/Y') }}</td>
                            <td>
                                <span class="badge bg-warning">
                                    Pendente
                                </span>
                            </td>
                            <td>
                                <form action="{{ route('financeiro.pagar-comissao', $comissao->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success">Pagar</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $comissoes->links() }}
        </div>
    </div>
@endsection