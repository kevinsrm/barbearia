<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label" for="nome">Nome de exibição *</label>
        <input type="text" class="form-control @error('nome') is-invalid @enderror" id="nome" name="nome" value="{{ old('nome', $usuario->nome) }}" required>
        @error('nome')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label" for="email">E-mail (login) *</label>
        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $usuario->email) }}" required>
        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label" for="password">Senha {{ $usuario->exists ? '(deixe em branco para manter)' : '*' }}</label>
        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" {{ $usuario->exists ? '' : 'required' }}>
        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label" for="password_confirmation">Confirmar senha</label>
        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
    </div>

    <div class="col-md-6">
        <label class="form-label" for="role">Perfil de acesso *</label>
        <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
            @foreach ($papeis as $valor => $rotulo)
                <option value="{{ $valor }}" {{ old('role', $usuario->role ?: 'barbeiro') === $valor ? 'selected' : '' }}>{{ $rotulo }}</option>
            @endforeach
        </select>
        @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <div class="form-text">Administrador tem acesso total; os demais seguem as permissões configuradas.</div>
    </div>

    <div class="col-md-6">
        <label class="form-label" for="pessoa_id">Vincular a um cadastro (pessoa)</label>
        <select class="form-select" id="pessoa_id" name="pessoa_id">
            <option value="">— Não vincular —</option>
            @foreach ($pessoas as $pessoa)
                <option value="{{ $pessoa->id }}" {{ (int) old('pessoa_id', $usuario->pessoa_id) === $pessoa->id ? 'selected' : '' }}>
                    {{ $pessoa->nome }} ({{ $pessoa->tipo_pessoa }})
                </option>
            @endforeach
        </select>
        <div class="form-text">Vincule ao cadastro do profissional para liberar a agenda individual dele.</div>
    </div>

    <div class="col-md-4">
        <div class="form-check form-switch mt-4">
            <input class="form-check-input" type="checkbox" role="switch" id="ativo" name="ativo" value="1" {{ old('ativo', $usuario->ativo ?? true) ? 'checked' : '' }}>
            <label class="form-check-label" for="ativo">Acesso ativo</label>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between mt-4">
    <a href="{{ route('admin.usuarios.index') }}" class="btn btn-outline-secondary">Voltar</a>
    <button type="submit" class="btn btn-dark"><i class="bi bi-check-lg"></i> Salvar usuário</button>
</div>
