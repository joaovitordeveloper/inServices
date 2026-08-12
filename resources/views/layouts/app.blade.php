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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
@php
    $notificacoesTopo = collect();
    $notificacoesNaoLidas = 0;
    $ultimaNotificacaoNaoLida = null;

    if (auth()->check()) {
        $notificacoesTopo = \App\Models\Notificacao::query()
            ->where('destinatario_type', \App\Models\User::class)
            ->where('destinatario_id', auth()->id())
            ->latest()
            ->limit(6)
            ->get();
        $notificacoesNaoLidas = \App\Models\Notificacao::query()
            ->where('destinatario_type', \App\Models\User::class)
            ->where('destinatario_id', auth()->id())
            ->whereNull('lida_em')
            ->count();
        $ultimaNotificacaoNaoLida = $notificacoesTopo->firstWhere('lida_em', null);
    }
@endphp
<div class="app-shell">
    <aside class="app-sidebar" id="appSidebar" aria-label="Navegacao principal">
        <a class="brand" href="{{ auth()->user()?->tipo === 'administrador_geral' ? route('admin.painel') : route('prestador.painel') }}">
            <span class="brand-mark">iS</span>
            <span>inServices</span>
        </a>
        <nav class="nav flex-column gap-1">
            @if(auth()->user()?->tipo === 'administrador_geral')
                <span class="nav-section">ADMIN MASTER</span>
                <a class="nav-link {{ request()->routeIs('admin.painel') ? 'active' : '' }}" href="{{ route('admin.painel') }}"><span class="nav-icon app-icon"><i class="fa-solid fa-chart-line"></i></span><span>Dashboard</span></a>
                <a class="nav-link {{ request()->routeIs('admin.prestadores.*') ? 'active' : '' }}" href="{{ route('admin.prestadores.index') }}"><span class="nav-icon app-icon"><i class="fa-solid fa-store"></i></span><span>Prestadores</span></a>
                <a class="nav-link {{ request()->routeIs('admin.planos.*') ? 'active' : '' }}" href="{{ route('admin.planos.index') }}"><span class="nav-icon app-icon"><i class="fa-solid fa-layer-group"></i></span><span>Planos</span></a>
                <a class="nav-link {{ request()->routeIs('admin.mensalidades.*') ? 'active' : '' }}" href="{{ route('admin.mensalidades.index') }}"><span class="nav-icon app-icon"><i class="fa-solid fa-file-invoice-dollar"></i></span><span>Mensalidades</span></a>
                <a class="nav-link {{ request()->routeIs('conta.*') ? 'active' : '' }}" href="{{ route('conta.edit') }}"><span class="nav-icon app-icon"><i class="fa-solid fa-user-gear"></i></span><span>Conta</span></a>
            @else
                <span class="nav-section">PRESTADOR</span>
                <a class="nav-link {{ request()->routeIs('prestador.painel') ? 'active' : '' }}" href="{{ route('prestador.painel') }}"><span class="nav-icon app-icon"><i class="fa-solid fa-gauge-high"></i></span><span>Dashboard</span></a>
                <a class="nav-link {{ request()->routeIs('prestador.servicos.*') ? 'active' : '' }}" href="{{ route('prestador.servicos.index') }}"><span class="nav-icon app-icon"><i class="fa-solid fa-briefcase"></i></span><span>Servicos</span></a>
                <a class="nav-link {{ request()->routeIs('prestador.profissionais.*') ? 'active' : '' }}" href="{{ route('prestador.profissionais.index') }}"><span class="nav-icon app-icon"><i class="fa-solid fa-user-tie"></i></span><span>Profissionais</span></a>
                <a class="nav-link {{ request()->routeIs('prestador.agenda.*') ? 'active' : '' }}" href="{{ route('prestador.agenda.index') }}"><span class="nav-icon app-icon"><i class="fa-solid fa-calendar-days"></i></span><span>Agenda</span></a>
                <a class="nav-link {{ request()->routeIs('prestador.clientes.*') ? 'active' : '' }}" href="{{ route('prestador.clientes.index') }}"><span class="nav-icon app-icon"><i class="fa-solid fa-users"></i></span><span>Clientes</span></a>
                <a class="nav-link {{ request()->routeIs('prestador.assinatura.*') ? 'active' : '' }}" href="{{ route('prestador.assinatura.edit') }}"><span class="nav-icon app-icon"><i class="fa-solid fa-credit-card"></i></span><span>Assinatura</span></a>
                <a class="nav-link {{ request()->routeIs('prestador.mensalidades.*') ? 'active' : '' }}" href="{{ route('prestador.mensalidades.index') }}"><span class="nav-icon app-icon"><i class="fa-solid fa-money-bill-wave"></i></span><span>Mensalidade</span></a>
                <a class="nav-link {{ request()->routeIs('prestador.mensagens-whatsapp.*') ? 'active' : '' }}" href="{{ route('prestador.mensagens-whatsapp.edit') }}"><span class="nav-icon app-icon"><i class="fa-brands fa-whatsapp"></i></span><span>WhatsApp</span></a>
                <a class="nav-link {{ request()->routeIs('publico.*') ? 'active' : '' }}" href="{{ route('publico.agendamento.index', ['prestador' => optional($prestador ?? auth()->user()?->perfilPrestador)->uuid_publico ?? 'demo']) }}"><span class="nav-icon app-icon"><i class="fa-solid fa-link"></i></span><span>Link</span></a>
                <a class="nav-link {{ request()->routeIs('conta.*') ? 'active' : '' }}" href="{{ route('conta.edit') }}"><span class="nav-icon app-icon"><i class="fa-solid fa-user-gear"></i></span><span>Conta</span></a>
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
                @auth
                    <div class="notification-menu" data-notification-menu data-notifications-url="{{ route('notificacoes.index') }}" data-notification-latest-id="{{ $notificacoesTopo->max('id') ?? 0 }}">
                        <button class="notification-bell" type="button" data-notification-toggle aria-label="Abrir notificacoes">
                            <i class="fa-solid fa-bell"></i>
                            <span data-notification-count @if($notificacoesNaoLidas === 0) hidden @endif>{{ $notificacoesNaoLidas }}</span>
                        </button>
                        <div class="notification-dropdown" data-notification-dropdown hidden>
                            <div class="notification-dropdown-header">
                                <strong>Notificacoes</strong>
                                <form method="post" action="{{ route('notificacoes.lidas') }}" data-notification-read-form @if($notificacoesNaoLidas === 0) hidden @endif>
                                    @csrf
                                    @method('patch')
                                    <button type="submit">Marcar lidas</button>
                                </form>
                            </div>
                            <div class="notification-list" data-notification-list>
                                @forelse($notificacoesTopo as $notificacao)
                                    <a class="notification-item {{ $notificacao->lida_em ? '' : 'unread' }}" href="{{ $notificacao->url ?? '#' }}">
                                        <strong>{{ $notificacao->titulo }}</strong>
                                        <span>{{ $notificacao->corpo }}</span>
                                        <small>{{ $notificacao->created_at->format('d/m H:i') }}</small>
                                    </a>
                                @empty
                                    <div class="notification-empty">Nenhuma notificacao por enquanto.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                @endauth
                <button class="btn btn-template-primary btn-sm" type="button" data-instalar-pwa>Instalar app</button>
                @auth
                    <form method="post" action="{{ route('logout') }}">
                        @csrf
                        <button class="btn btn-outline-secondary btn-sm" type="submit">Sair</button>
                    </form>
                @endauth
            </div>
        </header>
        @auth
            <div class="notification-permission-banner" data-notification-permission hidden @if($ultimaNotificacaoNaoLida) data-latest-title="{{ $ultimaNotificacaoNaoLida->titulo }}" data-latest-body="{{ $ultimaNotificacaoNaoLida->corpo }}" data-latest-url="{{ $ultimaNotificacaoNaoLida->url }}" @endif>
                <div>
                    <strong>Ative as notificacoes</strong>
                    <span>Receba alertas no navegador quando novos agendamentos entrarem.</span>
                </div>
                <button class="btn btn-template-primary btn-sm" type="button" data-ativar-notificacoes>Ativar notificacoes</button>
            </div>
        @endauth
        @hasSection('after-header')
            <div class="after-header-content">
                @yield('after-header')
            </div>
        @endif
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
