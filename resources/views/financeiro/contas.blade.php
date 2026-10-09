@extends('layouts.admin')

@section('title', 'Contas a pagar e receber')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h1 class="h4 mb-0"><i class="bi bi-journals"></i> Contas</h1>
            <span class="text-muted small">A pagar: <strong class="text-danger">@money($totalPagar)</strong> · A receber: <strong class="text-success">@money($totalReceber)</strong></span>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalPagar"><i class="bi bi-plus-lg"></i> Conta a pagar</button>
            <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalReceber"><i class="bi bi-plus-lg"></i> Conta a receber</button>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header bg-white"><strong class="text-danger"><i class="bi bi-arrow-up-right-circle"></i> A pagar</strong></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr><th>Descrição</th><th>Vencimento</th><th class="text-end">Valor</th><th>Situação</th><th class="text-end">Ações</th></tr>
                            </thead>
                            <tbody>
                                @forelse ($contasPagar as $conta)
                                    <tr>
                                        <td>
                                            <div class="small fw-semibold">{{ $conta->descricao }}</div>
                                            <div class="small text-muted">{{ $conta->fornecedor->pessoa->nome ?? '' }}</div>
                                        </td>
                                        <td class="small {{ $conta->vencida ? 'text-danger fw-semibold' : '' }}">@data($conta->vencimento)</td>
                                        <td class="text-end">@money($conta->valor)</td>
                                        <td>
                                            <span class="badge bg-{{ $conta->status === 'paga' ? 'success' : ($conta->vencida ? 'danger' : 'warning') }} badge-status">
                                                {{ $conta->status === 'paga' ? 'Paga' : ($conta->vencida ? 'Vencida' : 'Pendente') }}
                                            </span>
                                        </td>
                                        <td class="text-end text-nowrap">
                                            @if ($conta->status === 'pendente')
                                                <form action="{{ route('admin.financeiro.contas.pagar.baixa', $conta) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button class="btn btn-sm btn-outline-success" title="Dar baixa" onclick="return confirm('Confirmar pagamento?');"><i class="bi bi-check2"></i></button>
                                                </form>
                                            @endif
                                            <form action="{{ route('admin.financeiro.contas.destroy', ['pagar', $conta->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Excluir esta conta?');">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger" title="Excluir"><i class="bi bi-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted py-3">Nenhuma conta a pagar.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if ($contasPagar->hasPages())
                    <div class="card-footer bg-white">{{ $contasPagar->links() }}</div>
                @endif
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header bg-white"><strong class="text-success"><i class="bi bi-arrow-down-left-circle"></i> A receber</strong></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr><th>Descrição</th><th>Vencimento</th><th class="text-end">Valor</th><th>Situação</th><th class="text-end">Ações</th></tr>
                            </thead>
                            <tbody>
                                @forelse ($contasReceber as $conta)
                                    <tr>
                                        <td>
                                            <div class="small fw-semibold">{{ $conta->descricao }}</div>
                                            <div class="small text-muted">{{ $conta->cliente->pessoa->nome ?? '' }}</div>
                                        </td>
                                        <td class="small {{ $conta->vencida ? 'text-danger fw-semibold' : '' }}">@data($conta->vencimento)</td>
                                        <td class="text-end">@money($conta->valor)</td>
                                        <td>
                                            <span class="badge bg-{{ $conta->status === 'recebida' ? 'success' : ($conta->vencida ? 'danger' : 'warning') }} badge-status">
                                                {{ $conta->status === 'recebida' ? 'Recebida' : ($conta->vencida ? 'Vencida' : 'Pendente') }}
                                            </span>
                                        </td>
                                        <td class="text-end text-nowrap">
                                            @if ($conta->status === 'pendente')
                                                <form action="{{ route('admin.financeiro.contas.receber.baixa', $conta) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button class="btn btn-sm btn-outline-success" title="Confirmar recebimento" onclick="return confirm('Confirmar recebimento?');"><i class="bi bi-check2"></i></button>
                                                </form>
                                            @endif
                                            <form action="{{ route('admin.financeiro.contas.destroy', ['receber', $conta->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Excluir esta conta?');">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger" title="Excluir"><i class="bi bi-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted py-3">Nenhuma conta a receber.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if ($contasReceber->hasPages())
                    <div class="card-footer bg-white">{{ $contasReceber->links() }}</div>
                @endif
            </div>
        </div>
    </div>

    {{-- Modal: nova conta a pagar --}}
    <div class="modal fade" id="modalPagar" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content" action="{{ route('admin.financeiro.contas.pagar.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Nova conta a pagar</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" for="pagar_descricao">Descrição *</label>
                        <input type="text" class="form-control" id="pagar_descricao" name="descricao" required maxlength="180" placeholder="Ex.: aluguel, energia, fornecedor">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="pagar_fornecedor">Fornecedor</label>
                        <select class="form-select" id="pagar_fornecedor" name="fornecedor_id">
                            <option value="">— Não vincular —</option>
                            @foreach ($fornecedores as $fornecedor)
                                <option value="{{ $fornecedor->id }}">{{ $fornecedor->pessoa->nome ?? 'Fornecedor' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label" for="pagar_valor">Valor (R$) *</label>
                            <input type="number" step="0.01" min="0.01" class="form-control" id="pagar_valor" name="valor" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label" for="pagar_vencimento">Vencimento *</label>
                            <input type="date" class="form-control" id="pagar_vencimento" name="vencimento" value="{{ today()->format('Y-m-d') }}" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Lançar conta</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal: nova conta a receber --}}
    <div class="modal fade" id="modalReceber" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content" action="{{ route('admin.financeiro.contas.receber.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Nova conta a receber</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" for="receber_descricao">Descrição *</label>
                        <input type="text" class="form-control" id="receber_descricao" name="descricao" required maxlength="180" placeholder="Ex.: fiado do cliente João">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="receber_cliente">Cliente</label>
                        <select class="form-select" id="receber_cliente" name="cliente_id">
                            <option value="">— Não vincular —</option>
                            @foreach ($clientes as $cliente)
                                <option value="{{ $cliente->id }}">{{ $cliente->pessoa->nome ?? 'Cliente' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label" for="receber_valor">Valor (R$) *</label>
                            <input type="number" step="0.01" min="0.01" class="form-control" id="receber_valor" name="valor" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label" for="receber_vencimento">Vencimento *</label>
                            <input type="date" class="form-control" id="receber_vencimento" name="vencimento" value="{{ today()->format('Y-m-d') }}" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Lançar conta</button>
                </div>
            </form>
        </div>
    </div>
@endsection
