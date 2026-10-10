@extends('layouts.admin')

@section('title', 'Pessoas')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <h1 class="h4 mb-0"><i class="bi bi-people"></i> Pessoas</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.pessoas.create', ['tipo' => 'cliente']) }}" class="btn btn-dark btn-sm"><i class="bi bi-person-plus"></i> Novo cliente</a>
            <a href="{{ route('admin.pessoas.create', ['tipo' => 'funcionario']) }}" class="btn btn-outline-dark btn-sm"><i class="bi bi-person-badge"></i> Novo profissional</a>
            <a href="{{ route('admin.pessoas.create', ['tipo' => 'fornecedor']) }}" class="btn btn-outline-dark btn-sm"><i class="bi bi-truck"></i> Fornecedor</a>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form action="{{ route('admin.pessoas.index') }}" method="GET" class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label class="form-label small mb-1" for="busca">Buscar</label>
                    <input type="search" class="form-control form-control-sm" id="busca" name="busca" value="{{ $filtros['busca'] ?? '' }}" placeholder="Nome, telefone, e-mail ou CPF">
                </div>
                <div class="col-md-3">
                    <label class="form-label small mb-1" for="tipo">Tipo</label>
                    <select class="form-select form-select-sm" id="tipo" name="tipo">
                        <option value="">Todos</option>
                        @foreach ($tipos as $valor => $rotulo)
                            <option value="{{ $valor }}" {{ ($filtros['tipo'] ?? '') === $valor ? 'selected' : '' }}>{{ $rotulo }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-1" for="status">Situação</label>
                    <select class="form-select form-select-sm" id="status" name="status">
                        <option value="">Todas</option>
                        <option value="1" {{ ($filtros['status'] ?? '') === '1' ? 'selected' : '' }}>Ativos</option>
                        <option value="0" {{ ($filtros['status'] ?? '') === '0' ? 'selected' : '' }}>Inativos</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-sm btn-dark flex-grow-1" type="submit"><i class="bi bi-search"></i></button>
                    <a href="{{ route('admin.pessoas.index') }}" class="btn btn-sm btn-outline-secondary">Limpar</a>
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
                            <th>Nome</th>
                            <th>Tipo</th>
                            <th>Contato</th>
                            <th>Documento</th>
                            <th>Vínculo</th>
                            <th>Situação</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pessoas as $pessoa)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $pessoa->nome }}</div>
                                    <div class="small text-muted">{{ $pessoa->cidade }}{{ $pessoa->estado ? '/'.$pessoa->estado : '' }}</div>
                                </td>
                                <td><span class="badge bg-light text-dark border">{{ $pessoa->tipo_label }}</span></td>
                                <td class="small">
                                    {{ $pessoa->telefone ?: '—' }}
                                    <div class="text-muted">{{ $pessoa->email }}</div>
                                </td>
                                <td class="small">{{ $pessoa->cpf ?: '—' }}</td>
                                <td class="small">
                                    @if ($pessoa->cliente)
                                        <span class="text-muted">Cliente {{ $pessoa->cliente->codigo_cliente ?: '#'.$pessoa->cliente->id }}</span>
                                    @elseif ($pessoa->funcionario)
                                        <span class="text-muted">{{ $pessoa->funcionario->cargo->nome ?? 'Funcionário' }} · {{ number_format((float) $pessoa->funcionario->percentual_comissao, 0) }}%</span>
                                    @elseif ($pessoa->fornecedor)
                                        <span class="text-muted">Fornecedor {{ $pessoa->fornecedor->codigo_fornecedor ?: '' }}</span>
                                    @elseif ($pessoa->usuario)
                                        <span class="text-muted">Usuário {{ $pessoa->usuario->role_label }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($pessoa->ativo)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">Ativo</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border">Inativo</span>
                                    @endif
                                </td>
                                <td class="text-end text-nowrap">
                                    <div class="btn-group btn-group-sm">
                                        @if ($pessoa->cliente)
                                            @can('gerenciar-agendamentos')
                                                <a href="{{ route('admin.agendamentos.create') }}" class="btn btn-outline-primary" title="Agendar"><i class="bi bi-calendar-plus"></i></a>
                                            @endcan
                                            @can('gerenciar-vendas')
                                                <a href="{{ route('admin.vendas.create') }}" class="btn btn-outline-dark" title="Nova venda"><i class="bi bi-bag-plus"></i></a>
                                            @endcan
                                        @endif
                                        <a href="{{ route('admin.pessoas.edit', $pessoa) }}" class="btn btn-outline-secondary" title="Editar"><i class="bi bi-pencil"></i></a>
                                        <form action="{{ route('admin.pessoas.destroy', $pessoa) }}" method="POST" onsubmit="return confirm('Excluir {{ $pessoa->nome }}?');">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-outline-danger" title="Excluir"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">Nenhum cadastro encontrado. Use os botões acima para incluir o primeiro.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($pessoas->hasPages())
            <div class="card-footer bg-white">{{ $pessoas->links() }}</div>
        @endif
    </div>
@endsection
