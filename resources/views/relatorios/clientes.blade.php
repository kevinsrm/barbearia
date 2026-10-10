@extends('layouts.admin')

@section('title', 'Relatório de clientes')

@section('content')
    <h1 class="h4 mb-3"><i class="bi bi-people"></i> Clientes por valor gasto</h1>

    @include('relatorios._periodo', ['acao' => route('admin.relatorios.clientes')])

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th style="width: 60px">#</th><th>Cliente</th><th>Contato</th><th class="text-center">Compras</th><th class="text-end">Total gasto</th><th class="text-end">Ticket médio</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($clientes as $posicao => $cliente)
                            <tr>
                                <td class="text-muted">{{ $posicao + 1 }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $cliente->pessoa->nome ?? 'Cliente' }}</div>
                                    <div class="small text-muted">Desde @data($cliente->created_at)</div>
                                </td>
                                <td class="small">{{ $cliente->pessoa->telefone ?? '—' }}<div class="text-muted">{{ $cliente->pessoa->email }}</div></td>
                                <td class="text-center">{{ (int) $cliente->vendas_total }}</td>
                                <td class="text-end fw-semibold">@money($cliente->valor_total)</td>
                                <td class="text-end">@money($cliente->vendas_total > 0 ? ($cliente->valor_total / $cliente->vendas_total) : 0)</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">Nenhum cliente cadastrado.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
