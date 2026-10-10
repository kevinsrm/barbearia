@extends('layouts.admin')

@section('title', 'Aniversariantes')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <h1 class="h4 mb-0"><i class="bi bi-gift"></i> Aniversariantes</h1>
        <form action="{{ route('admin.relatorios.aniversariantes') }}" method="GET" class="d-flex gap-2">
            <select class="form-select form-select-sm" name="mes" onchange="this.form.submit()" style="width: auto">
                @foreach ($meses as $numero => $nome)
                    <option value="{{ $numero }}" {{ $mes === $numero ? 'selected' : '' }}>{{ $nome }}</option>
                @endforeach
            </select>
            <button class="btn btn-sm btn-outline-secondary" type="button" onclick="window.print()"><i class="bi bi-printer"></i></button>
        </form>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Cliente</th><th>Telefone</th><th>E-mail</th><th>Aniversário</th><th class="text-center">Idade</th><th class="text-end">Ação</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($aniversariantes as $pessoa)
                            <tr>
                                <td class="fw-semibold">{{ $pessoa->nome }}</td>
                                <td class="small">{{ $pessoa->telefone ?: '—' }}</td>
                                <td class="small">{{ $pessoa->email ?: '—' }}</td>
                                <td class="small">@data($pessoa->data_nascimento)</td>
                                <td class="text-center">{{ $pessoa->data_nascimento ? $pessoa->data_nascimento->age : '—' }}</td>
                                <td class="text-end">
                                    @if ($pessoa->telefone)
                                        <a class="btn btn-sm btn-outline-success" target="_blank" rel="noopener"
                                           href="https://wa.me/55{{ preg_replace('/\D/', '', $pessoa->telefone) }}?text={{ urlencode('Olá '.$pessoa->nome.'! A equipe '.config('barbearia.nome').' deseja um feliz aniversário 🎉') }}">
                                            <i class="bi bi-whatsapp"></i> Parabenizar
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">Nenhum aniversariante em {{ $meses[$mes] }}. Cadastre a data de nascimento dos clientes para usar este recurso.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
