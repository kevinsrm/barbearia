@extends('layouts.install')

@section('title', 'Instalação concluída')

@section('content')
    <div class="text-center mb-4">
        <div class="display-4 text-success"><i class="bi bi-check-circle-fill"></i></div>
        <h1 class="h4 mt-2 mb-1">Instalação concluída com sucesso!</h1>
        <p class="text-muted mb-0">
            {{ $lock['app_name'] ?? 'Sua barbearia' }} já está pronta para usar.
        </p>
    </div>

    <div class="alert alert-success">
        <strong><i class="bi bi-key"></i> Seu acesso ao painel</strong>
        <ul class="mb-0 mt-2 small">
            <li><strong>Endereço:</strong> <code>{{ rtrim((string) config('app.url'), '/') }}/login</code></li>
            <li><strong>E-mail:</strong> <code>{{ $lock['admin_email'] ?? session('instalacao.email') }}</code></li>
            <li><strong>Senha:</strong> a que você cadastrou no formulário anterior.</li>
        </ul>
    </div>

    <div class="card border mb-3">
        <div class="card-body">
            <h2 class="h6 mb-2"><i class="bi bi-shield-exclamation text-danger"></i> Último passo (importante)</h2>
            <ol class="small text-muted mb-0 ps-3">
                <li>Apague a pasta <code>install/</code> se ela existir no seu servidor (arquivos de instalação antiga).</li>
                <li>Confirme se o domínio aponta para a pasta <code>public/</code> do sistema — ou deixe o <code>.htaccess</code> da raiz fazer isso.</li>
                <li>Guarde uma cópia do arquivo <code>.env</code>: ele contém a chave e os dados do banco.</li>
            </ol>
        </div>
    </div>

    <div class="row g-2">
        <div class="col-md-6 d-grid"><a class="btn btn-dark btn-lg" href="{{ route('login') }}"><i class="bi bi-box-arrow-in-right"></i> Entrar no painel</a></div>
        <div class="col-md-6 d-grid"><a class="btn btn-outline-secondary btn-lg" href="{{ route('site.index') }}"><i class="bi bi-globe"></i> Ver o site público</a></div>
    </div>

    <div class="card mt-4">
        <div class="card-header bg-white"><strong>Primeiros passos no painel</strong></div>
        <div class="card-body small text-muted">
            <ol class="mb-0 ps-3">
                <li><strong>Configurações</strong>: complete endereço, telefone, WhatsApp e horário de funcionamento.</li>
                <li><strong>Profissionais</strong>: cadastre em <em>Pessoas → Funcionários</em> e defina o percentual de comissão.</li>
                <li><strong>Serviços</strong>: cadastre cortes, barba e combos com preço e duração.</li>
                <li><strong>Produtos</strong>: cadastre pomadas, óleos etc. com estoque inicial.</li>
                <li><strong>Venda</strong>: use o menu <em>Vendas → Nova venda</em> para fechar um atendimento.</li>
            </ol>
        </div>
    </div>
@endsection
