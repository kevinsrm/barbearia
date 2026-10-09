@extends('layouts.install')

@section('title', 'Sistema já instalado')

@section('content')
    <div class="text-center mb-4">
        <div class="display-5 text-dark"><i class="bi bi-lock-fill"></i></div>
        <h1 class="h4 mt-2 mb-1">Este sistema já está instalado</h1>
        <p class="text-muted mb-0">
            O assistente de instalação fica bloqueado depois da primeira instalação, para proteger seus dados.
        </p>
    </div>

    <div class="card border mb-3">
        <div class="card-body small">
            <table class="table table-sm mb-0">
                <tbody>
                    <tr><th style="width: 40%">Instalado em</th><td>{{ $lock['instalado_em'] ?? '—' }}</td></tr>
                    <tr><th>Barbearia</th><td>{{ $lock['app_name'] ?? config('barbearia.nome') }}</td></tr>
                    <tr><th>Banco de dados</th><td>{{ strtoupper((string) ($lock['db_driver'] ?? config('database.default'))) }}</td></tr>
                    <tr><th>Administrador</th><td>{{ $lock['admin_email'] ?? '—' }}</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="alert alert-light border small">
        <strong>Precisa reinstalar?</strong><br>
        Apague o arquivo <code>storage/app/installed.lock</code> (pelo Gerenciador de Arquivos do cPanel ou por FTP)
        e recarregue esta página. Atenção: reinstalar com a opção "recriar tabelas" apaga todos os dados cadastrados.
    </div>

    <div class="row g-2">
        <div class="col-md-6 d-grid"><a class="btn btn-dark" href="{{ route('login') }}"><i class="bi bi-box-arrow-in-right"></i> Ir para o login</a></div>
        <div class="col-md-6 d-grid"><a class="btn btn-outline-secondary" href="{{ route('site.index') }}"><i class="bi bi-globe"></i> Ver o site</a></div>
    </div>
@endsection
