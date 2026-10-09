@extends('layouts.admin')

@section('title', 'Minha agenda')

@section('content')
    @php
        $cores = ['pendente' => 'warning', 'confirmado' => 'info', 'concluido' => 'success', 'cancelado' => 'secondary', 'faltou' => 'danger', 'bloqueado' => 'dark'];
        $bloqueados = $agendamentos->where('status', 'bloqueado')->pluck('hora')->map(fn ($h) => substr((string) $h, 0, 5))->all();
        $ocupados = $agendamentos->where('status', '!=', 'bloqueado')->pluck('hora')->map(fn ($h) => substr((string) $h, 0, 5))->all();
        $slots = collect($horariosLivres)->merge($bloqueados)->unique()->sort()->values();
    @endphp

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h1 class="h4 mb-0"><i class="bi bi-person-badge"></i> Olá, {{ $funcionario->nome }}</h1>
            <div class="text-muted small">Sua agenda de @diaSemana($data), @data($data)</div>
        </div>
        <div class="btn-group btn-group-sm">
            <a class="btn btn-outline-secondary" href="{{ route('admin.barbeiro.index', ['data' => $data->copy()->subDay()->format('Y-m-d')]) }}"><i class="bi bi-chevron-left"></i></a>
            <a class="btn btn-outline-secondary" href="{{ route('admin.barbeiro.index') }}">Hoje</a>
            <a class="btn btn-outline-secondary" href="{{ route('admin.barbeiro.index', ['data' => $data->copy()->addDay()->format('Y-m-d')]) }}"><i class="bi bi-chevron-right"></i></a>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-sm-6 col-lg-3">
            <div class="card card-metrica h-100"><div class="card-body">
                <div class="text-muted small">Atendimentos concluídos no mês</div>
                <div class="valor">{{ $atendimentosMes }}</div>
            </div></div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-metrica h-100"><div class="card-body">
                <div class="text-muted small">Comissão do mês</div>
                <div class="valor text-success">@money($totalMes)</div>
            </div></div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-metrica h-100"><div class="card-body">
                <div class="text-muted small">A receber (pendente)</div>
                <div class="valor text-warning">@money($pendente)</div>
            </div></div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-metrica h-100"><div class="card-body">
                <div class="text-muted small">Horários livres hoje</div>
                <div class="valor">{{ count($horariosLivres) }}</div>
            </div></div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <strong>Compromissos do dia</strong>
                    <span class="badge bg-light text-dark border">{{ $agendamentos->where('status', '!=', 'bloqueado')->count() }}</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light"><tr><th>Hora</th><th>Cliente</th><th>Serviços</th><th class="text-end">Valor</th><th>Situação</th></tr></thead>
                            <tbody>
                                @forelse ($agendamentos->where('status', '!=', 'bloqueado') as $agendamento)
                                    <tr>
                                        <td class="fw-semibold">{{ substr($agendamento->hora, 0, 5) }}<div class="small text-muted">até {{ $agendamento->hora_fim }}</div></td>
                                        <td>
                                            {{ $agendamento->cliente_nome_exibicao }}
                                            <div class="small text-muted">{{ $agendamento->cliente_telefone }}</div>
                                        </td>
                                        <td class="small">{{ $agendamento->servicos_texto ?: '—' }}</td>
                                        <td class="text-end">@money($agendamento->valor_total)</td>
                                        <td><span class="badge bg-{{ $cores[$agendamento->status] ?? 'secondary' }} badge-status">{{ App\Models\Agendamento::STATUS[$agendamento->status] ?? $agendamento->status }}</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted py-4">
                                        @if (empty($horariosLivres))
                                            {{ $data->isToday() ? 'A barbearia está fechada hoje ou não há horários configurados.' : 'Nenhum compromisso nesta data.' }}
                                        @else
                                            Nenhum compromisso marcado para este dia. Aproveite para divulgar seus horários!
                                        @endif
                                    </td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white small text-muted">
                    A recepção é quem confirma e conclui os atendimentos. Aqui você acompanha o seu dia.
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header bg-white"><strong>Bloquear / liberar horários</strong></div>
                <div class="card-body">
                    <p class="small text-muted">
                        Marque os horários em que você <strong>não</strong> pode atender (almoço, compromisso pessoal, folga).
                        Eles somem da agenda e do site. Ao salvar, a lista deste dia é substituída pelo que estiver marcado.
                    </p>

                    @if ($slots->isEmpty())
                        <div class="alert alert-light border small mb-0">
                            Nenhum horário configurado para @data($data). Verifique o horário de funcionamento em
                            @if (auth()->user()->isAdmin())
                                <a href="{{ route('admin.configuracoes') }}">Configurações</a>.
                            @else
                                Configurações (peça ao administrador).
                            @endif
                        </div>
                    @else
                        <form action="{{ route('admin.barbeiro.disponibilidade') }}" method="POST">
                            @csrf
                            <input type="hidden" name="data" value="{{ $data->format('Y-m-d') }}">

                            <div class="d-flex flex-wrap gap-2 mb-3">
                                @foreach ($slots as $hora)
                                    <div class="form-check border rounded px-2 py-1 mb-0 {{ in_array($hora, $bloqueados, true) ? 'bg-dark text-white border-dark' : 'bg-light' }}">
                                        <input class="form-check-input" type="checkbox" name="horarios[]" value="{{ $hora }}"
                                               id="h-{{ str_replace(':', '', $hora) }}" {{ in_array($hora, $bloqueados, true) ? 'checked' : '' }}>
                                        <label class="form-check-label small" for="h-{{ str_replace(':', '', $hora) }}">{{ $hora }}</label>
                                    </div>
                                @endforeach
                            </div>

                            @if (! empty($ocupados))
                                <div class="small text-muted mb-3">
                                    <i class="bi bi-info-circle"></i> Horários já ocupados por clientes: {{ implode(', ', $ocupados) }}.
                                </div>
                            @endif

                            <div class="d-flex gap-2">
                                <button class="btn btn-dark btn-sm flex-grow-1" type="submit"><i class="bi bi-check2"></i> Salvar disponibilidade</button>
                                <button class="btn btn-outline-secondary btn-sm" type="submit" name="limpar" value="1"
                                        onclick="this.form.querySelectorAll('input[name=\'horarios[]\']').forEach(function (c) { c.checked = false; });">
                                    <i class="bi bi-eraser"></i> Liberar tudo
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
