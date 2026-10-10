<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Início') · {{ config('barbearia.nome') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root { --marca: #b8860b; }
        body { background-color: #fbfbfb; }
        .navbar-brand { font-weight: 700; letter-spacing: .5px; }
        .btn-marca { background-color: var(--marca); border-color: var(--marca); color: #fff; }
        .btn-marca:hover { background-color: #9c7209; border-color: #9c7209; color: #fff; }
        .text-marca { color: var(--marca) !important; }
        .hero { background: linear-gradient(135deg, #1f1f1f 0%, #3a3a3a 100%); color: #fff; }
        .card-servico { transition: transform .15s ease-in-out, box-shadow .15s ease-in-out; }
        .card-servico:hover { transform: translateY(-3px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.08); }
        footer { background-color: #1f1f1f; color: #adb5bd; }
        footer a { color: #e9ecef; text-decoration: none; }
    </style>
    @stack('styles')
</head>
<body class="d-flex flex-column min-vh-100">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="{{ route('site.index') }}">
                <i class="bi bi-scissors text-marca"></i> {{ config('barbearia.nome') }}
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuSite" aria-controls="menuSite" aria-expanded="false" aria-label="Alternar menu">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="menuSite">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('site.index') ? 'active' : '' }}" href="{{ route('site.index') }}">Início</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('site.servicos') ? 'active' : '' }}" href="{{ route('site.servicos') }}">Serviços</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('site.sobre') ? 'active' : '' }}" href="{{ route('site.sobre') }}">Sobre</a></li>
                    @auth
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i> {{ auth()->user()->nome }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                @can('acesso-painel')
                                    <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2"></i> Painel</a></li>
                                @endcan
                                @can('acesso-barbeiro')
                                    <li><a class="dropdown-item" href="{{ route('admin.barbeiro.index') }}"><i class="bi bi-calendar-week"></i> Minha agenda</a></li>
                                @endcan
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button class="dropdown-item text-danger" type="submit"><i class="bi bi-box-arrow-right"></i> Sair</button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @else
                        <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Entrar</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('register') }}">Criar conta</a></li>
                    @endauth
                    <li class="nav-item ms-lg-2">
                        <a class="btn btn-marca btn-sm px-3" href="{{ route('site.agendamento') }}">
                            <i class="bi bi-calendar-check"></i> Agendar
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="flex-grow-1">
        <div class="container mt-3">
            @include('partials.alerts')
        </div>
        @yield('content')
    </main>

    <footer class="mt-5 py-4">
        <div class="container">
            <div class="row gy-3">
                <div class="col-md-4">
                    <h5 class="text-white"><i class="bi bi-scissors text-marca"></i> {{ config('barbearia.nome') }}</h5>
                    <p class="mb-1 small">{{ config('barbearia.endereco') }}</p>
                    <p class="mb-0 small">{{ config('barbearia.cidade') }}</p>
                </div>
                <div class="col-md-4">
                    <h6 class="text-white">Atendimento</h6>
                    <p class="mb-1 small">
                        <i class="bi bi-clock"></i>
                        {{ config('barbearia.agenda.abertura') }} às {{ config('barbearia.agenda.fechamento') }}
                    </p>
                    <p class="mb-0 small"><i class="bi bi-telephone"></i> {{ config('barbearia.telefone') }}</p>
                    <p class="mb-0 small"><i class="bi bi-envelope"></i> {{ config('barbearia.email') }}</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <a class="btn btn-marca" href="{{ route('site.agendamento') }}"><i class="bi bi-calendar-check"></i> Agendar horário</a>
                    <p class="small mt-3 mb-0">&copy; {{ date('Y') }} {{ config('barbearia.nome') }}. Todos os direitos reservados.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
