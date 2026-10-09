@extends('layouts.install')

@section('title', 'Instalação')

@section('content')
    @php $driver = old('db_driver', $valores['db_driver']); @endphp

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="passo {{ empty($problemas) ? 'ativo' : '' }}">
                <h2 class="h6 mb-1"><span class="badge bg-dark me-1">1</span> Verificação do servidor</h2>
                <p class="small text-muted mb-2">O que o seu PHP precisa ter para o sistema funcionar.</p>
                <ul class="list-unstyled small mb-0">
                    @foreach ($requisitos as $rotulo => $ok)
                        <li class="{{ $ok ? 'requisito-ok' : 'requisito-erro' }}">
                            <i class="bi bi-{{ $ok ? 'check-circle-fill' : 'x-circle-fill' }}"></i> {{ $rotulo }}
                        </li>
                    @endforeach
                </ul>
                <p class="small text-muted mt-2 mb-0">
                    <i class="bi bi-info-circle"></i> Precisa de <strong>MySQL</strong> ou <strong>SQLite</strong> — não dos dois.
                </p>
            </div>

            <div class="passo ativo">
                <h2 class="h6 mb-1"><span class="badge bg-dark me-1">2</span> Banco e acesso</h2>
                <p class="small text-muted mb-0">Preencha o formulário ao lado. Leva menos de 2 minutos.</p>
            </div>

            <div class="passo">
                <h2 class="h6 mb-1"><span class="badge bg-dark me-1">3</span> Pronto!</h2>
                <p class="small text-muted mb-0">Você entra no painel, cadastra seus serviços e começa a vender.</p>
            </div>

            <div class="alert alert-light border small mb-0">
                <strong>Onde encontro os dados do banco?</strong><br>
                No cPanel, abra <em>Banco de dados MySQL</em>, crie o banco e o usuário e anote
                <em>nome do banco</em>, <em>usuário</em> e <em>senha</em>. Na maioria das hospedagens o
                <em>host</em> é <code>localhost</code>.
            </div>
        </div>

        <div class="col-lg-8">
            @if (! empty($problemas))
                <div class="alert alert-danger">
                    <strong><i class="bi bi-exclamation-octagon"></i> O servidor ainda não está pronto:</strong>
                    <ul class="mb-0 mt-1 small">
                        @foreach ($problemas as $problema)
                            <li>{{ $problema }}</li>
                        @endforeach
                    </ul>
                    <hr>
                    <div class="small mb-0">
                        Peça à sua hospedagem para habilitar esses itens no PHP (versão {{ PHP_VERSION }}) e recarregue esta página.
                    </div>
                </div>
            @endif

            @if (session('saida_migrate'))
                <div class="alert alert-warning small">
                    <strong>Saída do instalador:</strong>
                    <pre class="mb-0 mt-1" style="white-space: pre-wrap">{{ session('saida_migrate') }}</pre>
                </div>
            @endif

            <form action="{{ route('install.store') }}" method="POST">
                @csrf

                <fieldset class="border rounded p-3 mb-3">
                    <legend class="h6 float-none w-auto px-2 mb-0">Banco de dados</legend>

                    <div class="mb-3">
                        <label class="form-label" for="db_driver">Tipo de banco <span class="text-danger">*</span></label>
                        <select class="form-select" id="db_driver" name="db_driver">
                            @foreach ($drivers as $valor => $rotulo)
                                <option value="{{ $valor }}" {{ $driver === $valor ? 'selected' : '' }}>{{ $rotulo }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div id="camposMysql" class="row g-3 {{ $driver === 'mysql' ? '' : 'd-none' }}">
                        <div class="col-md-8">
                            <label class="form-label" for="db_host">Servidor (host)</label>
                            <input class="form-control" id="db_host" name="db_host" value="{{ old('db_host', $valores['db_host']) }}" placeholder="localhost">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="db_port">Porta</label>
                            <input type="number" min="1" max="65535" class="form-control" id="db_port" name="db_port" value="{{ old('db_port', $valores['db_port']) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="db_database">Nome do banco <span class="text-danger">*</span></label>
                            <input class="form-control" id="db_database" name="db_database" value="{{ old('db_database', $valores['db_database']) }}" placeholder="usuario_barbearia">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="db_username">Usuário do banco</label>
                            <input class="form-control" id="db_username" name="db_username" value="{{ old('db_username', $valores['db_username']) }}" autocomplete="off">
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="db_password">Senha do banco</label>
                            <input type="password" class="form-control" id="db_password" name="db_password" value="{{ old('db_password', $valores['db_password']) }}" autocomplete="new-password">
                        </div>
                    </div>

                    <div id="camposSqlite" class="{{ $driver === 'sqlite' ? '' : 'd-none' }}">
                        <label class="form-label" for="db_database_sqlite">Arquivo do banco</label>
                        <input class="form-control" id="db_database_sqlite" name="db_database_sqlite_disabled" value="database/database.sqlite" disabled>
                        <div class="form-text">
                            O SQLite usa um arquivo dentro da pasta <code>database/</code>. Se ele não existir, será criado automaticamente
                            (a pasta precisa ter permissão de escrita).
                        </div>
                    </div>
                </fieldset>

                <fieldset class="border rounded p-3 mb-3">
                    <legend class="h6 float-none w-auto px-2 mb-0">Sua barbearia</legend>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="app_name">Nome da barbearia <span class="text-danger">*</span></label>
                            <input class="form-control @error('app_name') is-invalid @enderror" id="app_name" name="app_name"
                                   value="{{ old('app_name', $valores['app_name']) }}" required maxlength="80">
                            @error('app_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="app_url">Endereço do site</label>
                            <input type="url" class="form-control @error('app_url') is-invalid @enderror" id="app_url" name="app_url"
                                   value="{{ old('app_url', $valores['app_url']) }}" placeholder="https://seudominio.com.br">
                            @error('app_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </fieldset>

                <fieldset class="border rounded p-3 mb-3">
                    <legend class="h6 float-none w-auto px-2 mb-0">Acesso do administrador</legend>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="admin_nome">Seu nome <span class="text-danger">*</span></label>
                            <input class="form-control @error('admin_nome') is-invalid @enderror" id="admin_nome" name="admin_nome"
                                   value="{{ old('admin_nome', $valores['admin_nome']) }}" required minlength="3" maxlength="120">
                            @error('admin_nome')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="admin_email">E-mail de acesso <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('admin_email') is-invalid @enderror" id="admin_email" name="admin_email"
                                   value="{{ old('admin_email', $valores['admin_email']) }}" required maxlength="180">
                            @error('admin_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="admin_senha">Senha <span class="text-danger">*</span></label>
                            <input type="password" class="form-control @error('admin_senha') is-invalid @enderror" id="admin_senha" name="admin_senha"
                                   required minlength="6" autocomplete="new-password">
                            @error('admin_senha')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="admin_senha_confirmation">Confirme a senha <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="admin_senha_confirmation" name="admin_senha_confirmation"
                                   required minlength="6" autocomplete="new-password">
                        </div>
                    </div>
                </fieldset>

                <fieldset class="border rounded p-3 mb-3">
                    <legend class="h6 float-none w-auto px-2 mb-0">Opções</legend>

                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" role="switch" id="dados_demo" name="dados_demo" value="1" {{ old('dados_demo') ? 'checked' : '' }}>
                        <label class="form-check-label" for="dados_demo">
                            Incluir <strong>dados de demonstração</strong> (clientes, serviços, produtos e vendas de exemplo)
                            <span class="d-block small text-muted">Ótimo para conhecer o sistema. Você pode apagar depois.</span>
                        </label>
                    </div>

                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" role="switch" id="recriar" name="recriar" value="1" {{ old('recriar') ? 'checked' : '' }}>
                        <label class="form-check-label" for="recriar">
                            <strong>Recriar as tabelas</strong> do zero (apaga tudo que existir no banco)
                            <span class="d-block small text-danger">Use apenas em instalação nova ou para refazer uma instalação com erro.</span>
                        </label>
                    </div>

                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="modo_debug" name="modo_debug" value="1" {{ old('modo_debug') ? 'checked' : '' }}>
                        <label class="form-check-label" for="modo_debug">
                            Modo de depuração (mostra detalhes de erros na tela)
                            <span class="d-block small text-muted">Deixe desligado em produção.</span>
                        </label>
                    </div>
                </fieldset>

                <div class="d-grid gap-2">
                    <button class="btn btn-dark btn-lg" type="submit" {{ empty($problemas) ? '' : 'disabled' }}>
                        <i class="bi bi-lightning-charge"></i> Instalar agora
                    </button>
                    @if (! empty($problemas))
                        <div class="small text-danger text-center">Corrija os itens do servidor (etapa 1) para liberar a instalação.</div>
                    @endif
                </div>
            </form>

            <div class="alert alert-light border small mt-3 mb-0">
                <i class="bi bi-shield-lock"></i>
                Por segurança, depois de instalar o sistema bloqueia esta página automaticamente.
                Se precisar reinstalar, apague o arquivo <code>storage/app/installed.lock</code>.
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    (function () {
        var seletor = document.getElementById('db_driver');
        if (!seletor) return;

        function alternar() {
            var mysql = seletor.value === 'mysql';
            document.getElementById('camposMysql').classList.toggle('d-none', !mysql);
            document.getElementById('camposSqlite').classList.toggle('d-none', mysql);

            ['db_host', 'db_port', 'db_database', 'db_username', 'db_password'].forEach(function (nome) {
                var campo = document.getElementById(nome);
                if (campo) { campo.disabled = !mysql; }
            });
        }

        seletor.addEventListener('change', alternar);
        alternar();
    })();
</script>
@endpush
