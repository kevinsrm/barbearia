@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h1 class="h4 mb-0">Olá, {{ auth()->user()->nome }} 👋</h1>
            <span class="text-muted small">Hoje é {{ now()->locale('pt_BR')->isoFormat('dddd, D [de] MMMM [de] YYYY') }}</span>
        </div>
        <div class="d-flex gap-2">
            @can('gerenciar-agendamentos')
                <a href="{{ route('admin.agendamentos.create') }}" class="btn btn-dark btn-sm"><i class="bi bi-calendar-plus"></i> Novo agendamento</a>
            @endcan
            @can('gerenciar-vendas')
                <a href="{{ route('admin.vendas.create') }}" class="btn btn-outline-dark btn-sm"><i class="bi bi-bag-plus"></i> Nova venda</a>
            @endcan
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card card-metrica h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="text-muted small">Agendamentos hoje</div>
                            <div class="valor">{{ $totalAgendamentosHoje }}</div>
                        </div>
                        <i class="bi bi-calendar-event fs-2 text-primary opacity-50"></i>
                    </div>
                    <div class="small text-muted">{{ $agendamentosPendentes }} aguardando confirmação</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card card-metrica h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="text-muted small">Vendas hoje</div>
                            <div class="valor">@money($vendasHoje)</div>
                        </div>
                        <i class="bi bi-cash-coin fs-2 text-success opacity-50"></i>
                    </div>
                    <div class="small text-muted">No mês: @money($vendasMes)</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card card-metrica h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="text-muted small">Ticket médio do mês</div>
                            <div class="valor">@money($ticketMedio)</div>
                        </div>
                        <i class="bi bi-graph-up-arrow fs-2 text-info opacity-50"></i>
                    </div>
                    <div class="small text-muted">{{ $novosClientesMes }} novo(s) cliente(s)</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card card-metrica h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="text-muted small">Comissões pendentes</div>
                            <div class="valor">@money($comissoesPendentes)</div>
                        </div>
                        <i class="bi bi-percent fs-2 text-warning opacity-50"></i>
                    </div>
                    <div class="small text-muted">A pagar: @money($contasPagar) · A receber: @money($contasReceber)</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <strong><i class="bi bi-calendar-week"></i> Agenda de hoje</strong>
                    @can('gerenciar-agendamentos')
                        <a href="{{ route('admin.agendamentos.index') }}" class="btn btn-sm btn-outline-secondary">Ver agenda completa</a>
                    @endcan
                </div>
                <div class="card-body p-0">
                    @if ($agendamentosHoje->isEmpty())
                        <p class="text-muted p-3 mb-0">Nenhum agendamento para hoje.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 90px">Hora</th>
                                        <th>Cliente</th>
                                        <th>Profissional</th>
                                        <th>Serviços</th>
                                        <th class="text-end">Valor</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($agendamentosHoje as $agendamento)
                                        <tr>
                                            <td class="fw-semibold">{{ substr($agendamento->hora, 0, 5) }}</td>
                                            <td>{{ $agendamento->cliente_nome_exibicao }}</td>
                                            <td>{{ $agendamento->funcionario->pessoa->nome ?? '—' }}</td>
                                            <td class="small text-muted">{{ $agendamento->servicos_texto ?: '—' }}</td>
                                            <td class="text-end">@money($agendamento->valor_total)</td>
                                            <td>
                                                @php
                                                    $cores = ['pendente' => 'warning', 'confirmado' => 'info', 'concluido' => 'success', 'cancelado' => 'secondary', 'faltou' => 'danger', 'bloqueado' => 'dark'];
                                                @endphp
                                                <span class="badge bg-{{ $cores[$agendamento->status] ?? 'secondary' }} badge-status">
                                                    {{ App\Models\Agendamento::STATUS[$agendamento->status] ?? $agendamento->status }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            @can('gerenciar-produtos')
                <div class="card mb-3">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <strong><i class="bi bi-exclamation-triangle text-danger"></i> Estoque baixo</strong>
                        <a href="{{ route('admin.produtos.index', ['estoque_baixo' => 1]) }}" class="btn btn-sm btn-outline-secondary">{{ $totalProdutosEstoqueBaixo }}</a>
                    </div>
                    <div class="card-body p-0">
                        @if ($produtosEstoqueBaixo->isEmpty())
                            <p class="text-muted p-3 mb-0">Todos os produtos com estoque saudável.</p>
                        @else
                            <ul class="list-group list-group-flush">
                                @foreach ($produtosEstoqueBaixo as $produto)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span class="small">{{ $produto->nome }}</span>
                                        <span class="badge bg-danger">{{ $produto->estoque }} / mín {{ $produto->estoque_minimo }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            @endcan

            <div class="card">
                <div class="card-header bg-white"><strong><i class="bi bi-lightning-charge"></i> Atalhos</strong></div>
                <div class="card-body d-grid gap-2">
                    @can('gerenciar-pessoas')
                        <a href="{{ route('admin.pessoas.create', ['tipo' => 'cliente']) }}" class="btn btn-outline-dark btn-sm"><i class="bi bi-person-plus"></i> Cadastrar cliente</a>
                    @endcan
                    @can('gerenciar-financeiro')
                        <a href="{{ route('admin.financeiro.comissoes') }}" class="btn btn-outline-dark btn-sm"><i class="bi bi-percent"></i> Pagar comissões</a>
                        <a href="{{ route('admin.financeiro.contas') }}" class="btn btn-outline-dark btn-sm"><i class="bi bi-journals"></i> Contas a pagar/receber</a>
                    @endcan
                    @can('ver-relatorios')
                        <a href="{{ route('admin.relatorios.financeiro') }}" class="btn btn-outline-dark btn-sm"><i class="bi bi-graph-up"></i> Relatório financeiro</a>
                    @endcan
                </div>
            </div>
        </div>
    </div>
@endsection
