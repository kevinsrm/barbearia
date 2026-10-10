@extends('layouts.admin')

@section('title', 'Usuários')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <h1 class="h4 mb-0"><i class="bi bi-shield-lock"></i> Usuários e acessos</h1>
        <a href="{{ route('admin.usuarios.create') }}" class="btn btn-dark btn-sm"><i class="bi bi-plus-lg"></i> Novo usuário</a>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form action="{{ route('admin.usuarios.index') }}" method="GET" class="row g-2 align-items-end">
                <div class="col-md-8">
                    <label class="form-label small mb-1" for="busca">Buscar</label>
                    <input type="search" class="form-control form-control-sm" id="busca" name="busca" value="{{ request('busca') }}" placeholder="Nome ou e-mail">
                </div>
                <div class="col-md-4">
                    <button class="btn btn-sm btn-dark w-100" type="submit"><i class="bi bi-search"></i> Buscar</button>
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
                            <th>E-mail</th>
                            <th>Perfil</th>
                            <th>Vínculo</th>
                            <th>Último acesso</th>
                            <th>Situação</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($usuarios as $usuario)
                            <tr>
                                <td class="fw-semibold">{{ $usuario->nome }}</td>
                                <td class="small">{{ $usuario->email }}</td>
                                <td><span class="badge bg-light text-dark border">{{ $usuario->role_label }}</span></td>
                                <td class="small text-muted">{{ $usuario->pessoa->nome ?? '—' }}</td>
                                <td class="small text-muted">@dataHora($usuario->ultimo_acesso)</td>
                                <td>
                                    @if ($usuario->ativo)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">Ativo</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border">Bloqueado</span>
                                    @endif
                                </td>
                                <td class="text-end text-nowrap">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.usuarios.edit', $usuario) }}" class="btn btn-outline-secondary" title="Editar"><i class="bi bi-pencil"></i></a>
                                        <form action="{{ route('admin.usuarios.destroy', $usuario) }}" method="POST" onsubmit="return confirm('Remover o acesso de {{ $usuario->nome }}?');">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-outline-danger" title="Excluir"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">Nenhum usuário cadastrado.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($usuarios->hasPages())
            <div class="card-footer bg-white">{{ $usuarios->links() }}</div>
        @endif
    </div>

    <div class="alert alert-light border mt-3 small mb-0">
        <i class="bi bi-info-circle"></i>
        Perfis disponíveis: @foreach ($papeis as $valor => $rotulo)<span class="badge bg-light text-dark border">{{ $rotulo }}</span> @endforeach
        — as permissões de cada perfil ficam em <code>config/barbearia.php</code>.
    </div>
@endsection
