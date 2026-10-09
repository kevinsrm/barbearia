@extends('layouts.admin')

@section('title', 'Meus agendamentos')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <h1 class="h4 mb-0"><i class="bi bi-calendar-week"></i> Meus agendamentos</h1>
        <a href="{{ route('admin.barbeiro.index') }}" class="btn btn-outline-dark btn-sm"><i class="bi bi-calendar-day"></i> Ver hoje</a>
    </div>

    <div class="card mb-3">
        <div class="card-body py-2">
            <form action="{{ route('admin.barbeiro.agenda') }}" method="GET" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small mb-1" for="data">Filtrar por data</label>
                    <input type="date" class="form-control form-control-sm" id="data" name="data" value="{{ request('data') }}">
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button class="btn btn-sm btn-dark" type="submit"><i class="bi bi-funnel"></i> Filtrar</button>
                    <a href="{{ route('admin.barbeiro.agenda') }}" class="btn btn-sm btn-outline-secondary">Todos</a>
                </div>
            </form>
        </div>
    </div>

    @php $cores = ['pendente' => 'warning', 'confirmado' => 'info', 'concluido' => 'success', 'cancelado' => 'secondary', 'faltou' => 'danger', 'bloqueado' => 'dark']; @endphp

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Data</th><th>Hora</th><th>Cliente</th><th>Serviços</th><th class="text-end">Valor</th><th>Situação</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($agendamentos as $agendamento)
                            <tr>
                                <td class="small">@data($agendamento->data)<div class="text-muted">@diaSemana($agendamento->data)</div></td>
                                <td class="fw-semibold">{{ substr($agendamento->hora, 0, 5) }}</td>
                                <td>
                                    {{ $agendamento->cliente_nome_exibicao }}
                                    <div class="small text-muted">{{ $agendamento->cliente_telefone }}</div>
                                </td>
                                <td class="small text-muted">{{ $agendamento->servicos_texto ?: '—' }}</td>
                                <td class="text-end">@money($agendamento->valor_total)</td>
                                <td>
                                    <span class="badge bg-{{ $cores[$agendamento->status] ?? 'secondary' }} badge-status">
                                        {{ App\Models\Agendamento::STATUS[$agendamento->status] ?? $agendamento->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">Nenhum agendamento encontrado.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($agendamentos->hasPages())
            <div class="card-footer bg-white">{{ $agendamentos->links() }}</div>
        @endif
    </div>
@endsection
