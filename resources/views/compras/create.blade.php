@extends('layouts.admin')

@section('title', 'Nova compra')

@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('admin.compras.index') }}">Compras</a></li>
            <li class="breadcrumb-item active">Nova</li>
        </ol>
    </nav>

    <div class="card">
        <div class="card-header bg-white"><strong><i class="bi bi-box-seam"></i> Registrar compra de fornecedor</strong></div>
        <div class="card-body">
            <form action="{{ route('admin.compras.store') }}" method="POST" id="formCompra">
                @csrf

                <div class="row g-3 mb-4">
                    <div class="col-md-5">
                        <label class="form-label" for="fornecedor_id">Fornecedor</label>
                        <select class="form-select" id="fornecedor_id" name="fornecedor_id">
                            <option value="">— Sem fornecedor —</option>
                            @foreach ($fornecedores as $fornecedor)
                                <option value="{{ $fornecedor->id }}" {{ (string) old('fornecedor_id') === (string) $fornecedor->id ? 'selected' : '' }}>
                                    {{ $fornecedor->pessoa->nome ?? 'Fornecedor' }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">Cadastre fornecedores em <a href="{{ route('admin.pessoas.create', ['tipo' => 'fornecedor']) }}">Pessoas → Fornecedor</a>.</div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="data">Data *</label>
                        <input type="date" class="form-control @error('data') is-invalid @enderror" id="data" name="data" value="{{ old('data', today()->format('Y-m-d')) }}" required>
                        @error('data')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-2">
                        <label class="form-label" for="forma_pagamento">Pagamento *</label>
                        <select class="form-select" id="forma_pagamento" name="forma_pagamento" required>
                            @foreach (['boleto' => 'Boleto', 'pix' => 'Pix', 'dinheiro' => 'Dinheiro', 'credito' => 'Cartão de crédito', 'debito' => 'Cartão de débito', 'prazo' => 'Faturado / a prazo'] as $valor => $rotulo)
                                <option value="{{ $valor }}" {{ old('forma_pagamento', 'boleto') === $valor ? 'selected' : '' }}>{{ $rotulo }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label" for="status">Situação *</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="recebida" {{ old('status', 'recebida') === 'recebida' ? 'selected' : '' }}>Recebida (dá entrada no estoque)</option>
                            <option value="pendente" {{ old('status') === 'pendente' ? 'selected' : '' }}>Pendente (mercadoria a caminho)</option>
                        </select>
                    </div>
                </div>

                <h2 class="h6 mb-2"><i class="bi bi-list-ul"></i> Produtos</h2>
                <div class="table-responsive">
                    <table class="table align-middle" id="tabelaItens">
                        <thead class="table-light">
                            <tr>
                                <th>Produto</th>
                                <th style="width: 100px" class="text-center">Qtd.</th>
                                <th style="width: 150px">Custo unit. (R$)</th>
                                <th style="width: 130px" class="text-end">Subtotal</th>
                                <th style="width: 50px"></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-end">Total da compra</th>
                                <th class="text-end" id="totalCompra">R$ 0,00</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <button type="button" class="btn btn-sm btn-outline-dark" id="btnAddItem"><i class="bi bi-plus-lg"></i> Adicionar produto</button>

                <div class="row g-3 mt-3">
                    <div class="col-md-3">
                        <label class="form-label" for="prazo_pagamento">Prazo de pagamento (dias)</label>
                        <input type="number" min="0" max="365" class="form-control" id="prazo_pagamento" name="prazo_pagamento" value="{{ old('prazo_pagamento', 30) }}">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="gerar_conta_pagar" name="gerar_conta_pagar" value="1" {{ old('gerar_conta_pagar') ? 'checked' : '' }}>
                            <label class="form-check-label" for="gerar_conta_pagar">
                                Gerar conta a pagar com vencimento em {{ old('prazo_pagamento', 30) }} dias
                            </label>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label" for="observacoes">Observações</label>
                        <input type="text" class="form-control" id="observacoes" name="observacoes" maxlength="500" value="{{ old('observacoes') }}">
                    </div>
                </div>

                <div class="border-top mt-4 pt-3 d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.compras.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-dark"><i class="bi bi-check-lg"></i> Salvar compra</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@php
    $produtosJson = $produtos->map(fn ($produto) => [
        'id' => $produto->id,
        'nome' => $produto->nome,
        'custo' => (float) $produto->preco_custo,
        'estoque' => (int) $produto->estoque,
    ])->values();
@endphp
@push('scripts')
<script>
(function () {
    const produtos = {!! json_encode($produtosJson, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!};
    const corpo = document.querySelector('#tabelaItens tbody');
    const total = document.getElementById('totalCompra');
    let indice = 0;

    function formatar(valor) { return 'R$ ' + (Number(valor) || 0).toFixed(2).replace('.', ','); }

    function recalcular() {
        let soma = 0;
        corpo.querySelectorAll('tr').forEach(function (linha) {
            const subtotal = parseFloat(linha.querySelector('.qtd').value || 0) * parseFloat(linha.querySelector('.unitario').value || 0);
            linha.querySelector('.subtotal').textContent = formatar(subtotal);
            soma += subtotal;
        });
        total.textContent = formatar(soma);
    }

    function adicionarLinha() {
        const i = indice++;
        const tr = document.createElement('tr');
        tr.innerHTML =
            '<td><select class="form-select form-select-sm produto" name="itens[' + i + '][id]" required>' +
                '<option value="">Selecione o produto...</option>' +
                produtos.map(function (p) { return '<option value="' + p.id + '" data-custo="' + p.custo + '">' + p.nome + ' (estoque ' + p.estoque + ')</option>'; }).join('') +
            '</select></td>' +
            '<td><input type="number" min="1" class="form-control form-control-sm qtd text-center" name="itens[' + i + '][quantidade]" value="1" required></td>' +
            '<td><input type="number" step="0.01" min="0" class="form-control form-control-sm unitario" name="itens[' + i + '][valor_unitario]" value="0.00" required></td>' +
            '<td class="text-end subtotal">R$ 0,00</td>' +
            '<td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger remover"><i class="bi bi-trash"></i></button></td>';

        corpo.appendChild(tr);

        tr.querySelector('.produto').addEventListener('change', function () {
            const opcao = this.options[this.selectedIndex];
            tr.querySelector('.unitario').value = Number(opcao.dataset.custo || 0).toFixed(2);
            recalcular();
        });
        tr.querySelector('.qtd').addEventListener('input', recalcular);
        tr.querySelector('.unitario').addEventListener('input', recalcular);
        tr.querySelector('.remover').addEventListener('click', function () { tr.remove(); recalcular(); });

        recalcular();
    }

    document.getElementById('btnAddItem').addEventListener('click', adicionarLinha);
    adicionarLinha();
})();
</script>
@endpush
