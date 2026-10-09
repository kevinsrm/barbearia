@extends('layouts.admin')

@section('title', 'Serviços')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <h1 class="h4 mb-0"><i class="bi bi-scissors"></i> Serviços</h1>
        <a href="{{ route('admin.servicos.create') }}" class="btn btn-dark btn-sm"><i class="bi bi-plus-lg"></i> Novo serviço</a>
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-body py-2">
                    <form action="{{ route('admin.servicos.index') }}" method="GET" class="row g-2 align-items-center">
                        <div class="col-md-6">
                            <input type="search" class="form-control form-control-sm" name="busca" value="{{ request('busca') }}" placeholder="Buscar serviço">
                        </div>
                        <div class="col-md-4">
                            <select class="form-select form-select-sm" name="categoria">
                                <option value="">Todas as categorias</option>
                                @foreach ($categorias as $categoria)
                                    <option value="{{ $categoria->id }}" {{ (string) request('categoria') === (string) $categoria->id ? 'selected' : '' }}>{{ $categoria->nome }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 d-flex gap-1">
                            <button class="btn btn-sm btn-dark flex-grow-1" type="submit"><i class="bi bi-search"></i></button>
                            <a href="{{ route('admin.servicos.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x-lg"></i></a>
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
                                    <th>Serviço</th>
                                    <th>Categoria</th>
                                    <th class="text-center">Duração</th>
                                    <th class="text-end">Valor</th>
                                    <th class="text-center">Comissão</th>
                                    <th class="text-center">Site</th>
                                    <th>Situação</th>
                                    <th class="text-end">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($servicos as $servico)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $servico->nome }}</div>
                                            <div class="small text-muted">{{ Str::limit($servico->descricao, 60) }}</div>
                                        </td>
                                        <td class="small">{{ $servico->categoria->nome ?? '—' }}</td>
                                        <td class="text-center small">{{ $servico->duracao }} min</td>
                                        <td class="text-end fw-semibold">@money($servico->valor)</td>
                                        <td class="text-center small">{{ number_format((float) $servico->percentual_comissao, 0) }}%</td>
                                        <td class="text-center">
                                            @if ($servico->agendamento_online)
                                                <i class="bi bi-check-circle-fill text-success"></i>
                                            @else
                                                <i class="bi bi-dash-circle text-muted"></i>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($servico->ativo)
                                                <span class="badge bg-success-subtle text-success border border-success-subtle">Ativo</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary border">Inativo</span>
                                            @endif
                                        </td>
                                        <td class="text-end text-nowrap">
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('admin.servicos.edit', $servico) }}" class="btn btn-outline-secondary" title="Editar"><i class="bi bi-pencil"></i></a>
                                                <form action="{{ route('admin.servicos.destroy', $servico) }}" method="POST" onsubmit="return confirm('Remover o serviço {{ $servico->nome }}?');">
                                                    @csrf @method('DELETE')
                                                    <button class="btn btn-outline-danger" title="Excluir"><i class="bi bi-trash"></i></button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="8" class="text-center text-muted py-4">Nenhum serviço cadastrado ainda.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if ($servicos->hasPages())
                    <div class="card-footer bg-white">{{ $servicos->links() }}</div>
                @endif
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-white"><strong><i class="bi bi-tags"></i> Categorias de serviço</strong></div>
                <div class="card-body">
                    <form action="{{ route('admin.categorias.store', 'servico') }}" method="POST" class="row g-2 mb-3">
                        @csrf
                        <div class="col-8">
                            <input type="text" class="form-control form-control-sm" name="nome" placeholder="Nova categoria" required>
                        </div>
                        <div class="col-4">
                            <button class="btn btn-sm btn-dark w-100" type="submit"><i class="bi bi-plus-lg"></i> Add</button>
                        </div>
                    </form>

                    <ul class="list-group">
                        @forelse ($categorias as $categoria)
                            <li class="list-group-item d-flex justify-content-between align-items-center gap-2">
                                <div class="flex-grow-1">
                                    <div class="small fw-semibold">{{ $categoria->nome }}</div>
                                    <div class="small text-muted">{{ $categoria->servicos()->count() }} serviço(s) · ordem {{ $categoria->ordem }}</div>
                                </div>
                                <form action="{{ route('admin.categorias.destroy', ['servico', $categoria->id]) }}" method="POST" onsubmit="return confirm('Remover a categoria?');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" title="Excluir"><i class="bi bi-trash"></i></button>
                                </form>
                            </li>
                        @empty
                            <li class="list-group-item text-muted small">Nenhuma categoria.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection
