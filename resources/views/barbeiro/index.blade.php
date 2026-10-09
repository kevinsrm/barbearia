@extends('layouts.app')

@section('title', 'Minha Conta - Barbeiro')

@section('content')
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title">{{ $funcionario->pessoa->nome }}</h5>
                    <p class="text-muted">Código: {{ $funcionario->codigo_funcional }}</p>
                    <p><strong>Cargo:</strong> {{ $funcionario->cargo->nome ?? 'N/A' }}</p>
                    <p><strong>Admissão:</strong> {{ $funcionario->data_admissao->format('d/m/Y') ?? 'N/A' }}</p>
                    <p><strong>Salário Base:</strong> R$ {{ number_format($funcionario->salario_base, 2, ',', '.') }}</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5>Próximos Atendimentos</h5>
                    <a href="{{ route('barbeiro.agenda') }}" class="btn btn-sm btn-outline-primary">Ver Agenda Completa</a>
                </div>
                <div class="card-body p-0">
                    @php
                        $agendamentos = Agendamento::where('funcionario_id', $funcionario->id)
                            ->whereDate('data', '>=', today())
                            ->orderBy('data')
                            ->orderBy('hora')
                            ->limit(5)
                            ->get();
                    @endphp
                    @if($agendamentos->isEmpty())
                        <p class="text-muted p-3">Nenhum agendamento futuro.</p>
                    @else
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Hora</th>
                                    <th>Cliente</th>
                                    <th>Serviço</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($agendamentos as $ag)
                                    <tr>
                                        <td>{{ $ag->data->format('d/m') }} - {{ $ag->hora }}</td>
                                        <td>{{ $ag->cliente->pessoa->nome }}</td>
                                        <td>{{ $ag->servicos->first()->servico->nome ?? 'N/A' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h5>Configurar Horários</h5>
                </div>
                <div class="card-body">
                    <p>Defina seus horários de trabalho para cada dia da semana.</p>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#horariosModal">
                        <i class="bi bi-calendar3"></i> Configurar Disponibilidade
                    </button>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h5>Minhas Comissões</h5>
                </div>
                <div class="card-body text-center">
                    <p class="fs-4 text-success fw-bold">R$ {{ number_format($totalComissoes ?? 0, 2, ',', '.') }}</p>
                    <p class="text-muted">Total de comissões pagas</p>
                    <a href="{{ route('barbeiro.comissoes') }}" class="btn btn-outline-primary mt-2">Ver Detalhes</a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal de Horários -->
    <div class="modal fade" id="horariosModal" tabindex="-1" aria-labelledby="horariosModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="horariosModalLabel">Configurar Horários de Trabalho</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Selecione os horários que deseja bloquear para este dia:</p>
                    <div id="horariosContainer" class="d-flex flex-wrap gap-2">
                        <!-- Horários serão carregados dinamicamente -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="salvarHorarios">Salvar</button>
                </div>
            </div>
        </div>
    </div>
@endsection