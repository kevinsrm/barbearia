@extends('layouts.admin')

@section('title', 'Nova venda')

@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('admin.vendas.index') }}">Vendas</a></li>
            <li class="breadcrumb-item active">Nova venda</li>
        </ol>
    </nav>

    <div class="card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <strong><i class="bi bi-bag-plus"></i> Registrar venda</strong>
            @if ($agendamento)
                <span class="badge bg-info text-dark">Agendamento #{{ $agendamento->id }} · {{ $agendamento->cliente_nome_exibicao }}</span>
            @endif
        </div>
        <div class="card-body">
            <form action="{{ route('admin.vendas.store') }}" method="POST" id="formVenda">
                @csrf

                @if ($agendamento)
                    <input type="hidden" name="agendamento_id" value="{{ $agendamento->id }}">
                @endif

                <div class="row g-3 mb-4">
                    <div class="col-md-5">
                        <label class="form-label" for="cliente_id">Cliente</label>
                        <select class="form-select" id="cliente_id" name="cliente_id">
                            <option value="">— Consumidor final —</option>
                            @foreach ($clientes as $cliente)
                                <option value="{{ $cliente->id }}"
                                    {{ (int) old('cliente_id', $agendamento->cliente_id ?? '') === $cliente->id ? 'selected' : '' }}>
                                    {{ $cliente->pessoa->nome ?? 'Cliente' }}{{ $cliente->pessoa->telefone ? ' · '.$cliente->pessoa->telefone : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="funcionario_id">Profissional</label>
                        <select class="form-select" id="funcionario_id" name="funcionario_id">
                            <option value="">— Sem profissional —</option>
                            @foreach ($funcionarios as $funcionario)
                                <option value="{{ $funcionario->id }}"
                                    {{ (int) old('funcionario_id', $agendamento->funcionario_id ?? '') === $funcionario->id ? 'selected' : '' }}>
                                    {{ $funcionario->pessoa->nome ?? 'Profissional' }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">Define quem recebe a comissão.</div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="data">Data *</label>
                        <input type="datetime-local" class="form-control @error('data') is-invalid @enderror" id="data" name="data"
                               value="{{ old('data', now()->format('Y-m-d\TH:i')) }}" required>
                        @error('data')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <h2 class="h6 mb-2"><i class="bi bi-list-ul"></i> Itens da venda</h2>
                <div class="table-responsive">
                    <table class="table align-middle" id="tabelaItens">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 130px">Tipo</th>
                                <th>Item</th>
                                <th style="width: 90px" class="text-center">Qtd.</th>
                                <th style="width: 130px">Valor unit.</th>
                                <th style="width: 120px" class="text-end">Subtotal</th>
                                <th style="width: 50px"></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <tr>
                                <th colspan="4" class="text-end">Total dos itens</th>
                                <th class="text-end" id="totalItens">R$ 0,00</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <button type="button" class="btn btn-sm btn-outline-dark" id="btnAddItem"><i class="bi bi-plus-lg"></i> Adicionar item</button>

                <div class="row g-3 mt-3">
                    <div class="col-md-3">
                        <label class="form-label" for="forma_pagamento">Forma de pagamento *</label>
                        <select class="form-select" id="forma_pagamento" name="forma_pagamento" required>
                            @foreach ($formasPagamento as $valor => $rotulo)
                                <option value="{{ $valor }}" {{ old('forma_pagamento', 'dinheiro') === $valor ? 'selected' : '' }}>{{ $rotulo }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="desconto">Desconto (R$)</label>
                        <input type="number" step="0.01" min="0" class="form-control" id="desconto" name="desconto" value="{{ old('desconto', 0) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="observacoes">Observações</label>
                        <input type="text" class="form-control" id="observacoes" name="observacoes" maxlength="500" value="{{ old('observacoes') }}">
                    </div>
                </div>

                <div class="border-top mt-4 pt-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div class="fs-5">
                        Total da venda: <strong class="text-success" id="totalVenda">R$ 0,00</strong>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.vendas.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-dark"><i class="bi bi-check-lg"></i> Finalizar venda</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@php
    $catalogoJson = $servicos
        ->map(fn ($servico) => [
            'tipo' => 'servico',
            'id' => $servico->id,
            'nome' => $servico->nome,
            'preco' => (float) $servico->valor,
            'estoque' => null,
        ])
        ->concat($produtos->map(fn ($produto) => [
            'tipo' => 'produto',
            'id' => $produto->id,
            'nome' => $produto->nome,
            'preco' => (float) $produto->preco_venda,
            'estoque' => (int) $produto->estoque,
        ]))
        ->values();

    $itensAgendamentoJson = $agendamento
        ? $agendamento->servicos->map(fn ($servico) => [
            'tipo' => 'servico',
            'id' => $servico->id,
            'quantidade' => 1,
            'valor_unitario' => (float) ($servico->pivot->valor ?: $servico->valor),
        ])->values()
        : collect();
@endphp
@push('scripts')
<script>
(function () {
    const catalogo = {!! json_encode($catalogoJson, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!};
    const itensAgendamento = {!! json_encode($itensAgendamentoJson, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!};

    const corpo = document.querySelector('#tabelaItens tbody');
    const totalItens = document.getElementById('totalItens');
    const totalVenda = document.getElementById('totalVenda');
    const desconto = document.getElementById('desconto');
    let indice = 0;

    function formatar(valor) { return 'R$ ' + (Number(valor) || 0).toFixed(2).replace('.', ','); }

    function opcoes(tipoSelecionado, idSelecionado) {
        return catalogo
            .filter(function (item) { return item.tipo === tipoSelecionado; })
            .map(function (item) {
                const selecionado = item.id === Number(idSelecionado) ? ' selected' : '';
                const estoque = item.estoque !== null ? ' (estoque ' + item.estoque + ')' : '';
                return '<option value="' + item.id + '"' + selecionado + '>' + item.nome + estoque + '</option>';
            })
            .join('');
    }

    function recalcular() {
        let total = 0;
        corpo.querySelectorAll('tr').forEach(function (linha) {
            const qtd = parseFloat(linha.querySelector('.qtd').value || 0);
            const unit = parseFloat(linha.querySelector('.unitario').value || 0);
            const subtotal = qtd * unit;
            linha.querySelector('.subtotal').textContent = formatar(subtotal);
            total += subtotal;
        });
        totalItens.textContent = formatar(total);
        totalVenda.textContent = formatar(Math.max(0, total - (parseFloat(desconto.value || 0))));
    }

    function adicionarLinha(dados) {
        dados = dados || { tipo: 'servico', id: null, quantidade: 1, valor_unitario: null };
        const i = indice++;
        const tr = document.createElement('tr');
        tr.innerHTML =
            '<td><select class="form-select form-select-sm tipo" name="itens[' + i + '][tipo]" required>' +
                '<option value="servico"' + (dados.tipo === 'servico' ? ' selected' : '') + '>Serviço</option>' +
                '<option value="produto"' + (dados.tipo === 'produto' ? ' selected' : '') + '>Produto</option>' +
            '</select></td>' +
            '<td><select class="form-select form-select-sm item" name="itens[' + i + '][id]" required>' + opcoes(dados.tipo, dados.id) + '</select>' +
                '<input type="hidden" class="tipo-oculto" name="itens[' + i + '][tipo_ref]"></td>' +
            '<td><input type="number" min="1" class="form-control form-control-sm qtd text-center" name="itens[' + i + '][quantidade]" value="' + (dados.quantidade || 1) + '" required></td>' +
            '<td><input type="number" step="0.01" min="0" class="form-control form-control-sm unitario" name="itens[' + i + '][valor_unitario]" value="' + (dados.valor_unitario !== null && dados.valor_unitario !== undefined ? dados.valor_unitario : '') + '" required></td>' +
            '<td class="text-end subtotal">R$ 0,00</td>' +
            '<td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger remover" title="Remover item"><i class="bi bi-trash"></i></button></td>';

        corpo.appendChild(tr);

        const selectTipo = tr.querySelector('.tipo');
        const selectItem = tr.querySelector('.item');

        function sincronizarPreco() {
            const escolhido = catalogo.find(function (c) { return c.tipo === selectTipo.value && String(c.id) === selectItem.value; });
            if (escolhido) tr.querySelector('.unitario').value = Number(escolhido.preco).toFixed(2);
            recalcular();
        }

        selectTipo.addEventListener('change', function () {
            selectItem.innerHTML = opcoes(selectTipo.value, null);
            sincronizarPreco();
        });
        selectItem.addEventListener('change', sincronizarPreco);
        tr.querySelector('.qtd').addEventListener('input', recalcular);
        tr.querySelector('.unitario').addEventListener('input', recalcular);
        tr.querySelector('.remover').addEventListener('click', function () { tr.remove(); recalcular(); });

        if (dados.valor_unitario === null || dados.valor_unitario === undefined) sincronizarPreco();
        recalcular();
    }

    document.getElementById('btnAddItem').addEventListener('click', function () { adicionarLinha(); });
    desconto.addEventListener('input', recalcular);

    if (itensAgendamento.length) {
        itensAgendamento.forEach(adicionarLinha);
    } else {
        adicionarLinha();
    }
})();
</script>
@endpush
