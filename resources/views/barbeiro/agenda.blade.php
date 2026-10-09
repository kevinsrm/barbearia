@extends('layouts.app')

@section('title', 'Minha Agenda - Barbeiro')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Minha Agenda</h2>
        <a href="{{ route('barbeiro.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Voltar ao Dashboard
        </a>
    </div>
    
    <div class="card">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5>Agendamentos</h5>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-outline-primary" onclick="window.print()">
                    <i class="bi bi-printer"></i> Imprimir
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Hora</th>
                        <th>Cliente</th>
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
                            <td>
                                @foreach($agendamento->servicos as $item)
                                    <span class="badge bg-light text-dark mb-1 d-block">
                                        {{ $item->servico->nome }} (R$ {{ number_format($item->servico->valor, 2, ',', '.') }})
                                    </span>
                                @endforeach
                            </td>
                            <td>R$ {{ number_format($agendamento->valor_total, 2, ',', '.') }}</td>
                            <td>
                                <span class="badge {{ $agendamento->status == 'confirmado' ? 'bg-success' : ($agendamento->status == 'concluido' ? 'bg-primary' : 'bg-warning') }}">
                                    {{ ucfirst($agendamento->status) }}
                                </span>
                            </td>
                            <td>
                                @if($agendamento->status == 'confirmado')
                                    <button class="btn btn-sm btn-success" onclick="concluirAgendamento({{ $agendamento->id }})">
                                        <i class="bi bi-check-circle"></i> Concluir
                                    </button>
                                @endif
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
    
    @push('scripts')
        <script>
            function concluirAgendamento(id) {
                if (confirm('Marcar este agendamento como concluído?')) {
                    fetch('/api/agendamentos/' + id, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                        },
                        body: JSON.stringify({
                            status: 'concluido'
                        })
                    }).then(response => {
                        if (response.ok) {
                            alert('Agendamento concluído com sucesso!');
                            location.reload();
                        }
                    });
                }
            }
        </script>
    @endpush
@endsection