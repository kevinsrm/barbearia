<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Instalação') · Sistema de Barbearia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #1f1f1f 0%, #3a3a3a 100%); min-height: 100vh; }
        .cartao-instalacao { max-width: 860px; margin: 2rem auto; }
        .card { border: 0; box-shadow: 0 10px 30px rgba(0,0,0,.25); }
        .marca { color: #b8860b; }
        .passo { border-left: 3px solid #e9ecef; padding-left: .9rem; margin-bottom: 1.1rem; }
        .passo.ativo { border-left-color: #b8860b; }
        .requisito-ok { color: #198754; }
        .requisito-erro { color: #dc3545; }
        code { background: #f1f3f5; padding: .1rem .35rem; border-radius: .25rem; }
    </style>
    @stack('styles')
</head>
<body>
    <div class="container">
        <div class="cartao-instalacao">
            <div class="text-center text-white mb-3">
                <h1 class="h4 mb-1"><i class="bi bi-scissors marca"></i> Sistema de Gestão para Barbearias</h1>
                <div class="small opacity-75">Assistente de instalação</div>
            </div>

            <div class="card">
                <div class="card-body p-4 p-md-5">
                    @include('partials.alerts')

                    @yield('content')
                </div>
            </div>

            <p class="text-center text-white-50 small mt-3 mb-4">
                Precisa de ajuda? Consulte o arquivo <code class="text-dark">INSTALACAO.md</code> que acompanha o sistema.
            </p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
