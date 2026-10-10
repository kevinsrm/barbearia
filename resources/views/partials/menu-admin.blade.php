<ul class="nav nav-pills flex-column gap-1">
    <li class="titulo-menu">Operação</li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
    </li>
    @can('gerenciar-agendamentos')
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.agendamentos.*') ? 'active' : '' }}" href="{{ route('admin.agendamentos.index') }}">
                <i class="bi bi-calendar-week"></i> Agenda
            </a>
        </li>
    @endcan
    @can('gerenciar-vendas')
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.vendas.*') ? 'active' : '' }}" href="{{ route('admin.vendas.index') }}">
                <i class="bi bi-bag-check"></i> Vendas
            </a>
        </li>
    @endcan
    @can('gerenciar-compras')
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.compras.*') ? 'active' : '' }}" href="{{ route('admin.compras.index') }}">
                <i class="bi bi-box-seam"></i> Compras
            </a>
        </li>
    @endcan

    <li class="titulo-menu">Cadastros</li>
    @can('gerenciar-pessoas')
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.pessoas.*') ? 'active' : '' }}" href="{{ route('admin.pessoas.index') }}">
                <i class="bi bi-people"></i> Pessoas
            </a>
        </li>
    @endcan
    @can('gerenciar-servicos')
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.servicos.*') ? 'active' : '' }}" href="{{ route('admin.servicos.index') }}">
                <i class="bi bi-scissors"></i> Serviços
            </a>
        </li>
    @endcan
    @can('gerenciar-produtos')
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.produtos.*') ? 'active' : '' }}" href="{{ route('admin.produtos.index') }}">
                <i class="bi bi-box"></i> Produtos
            </a>
        </li>
    @endcan
    @can('gerenciar-usuarios')
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.usuarios.*') ? 'active' : '' }}" href="{{ route('admin.usuarios.index') }}">
                <i class="bi bi-shield-lock"></i> Usuários
            </a>
        </li>
    @endcan

    <li class="titulo-menu">Financeiro</li>
    @can('gerenciar-financeiro')
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.financeiro.index') ? 'active' : '' }}" href="{{ route('admin.financeiro.index') }}">
                <i class="bi bi-cash-stack"></i> Resumo
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.financeiro.contas') ? 'active' : '' }}" href="{{ route('admin.financeiro.contas') }}">
                <i class="bi bi-journals"></i> Contas
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.financeiro.comissoes') ? 'active' : '' }}" href="{{ route('admin.financeiro.comissoes') }}">
                <i class="bi bi-percent"></i> Comissões
            </a>
        </li>
    @endcan
    @can('ver-relatorios')
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.relatorios.*') ? 'active' : '' }}" href="{{ route('admin.relatorios.index') }}">
                <i class="bi bi-graph-up"></i> Relatórios
            </a>
        </li>
    @endcan

    @can('acesso-barbeiro')
        <li class="titulo-menu">Profissional</li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.barbeiro.*') ? 'active' : '' }}" href="{{ route('admin.barbeiro.index') }}">
                <i class="bi bi-person-badge"></i> Minha agenda
            </a>
        </li>
    @endcan
</ul>
