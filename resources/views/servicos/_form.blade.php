<div class="row g-3">
    <div class="col-md-8">
        <label class="form-label" for="nome">Nome do serviço *</label>
        <input type="text" class="form-control @error('nome') is-invalid @enderror" id="nome" name="nome" value="{{ old('nome', $servico->nome) }}" required>
        @error('nome')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4">
        <label class="form-label" for="servico_categoria_id">Categoria</label>
        <select class="form-select @error('servico_categoria_id') is-invalid @enderror" id="servico_categoria_id" name="servico_categoria_id">
            <option value="">— Sem categoria —</option>
            @foreach ($categorias as $categoria)
                <option value="{{ $categoria->id }}" {{ (int) old('servico_categoria_id', $servico->servico_categoria_id) === $categoria->id ? 'selected' : '' }}>{{ $categoria->nome }}</option>
            @endforeach
        </select>
        @error('servico_categoria_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-3">
        <label class="form-label" for="valor">Valor (R$) *</label>
        <input type="number" step="0.01" min="0" class="form-control @error('valor') is-invalid @enderror" id="valor" name="valor" value="{{ old('valor', $servico->valor ?? 0) }}" required>
        @error('valor')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-3">
        <label class="form-label" for="duracao">Duração (minutos) *</label>
        <input type="number" min="5" max="480" step="5" class="form-control @error('duracao') is-invalid @enderror" id="duracao" name="duracao" value="{{ old('duracao', $servico->duracao ?? 30) }}" required>
        @error('duracao')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-3">
        <label class="form-label" for="percentual_comissao">Comissão (%)</label>
        <input type="number" step="0.01" min="0" max="100" class="form-control" id="percentual_comissao" name="percentual_comissao"
               value="{{ old('percentual_comissao', $servico->percentual_comissao ?? config('barbearia.comissao_padrao')) }}">
        <div class="form-text">Se ficar 0, vale a comissão do profissional.</div>
    </div>

    <div class="col-md-3 d-flex align-items-end gap-3">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" role="switch" id="ativo" name="ativo" value="1" {{ old('ativo', $servico->ativo ?? true) ? 'checked' : '' }}>
            <label class="form-check-label" for="ativo">Ativo</label>
        </div>
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" role="switch" id="agendamento_online" name="agendamento_online" value="1" {{ old('agendamento_online', $servico->agendamento_online ?? true) ? 'checked' : '' }}>
            <label class="form-check-label" for="agendamento_online">No site</label>
        </div>
    </div>

    <div class="col-12">
        <label class="form-label" for="descricao">Descrição</label>
        <textarea class="form-control" id="descricao" name="descricao" rows="2" maxlength="500" placeholder="Aparece no site">{{ old('descricao', $servico->descricao) }}</textarea>
    </div>
</div>

<div class="d-flex justify-content-between mt-4">
    <a href="{{ route('admin.servicos.index') }}" class="btn btn-outline-secondary">Voltar</a>
    <button type="submit" class="btn btn-dark"><i class="bi bi-check-lg"></i> Salvar serviço</button>
</div>
