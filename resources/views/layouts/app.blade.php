<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#3b6cff">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="inServices">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <title>{{ $titulo ?? config('app.name') }}</title>
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    <link rel="manifest" href="/manifest.webmanifest">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/2.1.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<div class="app-shell">
    <aside class="app-sidebar" id="appSidebar" aria-label="Navegacao principal">
        <a class="brand" href="{{ auth()->user()?->tipo === 'administrador_geral' ? route('admin.painel') : route('prestador.painel') }}">
            <span class="brand-mark">iS</span>
            <span>inServices</span>
        </a>
        <nav class="nav flex-column gap-1">
            @if(auth()->user()?->tipo === 'administrador_geral')
                <span class="nav-section">ADMIN MASTER</span>
                <a class="nav-link {{ request()->routeIs('admin.painel') ? 'active' : '' }}" href="{{ route('admin.painel') }}"><span class="nav-icon app-icon">D</span><span>Dashboard</span></a>
                <a class="nav-link {{ request()->routeIs('admin.prestadores.*') ? 'active' : '' }}" href="{{ route('admin.prestadores.index') }}"><span class="nav-icon app-icon">P</span><span>Prestadores</span></a>
                <a class="nav-link {{ request()->routeIs('admin.planos.*') ? 'active' : '' }}" href="{{ route('admin.planos.index') }}"><span class="nav-icon app-icon">PL</span><span>Planos</span></a>
                <a class="nav-link {{ request()->routeIs('admin.mensalidades.*') ? 'active' : '' }}" href="{{ route('admin.mensalidades.index') }}"><span class="nav-icon app-icon">M</span><span>Mensalidades</span></a>
                <a class="nav-link {{ request()->routeIs('conta.*') ? 'active' : '' }}" href="{{ route('conta.edit') }}"><span class="nav-icon app-icon">C</span><span>Conta</span></a>
            @else
                <span class="nav-section">PRESTADOR</span>
                <a class="nav-link {{ request()->routeIs('prestador.painel') ? 'active' : '' }}" href="{{ route('prestador.painel') }}"><span class="nav-icon app-icon">D</span><span>Dashboard</span></a>
                <a class="nav-link {{ request()->routeIs('prestador.servicos.*') ? 'active' : '' }}" href="{{ route('prestador.servicos.index') }}"><span class="nav-icon app-icon">S</span><span>Servicos</span></a>
                <a class="nav-link {{ request()->routeIs('prestador.agenda.*') ? 'active' : '' }}" href="{{ route('prestador.agenda.index') }}"><span class="nav-icon app-icon">A</span><span>Agenda</span></a>
                <a class="nav-link {{ request()->routeIs('prestador.clientes.*') ? 'active' : '' }}" href="{{ route('prestador.clientes.index') }}"><span class="nav-icon app-icon">CL</span><span>Clientes</span></a>
                <a class="nav-link {{ request()->routeIs('prestador.assinatura.*') ? 'active' : '' }}" href="{{ route('prestador.assinatura.edit') }}"><span class="nav-icon app-icon">AS</span><span>Assinatura</span></a>
                <a class="nav-link {{ request()->routeIs('prestador.mensagens-whatsapp.*') ? 'active' : '' }}" href="{{ route('prestador.mensagens-whatsapp.edit') }}"><span class="nav-icon app-icon">W</span><span>WhatsApp</span></a>
                <a class="nav-link {{ request()->routeIs('publico.*') ? 'active' : '' }}" href="{{ route('publico.agendamento.index', ['prestador' => optional($prestador ?? auth()->user()?->perfilPrestador)->uuid_publico ?? 'demo']) }}"><span class="nav-icon app-icon">L</span><span>Link</span></a>
                <a class="nav-link {{ request()->routeIs('conta.*') ? 'active' : '' }}" href="{{ route('conta.edit') }}"><span class="nav-icon app-icon">C</span><span>Conta</span></a>
            @endif
        </nav>
    </aside>
    <div class="sidebar-backdrop" data-fechar-menu></div>
    <main class="app-main">
        <header class="app-header">
            <button class="sidebar-toggle" type="button" data-alternar-menu aria-controls="appSidebar" aria-expanded="false" aria-label="Abrir navegacao">
                <span aria-hidden="true"></span>
            </button>
            <div class="header-copy">
                <h1>{{ $titulo ?? 'Painel' }}</h1>
                @isset($subtitulo)<p>{{ $subtitulo }}</p>@endisset
            </div>
            <div class="header-actions d-flex gap-2 align-items-center">
                <button class="btn btn-template-primary btn-sm" type="button" data-instalar-pwa>Instalar app</button>
                @auth
                    <form method="post" action="{{ route('logout') }}">
                        @csrf
                        <button class="btn btn-outline-secondary btn-sm" type="submit">Sair</button>
                    </form>
                @endauth
            </div>
        </header>
        <section class="app-content">
            @yield('content')
        </section>
    </main>
</div>
<div class="boxalert-backdrop" data-boxalert-backdrop hidden>
    <div class="boxalert-dialog" role="dialog" aria-modal="true" aria-labelledby="boxalert-titulo" aria-describedby="boxalert-mensagem">
        <div class="boxalert-icon" aria-hidden="true">!</div>
        <h2 id="boxalert-titulo">Confirmar acao</h2>
        <p id="boxalert-mensagem" data-boxalert-mensagem>Deseja continuar?</p>
        <div class="boxalert-actions">
            <button class="btn btn-outline-secondary" type="button" data-boxalert-cancelar>Cancelar</button>
            <button class="btn btn-template-primary" type="button" data-boxalert-confirmar>Confirmar</button>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
@stack('scripts')
</body>
</html>
