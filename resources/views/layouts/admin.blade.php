<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Painel') · {{ config('barbearia.nome') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; }
        .navbar-top { background-color: #1f1f1f; }
        .sidebar { min-height: calc(100vh - 56px); background-color: #fff; border-right: 1px solid #e9ecef; padding-top: 1rem; }
        .sidebar .nav-link { color: #495057; border-radius: .375rem; padding: .5rem .75rem; font-size: .95rem; }
        .sidebar .nav-link:hover { background-color: #f1f3f5; }
        .sidebar .nav-link.active { background-color: #1f1f1f; color: #fff; }
        .sidebar .titulo-menu { font-size: .72rem; text-transform: uppercase; letter-spacing: .08em; color: #adb5bd; padding: .9rem .75rem .25rem; }
        .card { border: 0; box-shadow: 0 1px 3px rgba(0,0,0,.06); }
        .card-metrica .valor { font-size: 1.7rem; font-weight: 700; }
        .table > :not(caption) > * > * { padding: .65rem .75rem; }
        .badge-status { font-weight: 500; }
    </style>
    @stack('styles')
</head>
<body>
    <nav class="navbar navbar-expand navbar-dark navbar-top sticky-top">
        <div class="container-fluid px-3">
            <a class="navbar-brand fw-bold" href="{{ route('admin.dashboard') }}">
                <i class="bi bi-scissors"></i> {{ config('barbearia.nome') }}
                <span class="badge bg-secondary ms-2 d-none d-md-inline">Painel</span>
            </a>
            <button class="navbar-toggler d-md-none" type="button" data-bs-toggle="collapse" data-bs-target="#menuMobile" aria-label="Menu">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="navbar-nav ms-auto align-items-center">
                <a class="nav-link" href="{{ route('site.index') }}" target="_blank" title="Ver o site"><i class="bi bi-box-arrow-up-right"></i> <span class="d-none d-lg-inline">Ver site</span></a>
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle"></i> {{ auth()->user()->nome }}
                        <span class="badge bg-light text-dark ms-1">{{ auth()->user()->role_label }}</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        @can('gerenciar-usuarios')
                            <li><a class="dropdown-item" href="{{ route('admin.configuracoes') }}"><i class="bi bi-gear"></i> Configurações</a></li>
                        @endcan
                        <li><a class="dropdown-item" href="{{ route('admin.usuarios.index') }}"><i class="bi bi-people"></i> Usuários</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button class="dropdown-item text-danger" type="submit"><i class="bi bi-box-arrow-right"></i> Sair</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-2 sidebar d-none d-md-block">
                @include('partials.menu-admin')
            </nav>
            <main class="col-md-10 px-md-4 py-3">
                <div class="collapse d-md-none mb-3" id="menuMobile">
                    <div class="card card-body">
                        @include('partials.menu-admin')
                    </div>
                </div>

                @include('partials.alerts')

                @yield('content')
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
