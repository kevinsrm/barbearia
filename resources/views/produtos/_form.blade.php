<div class="row g-3">
    <div class="col-md-8">
        <label class="form-label" for="nome">Nome do produto *</label>
        <input type="text" class="form-control @error('nome') is-invalid @enderror" id="nome" name="nome" value="{{ old('nome', $produto->nome) }}" required>
        @error('nome')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4">
        <label class="form-label" for="categoria_id">Categoria</label>
        <select class="form-select @error('categoria_id') is-invalid @enderror" id="categoria_id" name="categoria_id">
            <option value="">— Sem categoria —</option>
            @foreach ($categorias as $categoria)
                <option value="{{ $categoria->id }}" {{ (int) old('categoria_id', $produto->categoria_id) === $categoria->id ? 'selected' : '' }}>{{ $categoria->nome }}</option>
            @endforeach
        </select>
        @error('categoria_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4">
        <label class="form-label" for="codigo">Código interno</label>
        <input type="text" class="form-control @error('codigo') is-invalid @enderror" id="codigo" name="codigo" value="{{ old('codigo', $produto->codigo) }}">
        @error('codigo')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4">
        <label class="form-label" for="codigo_barras">Código de barras</label>
        <input type="text" class="form-control" id="codigo_barras" name="codigo_barras" value="{{ old('codigo_barras', $produto->codigo_barras) }}">
    </div>

    <div class="col-md-4">
        <label class="form-label" for="unidade">Unidade *</label>
        <select class="form-select" id="unidade" name="unidade" required>
            @foreach (['un' => 'Unidade', 'cx' => 'Caixa', 'pct' => 'Pacote', 'fr' => 'Frasco', 'kg' => 'Quilo', 'lt' => 'Litro'] as $sigla => $rotulo)
                <option value="{{ $sigla }}" {{ old('unidade', $produto->unidade ?: 'un') === $sigla ? 'selected' : '' }}>{{ $rotulo }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-3">
        <label class="form-label" for="preco_custo">Preço de custo (R$) *</label>
        <input type="number" step="0.01" min="0" class="form-control @error('preco_custo') is-invalid @enderror" id="preco_custo" name="preco_custo" value="{{ old('preco_custo', $produto->preco_custo ?? 0) }}" required>
        @error('preco_custo')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-3">
        <label class="form-label" for="preco_venda">Preço de venda (R$) *</label>
        <input type="number" step="0.01" min="0" class="form-control @error('preco_venda') is-invalid @enderror" id="preco_venda" name="preco_venda" value="{{ old('preco_venda', $produto->preco_venda ?? 0) }}" required>
        @error('preco_venda')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-3">
        <label class="form-label" for="estoque">Estoque atual</label>
        <input type="number" min="0" class="form-control" id="estoque" name="estoque" value="{{ old('estoque', $produto->estoque ?? 0) }}" {{ $produto->exists ? 'readonly' : '' }}>
        @if ($produto->exists)
            <div class="form-text">Use "Ajustar estoque" na listagem para alterar.</div>
        @endif
    </div>

    <div class="col-md-3">
        <label class="form-label" for="estoque_minimo">Estoque mínimo</label>
        <input type="number" min="0" class="form-control" id="estoque_minimo" name="estoque_minimo" value="{{ old('estoque_minimo', $produto->estoque_minimo ?? 0) }}">
    </div>

    <div class="col-12">
        <label class="form-label" for="descricao">Descrição</label>
        <textarea class="form-control" id="descricao" name="descricao" rows="2" maxlength="1000">{{ old('descricao', $produto->descricao) }}</textarea>
    </div>

    <div class="col-md-4">
        <div class="form-check form-switch mt-3">
            <input class="form-check-input" type="checkbox" role="switch" id="ativo" name="ativo" value="1" {{ old('ativo', $produto->ativo ?? true) ? 'checked' : '' }}>
            <label class="form-check-label" for="ativo">Produto ativo</label>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between mt-4">
    <a href="{{ route('admin.produtos.index') }}" class="btn btn-outline-secondary">Voltar</a>
    <button type="submit" class="btn btn-dark"><i class="bi bi-check-lg"></i> Salvar produto</button>
</div>
