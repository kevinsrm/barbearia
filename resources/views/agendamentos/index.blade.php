@extends('layouts.admin')

@section('title', 'Agenda')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <h1 class="h4 mb-0"><i class="bi bi-calendar-week"></i> Agenda</h1>
        <a href="{{ route('admin.agendamentos.create') }}" class="btn btn-dark btn-sm"><i class="bi bi-plus-lg"></i> Novo agendamento</a>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form action="{{ route('admin.agendamentos.index') }}" method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small mb-1" for="data">Data</label>
                    <input type="date" class="form-control form-control-sm" id="data" name="data" value="{{ request('data') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small mb-1" for="funcionario">Profissional</label>
                    <select class="form-select form-select-sm" id="funcionario" name="funcionario">
                        <option value="">Todos</option>
                        @foreach ($funcionarios as $funcionario)
                            <option value="{{ $funcionario->id }}" {{ (string) request('funcionario') === (string) $funcionario->id ? 'selected' : '' }}>
                                {{ $funcionario->pessoa->nome ?? 'Profissional' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small mb-1" for="status">Situação</label>
                    <select class="form-select form-select-sm" id="status" name="status">
                        <option value="">Todas</option>
                        @foreach ($statusDisponiveis as $valor => $rotulo)
                            <option value="{{ $valor }}" {{ request('status') === $valor ? 'selected' : '' }}>{{ $rotulo }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button class="btn btn-sm btn-dark flex-grow-1" type="submit"><i class="bi bi-funnel"></i> Filtrar</button>
                    <a href="{{ route('admin.agendamentos.index') }}" class="btn btn-sm btn-outline-secondary">Limpar</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Data</th>
                            <th>Hora</th>
                            <th>Cliente</th>
                            <th>Profissional</th>
                            <th>Serviços</th>
                            <th class="text-end">Valor</th>
                            <th>Situação</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($agendamentos as $agendamento)
                            @php
                                $cores = ['pendente' => 'warning', 'confirmado' => 'info', 'concluido' => 'success', 'cancelado' => 'secondary', 'faltou' => 'danger', 'bloqueado' => 'dark'];
                            @endphp
                            <tr>
                                <td class="small">@data($agendamento->data)<br><span class="text-muted">@diaSemana($agendamento->data)</span></td>
                                <td class="fw-semibold">{{ substr($agendamento->hora, 0, 5) }}</td>
                                <td>
                                    {{ $agendamento->cliente_nome_exibicao }}
                                    @if ($agendamento->origem === 'site')
                                        <span class="badge bg-light text-dark border ms-1" title="Agendado pelo site">site</span>
                                    @endif
                                    @if ($agendamento->cliente_telefone)
                                        <div class="small text-muted">{{ $agendamento->cliente_telefone }}</div>
                                    @endif
                                </td>
                                <td class="small">{{ $agendamento->funcionario->pessoa->nome ?? '—' }}</td>
                                <td class="small text-muted">{{ $agendamento->servicos_texto ?: '—' }}</td>
                                <td class="text-end">@money($agendamento->valor_total)</td>
                                <td>
                                    <span class="badge bg-{{ $cores[$agendamento->status] ?? 'secondary' }} badge-status">
                                        {{ App\Models\Agendamento::STATUS[$agendamento->status] ?? $agendamento->status }}
                                    </span>
                                </td>
                                <td class="text-end text-nowrap">
                                    <div class="btn-group btn-group-sm">
                                        @if ($agendamento->status !== 'confirmado' && $agendamento->status !== 'bloqueado')
                                            <form action="{{ route('admin.agendamentos.status', $agendamento) }}" method="POST">
                                                @csrf @method('PATCH')
                                                <input type="hidden" name="status" value="confirmado">
                                                <button class="btn btn-outline-success" title="Confirmar"><i class="bi bi-check2"></i></button>
                                            </form>
                                        @endif
                                        @if (in_array($agendamento->status, ['pendente', 'confirmado']))
                                            <form action="{{ route('admin.agendamentos.status', $agendamento) }}" method="POST">
                                                @csrf @method('PATCH')
                                                <input type="hidden" name="status" value="concluido">
                                                <button class="btn btn-outline-primary" title="Marcar como concluído"><i class="bi bi-flag"></i></button>
                                            </form>
                                            @can('gerenciar-vendas')
                                                <a class="btn btn-outline-dark" title="Registrar venda deste atendimento"
                                                   href="{{ route('admin.vendas.create', ['agendamento' => $agendamento->id]) }}"><i class="bi bi-bag-plus"></i></a>
                                            @endcan
                                        @endif
                                        <a href="{{ route('admin.agendamentos.edit', $agendamento) }}" class="btn btn-outline-secondary" title="Editar"><i class="bi bi-pencil"></i></a>
                                        <form action="{{ route('admin.agendamentos.destroy', $agendamento) }}" method="POST" onsubmit="return confirm('Excluir este agendamento?');">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-outline-danger" title="Excluir"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted py-4">Nenhum agendamento encontrado para os filtros escolhidos.</td></tr>
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
