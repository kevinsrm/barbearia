@extends('layouts.app')

@section('title', 'Agendamentos - Administrativo')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Agendamentos</h2>
        <div class="d-flex gap-2">
            <a href="{{ route('agendamentos.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Novo Agendamento
            </a>
            <a href="{{ route('agendamentos.calendar') }}" class="btn btn-outline-secondary">
                <i class="bi bi-calendar3"></i> Visualizar Calendário
            </a>
        </div>
    </div>
    
    <div class="card">
        <div class="card-body">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Hora</th>
                        <th>Cliente</th>
                        <th>Profissional</th>
                        <th>Serviços</th>
                        <th>Valor</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($agendamentos as $agendamento)
                        <tr>
                            <td>{{ $agendamento->data->format('d/m/Y') }}</td>
                            <td>{{ $agendamento->hora }}</td>
                            <td>{{ $agendamento->cliente->pessoa->nome }}</td>
                            <td>{{ $agendamento->funcionario->pessoa->nome }}</td>
                            <td>
                                @foreach($agendamento->servicos as $item)
                                    <span class="badge bg-light text-dark mb-1 d-block">
                                        {{ $item->servico->nome }} (R$ {{ number_format($item->servico->valor, 2, ',', '.') }})
                                    </span>
                                @endforeach
                            </td>
                            <td>R$ {{ number_format($agendamento->valor_total, 2, ',', '.') }}</td>
                            <td>
                                <span class="badge {{ $agendamento->status == 'confirmado' ? 'bg-success' : 'bg-warning' }}">
                                    {{ ucfirst($agendamento->status) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('agendamentos.edit', $agendamento->id) }}" class="btn btn-sm btn-warning">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('agendamentos.destroy', $agendamento->id) }}" method="POST" class="d-inline">
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
            {{ $agendamentos->links() }}
        </div>
    </div>
@endsection