<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Sistema de Barbearia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { padding-top: 56px; }
        .sidebar { height: calc(100vh - 56px); overflow-y: auto; }
        .nav-link { border-radius: 5px; transition: all 0.3s; }
        .nav-link:hover { background-color: #f8f9fa; }
        .active { background-color: #e9ecef; font-weight: bold; }
        .card-header { background-color: #f8f9fa; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container">
            <a class="navbar-brand" href="/"><i class="bi bi-scissors"></i> Barbearia</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link {{ request()->is('/') ? 'active' : '' }}" href="/">Início</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->is('agendamento*') ? 'active' : '' }}" href="/agendamento">Agendamento</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->is('servicos*') ? 'active' : '' }}" href="/servicos">Serviços</a></li>
                    @auth
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                Painel
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="/dashboard">Dashboard</a></li>
                                @can('gerenciar-pessoas')
                                    <li><a class="dropdown-item" href="/pessoas">Pessoas</a></li>
                                @endcan
                                @can('gerenciar-servicos')
                                    <li><a class="dropdown-item" href="/servicos/manage">Gerenciar Serviços</a></li>
                                @endcan
                                @can('gerenciar-produtos')
                                    <li><a class="dropdown-item" href="/produtos">Produtos</a></li>
                                @endcan
                                @can('gerenciar-agendamentos')
                                    <li><a class="dropdown-item" href="/agendamentos">Agendamentos</a></li>
                                @endcan
                                @can('gerenciar-financeiro')
                                    <li><a class="dropdown-item" href="/financeiro">Financeiro</a></li>
                                @endcan
                                @can('acesso-barbeiro')
                                    <li><a class="dropdown-item" href="/barbeiro">Minha Agenda</a></li>
                                @endcan
                                <li><a class="dropdown-item" href="/relatorios">Relatórios</a></li>
                            </ul>
                        </li>
                    @endauth
                </ul>
                <div class="d-flex align-items-center">
                    @guest
                        <a href="/login" class="btn btn-outline-light me-2">Login</a>
                    @else
                        <span class="text-white me-3">Olá, {{ Auth::user()->pessoa->nome }}</span>
                        <form action="/logout" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-light">Sair</button>
                        </form>
                    @endguest
                </div>
            </div>
        </div>
    </nav>

    <div class="container">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        
        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>