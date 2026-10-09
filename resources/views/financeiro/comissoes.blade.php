@extends('layouts.admin')

@section('title', 'Comissões')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h1 class="h4 mb-0"><i class="bi bi-percent"></i> Comissões</h1>
            <span class="text-muted small">Pendentes: <strong class="text-warning">@money($totalPendente)</strong> · Pagas: <strong class="text-success">@money($totalPago)</strong></span>
        </div>
        <a href="{{ route('admin.relatorios.comissoes') }}" class="btn btn-outline-dark btn-sm"><i class="bi bi-graph-up"></i> Relatório por profissional</a>
    </div>

    <div class="card mb-3">
        <div class="card-body py-2">
            <form action="{{ route('admin.financeiro.comissoes') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-5">
                    <select class="form-select form-select-sm" name="funcionario">
                        <option value="">Todos os profissionais</option>
                        @foreach ($funcionarios as $funcionario)
                            <option value="{{ $funcionario->id }}" {{ (string) request('funcionario') === (string) $funcionario->id ? 'selected' : '' }}>
                                {{ $funcionario->pessoa->nome ?? 'Profissional' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <select class="form-select form-select-sm" name="status">
                        <option value="">Todas as situações</option>
                        @foreach (App\Models\Comissao::STATUS as $valor => $rotulo)
                            <option value="{{ $valor }}" {{ request('status') === $valor ? 'selected' : '' }}>{{ $rotulo }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-1">
                    <button class="btn btn-sm btn-dark flex-grow-1" type="submit"><i class="bi bi-search"></i> Filtrar</button>
                    <a href="{{ route('admin.financeiro.comissoes') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x-lg"></i></a>
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
                            <th>Profissional</th>
                            <th>Origem</th>
                            <th class="text-end">Base</th>
                            <th class="text-center">%</th>
                            <th class="text-end">Comissão</th>
                            <th>Situação</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($comissoes as $comissao)
                            <tr>
                                <td class="small">@data($comissao->data)</td>
                                <td>{{ $comissao->funcionario_nome }}</td>
                                <td class="small">
                                    @if ($comissao->venda)
                                        <a href="{{ route('admin.vendas.show', $comissao->venda) }}">Venda #{{ $comissao->venda_id }}</a>
                                    @elseif ($comissao->agendamento_id)
                                        Agendamento #{{ $comissao->agendamento_id }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="text-end small">@money($comissao->valor_base)</td>
                                <td class="text-center small">{{ number_format((float) $comissao->percentual, 1) }}%</td>
                                <td class="text-end fw-semibold">@money($comissao->valor)</td>
                                <td>
                                    <span class="badge bg-{{ $comissao->status === 'paga' ? 'success' : 'warning' }} badge-status">
                                        {{ App\Models\Comissao::STATUS[$comissao->status] ?? $comissao->status }}
                                    </span>
                                    @if ($comissao->data_pagamento)
                                        <div class="small text-muted">em @data($comissao->data_pagamento)</div>
                                    @endif
                                </td>
                                <td class="text-end text-nowrap">
                                    @if ($comissao->status === 'pendente')
                                        <form action="{{ route('admin.financeiro.comissoes.pagar', $comissao) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-success" title="Marcar como paga" onclick="return confirm('Confirmar pagamento?');"><i class="bi bi-cash"></i> Pagar</button>
                                        </form>
                                    @else
                                        <form action="{{ route('admin.financeiro.comissoes.estornar', $comissao) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-secondary" title="Voltar para pendente"><i class="bi bi-arrow-counterclockwise"></i></button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted py-4">Nenhuma comissão encontrada. As comissões são geradas automaticamente a cada venda com profissional.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($comissoes->hasPages())
            <div class="card-footer bg-white">{{ $comissoes->links() }}</div>
        @endif
    </div>
@endsection
