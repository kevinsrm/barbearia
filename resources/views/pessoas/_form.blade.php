@php
    $vinculo = $pessoa->exists ? $pessoa : null;
    $cliente = $pessoa->cliente ?? null;
    $funcionario = $pessoa->funcionario ?? null;
    $fornecedor = $pessoa->fornecedor ?? null;
@endphp

<div class="row g-3">
    <div class="col-md-8">
        <label class="form-label" for="nome">Nome completo *</label>
        <input type="text" class="form-control @error('nome') is-invalid @enderror" id="nome" name="nome" value="{{ old('nome', $pessoa->nome) }}" required>
        @error('nome')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4">
        <label class="form-label" for="tipo_pessoa">Tipo de cadastro *</label>
        <select class="form-select" id="tipo_pessoa" name="tipo_pessoa" required>
            @foreach ($tipos as $valor => $rotulo)
                <option value="{{ $valor }}" {{ old('tipo_pessoa', $pessoa->tipo_pessoa ?: 'cliente') === $valor ? 'selected' : '' }}>{{ $rotulo }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label" for="cpf">CPF / CNPJ</label>
        <input type="text" class="form-control @error('cpf') is-invalid @enderror" id="cpf" name="cpf" value="{{ old('cpf', $pessoa->cpf) }}">
        @error('cpf')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4">
        <label class="form-label" for="telefone">Telefone / WhatsApp</label>
        <input type="text" class="form-control @error('telefone') is-invalid @enderror" id="telefone" name="telefone" value="{{ old('telefone', $pessoa->telefone) }}" placeholder="(98) 99999-0000">
        @error('telefone')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4">
        <label class="form-label" for="email">E-mail</label>
        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $pessoa->email) }}">
        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4">
        <label class="form-label" for="data_nascimento">Data de nascimento</label>
        <input type="date" class="form-control @error('data_nascimento') is-invalid @enderror" id="data_nascimento" name="data_nascimento"
               value="{{ old('data_nascimento', $pessoa->data_nascimento?->format('Y-m-d')) }}">
        @error('data_nascimento')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-8">
        <label class="form-label" for="endereco">Endereço</label>
        <input type="text" class="form-control" id="endereco" name="endereco" value="{{ old('endereco', $pessoa->endereco) }}">
    </div>

    <div class="col-md-2">
        <label class="form-label" for="numero">Número</label>
        <input type="text" class="form-control" id="numero" name="numero" value="{{ old('numero', $pessoa->numero) }}">
    </div>

    <div class="col-md-2">
        <label class="form-label" for="cep">CEP</label>
        <input type="text" class="form-control" id="cep" name="cep" value="{{ old('cep', $pessoa->cep) }}">
    </div>

    <div class="col-md-4">
        <label class="form-label" for="bairro">Bairro</label>
        <input type="text" class="form-control" id="bairro" name="bairro" value="{{ old('bairro', $pessoa->bairro) }}">
    </div>

    <div class="col-md-4">
        <label class="form-label" for="cidade">Cidade</label>
        <input type="text" class="form-control" id="cidade" name="cidade" value="{{ old('cidade', $pessoa->cidade) }}">
    </div>

    <div class="col-md-2">
        <label class="form-label" for="estado">UF</label>
        <input type="text" class="form-control" id="estado" name="estado" maxlength="2" value="{{ old('estado', $pessoa->estado) }}">
    </div>

    <div class="col-md-2">
        <label class="form-label" for="complemento">Complemento</label>
        <input type="text" class="form-control" id="complemento" name="complemento" value="{{ old('complemento', $pessoa->complemento) }}">
    </div>

    {{-- Campos específicos por tipo --}}
    <div class="col-12">
        <div class="border rounded p-3 bg-light" id="bloco-cliente">
            <h2 class="h6 mb-3"><i class="bi bi-person"></i> Dados do cliente</h2>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label" for="codigo_cliente">Código do cliente</label>
                    <input type="text" class="form-control" id="codigo_cliente" name="codigo_cliente" value="{{ old('codigo_cliente', $cliente?->codigo_cliente) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="status_cliente">Situação</label>
                    <select class="form-select" id="status_cliente" name="status_cliente">
                        <option value="ativo" {{ old('status_cliente', $cliente?->status ?? 'ativo') === 'ativo' ? 'selected' : '' }}>Ativo</option>
                        <option value="inativo" {{ old('status_cliente', $cliente?->status) === 'inativo' ? 'selected' : '' }}>Inativo</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="pontuacao_info">Pontuação</label>
                    <input type="text" class="form-control" id="pontuacao_info" value="{{ $cliente?->pontuacao ?? 0 }}" disabled>
                    <div class="form-text">Acumulada automaticamente pelas vendas.</div>
                </div>
                <div class="col-12">
                    <label class="form-label" for="observacoes_cliente">Observações do cliente</label>
                    <textarea class="form-control" id="observacoes_cliente" name="observacoes_cliente" rows="2">{{ old('observacoes_cliente', $cliente?->observacoes) }}</textarea>
                </div>
            </div>
        </div>

        <div class="border rounded p-3 bg-light mt-3" id="bloco-funcionario">
            <h2 class="h6 mb-3"><i class="bi bi-person-badge"></i> Dados do profissional</h2>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label" for="cargo_id">Cargo</label>
                    <select class="form-select" id="cargo_id" name="cargo_id">
                        <option value="">— Selecione —</option>
                        @foreach ($cargos as $cargo)
                            <option value="{{ $cargo->id }}" {{ (int) old('cargo_id', $funcionario?->cargo_id) === $cargo->id ? 'selected' : '' }}>{{ $cargo->nome }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="codigo_funcional">Código funcional</label>
                    <input type="text" class="form-control" id="codigo_funcional" name="codigo_funcional" value="{{ old('codigo_funcional', $funcionario?->codigo_funcional) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="data_admissao">Data de admissão</label>
                    <input type="date" class="form-control" id="data_admissao" name="data_admissao" value="{{ old('data_admissao', $funcionario?->data_admissao?->format('Y-m-d')) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="salario_base">Salário base (R$)</label>
                    <input type="number" step="0.01" min="0" class="form-control" id="salario_base" name="salario_base" value="{{ old('salario_base', $funcionario?->salario_base ?? 0) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="percentual_comissao">Comissão (%)</label>
                    <input type="number" step="0.01" min="0" max="100" class="form-control" id="percentual_comissao" name="percentual_comissao"
                           value="{{ old('percentual_comissao', $funcionario?->percentual_comissao ?? config('barbearia.comissao_padrao')) }}">
                    <div class="form-text">Usada nas vendas vinculadas a este profissional.</div>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="criar_acesso" name="criar_acesso" value="1"
                               {{ $funcionario?->usuario_id ? 'checked' : '' }} {{ ! $pessoa->exists ? 'checked' : '' }}>
                        <label class="form-check-label" for="criar_acesso">Criar acesso ao painel (perfil barbeiro)</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="senha_acesso">Senha inicial do acesso</label>
                    <input type="text" class="form-control" id="senha_acesso" name="senha_acesso" placeholder="mudar123">
                    <div class="form-text">Usa o e-mail informado acima como login.</div>
                </div>
            </div>
        </div>

        <div class="border rounded p-3 bg-light mt-3" id="bloco-fornecedor">
            <h2 class="h6 mb-3"><i class="bi bi-truck"></i> Dados do fornecedor</h2>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label" for="codigo_fornecedor">Código</label>
                    <input type="text" class="form-control" id="codigo_fornecedor" name="codigo_fornecedor" value="{{ old('codigo_fornecedor', $fornecedor?->codigo_fornecedor) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="contato_comercial">Contato comercial</label>
                    <input type="text" class="form-control" id="contato_comercial" name="contato_comercial" value="{{ old('contato_comercial', $fornecedor?->contato_comercial) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="prazo_pagamento">Prazo de pagamento (dias)</label>
                    <input type="number" min="0" max="365" class="form-control" id="prazo_pagamento" name="prazo_pagamento" value="{{ old('prazo_pagamento', $fornecedor?->prazo_pagamento ?? 30) }}">
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <label class="form-label" for="observacoes">Observações gerais</label>
        <textarea class="form-control" id="observacoes" name="observacoes" rows="2">{{ old('observacoes', $pessoa->observacoes) }}</textarea>
    </div>

    <div class="col-md-4">
        <div class="form-check form-switch mt-4">
            <input class="form-check-input" type="checkbox" role="switch" id="ativo" name="ativo" value="1" {{ old('ativo', $pessoa->ativo ?? true) ? 'checked' : '' }}>
            <label class="form-check-label" for="ativo">Cadastro ativo</label>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between mt-4">
    <a href="{{ route('admin.pessoas.index') }}" class="btn btn-outline-secondary">Voltar</a>
    <button type="submit" class="btn btn-dark"><i class="bi bi-check-lg"></i> Salvar cadastro</button>
</div>

@push('scripts')
<script>
(function () {
    const selecao = document.getElementById('tipo_pessoa');
    const blocos = {
        cliente: document.getElementById('bloco-cliente'),
        funcionario: document.getElementById('bloco-funcionario'),
        fornecedor: document.getElementById('bloco-fornecedor'),
        usuario: null
    };

    function alternar() {
        const tipo = selecao.value;
        Object.entries(blocos).forEach(function ([chave, elemento]) {
            if (!elemento) return;
            elemento.style.display = (chave === tipo) ? '' : 'none';
        });
    }

    selecao.addEventListener('change', alternar);
    alternar();
})();
</script>
@endpush
