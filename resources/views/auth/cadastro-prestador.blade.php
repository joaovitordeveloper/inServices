<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#3b6cff">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="inServices">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <title>Criar conta - {{ config('app.name') }}</title>
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    <link rel="manifest" href="/manifest.webmanifest">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="login-page">
<main class="login-split cadastro-split">
    <section class="login-form-panel" aria-labelledby="titulo-cadastro">
        <div class="login-form-inner cadastro-form-inner">
            <div class="login-brand">inServices</div>
            <h1 id="titulo-cadastro">Criar conta</h1>
            <p>Escolha um plano e crie o acesso do prestador para iniciar {{ $diasTesteGratuito }} dias de teste gratuito.</p>

            @if(session('status'))
                <div class="auth-alert mb-3">{{ session('status') }}</div>
            @endif

            <form method="post" action="{{ route('cadastro.prestador.store') }}" novalidate>
                @csrf

                <div class="mb-3">
                    <label class="form-label" for="plano_id">Plano</label>
                    <select class="form-select @error('plano_id') is-invalid @enderror" id="plano_id" name="plano_id" data-plano-select required>
                        <option value="">Selecione um plano</option>
                        @foreach($planos as $plano)
                            <option value="{{ $plano->id }}" @selected(old('plano_id') == $plano->id)>{{ $plano->nome }} - R$ {{ number_format($plano->valor_mensal, 2, ',', '.') }}/mes</option>
                        @endforeach
                    </select>
                    @error('plano_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="nome_responsavel">Responsavel</label>
                        <input class="form-control @error('nome_responsavel') is-invalid @enderror" id="nome_responsavel" name="nome_responsavel" value="{{ old('nome_responsavel') }}" required>
                        @error('nome_responsavel')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="nome_publico">Nome publico</label>
                        <input class="form-control @error('nome_publico') is-invalid @enderror" id="nome_publico" name="nome_publico" value="{{ old('nome_publico') }}" required>
                        @error('nome_publico')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="email">Email</label>
                        <input class="form-control @error('email') is-invalid @enderror" id="email" name="email" type="email" value="{{ old('email') }}" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="telefone">Telefone</label>
                        <input class="form-control @error('telefone') is-invalid @enderror" id="telefone" name="telefone" value="{{ old('telefone') }}" required>
                        @error('telefone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="senha">Senha</label>
                        <input class="form-control @error('senha') is-invalid @enderror" id="senha" name="senha" type="password" required>
                        @error('senha')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="senha_confirmation">Confirmar senha</label>
                        <input class="form-control" id="senha_confirmation" name="senha_confirmation" type="password" required>
                    </div>
                </div>

                <div class="form-check my-4">
                    <input class="form-check-input @error('aceite_privacidade') is-invalid @enderror" id="aceite_privacidade" name="aceite_privacidade" type="checkbox" value="1" required>
                    <label class="form-check-label" for="aceite_privacidade">Aceito a politica de privacidade e os termos da plataforma.</label>
                    @error('aceite_privacidade')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <button class="btn btn-template-primary w-100" type="submit">Criar conta e entrar</button>
                <p class="login-secondary-link">Ja tem conta? <a href="{{ route('login') }}">Entrar</a></p>
            </form>
        </div>
    </section>
    <section class="login-art-panel planos-art-panel" aria-label="Planos disponiveis">
        <div class="planos-vitrine">
            <div class="login-art-copy planos-copy">
                <h2>Escolha seu plano</h2>
                <p>Comece com {{ $diasTesteGratuito }} dias de teste gratuito e acompanhe tudo pelo painel.</p>
            </div>

            <div class="planos-grid">
                @forelse($planos as $plano)
                    <button class="plano-card" type="button" data-plano-card="{{ $plano->id }}">
                        <span>{{ $plano->nome }}</span>
                        <strong>R$ {{ number_format($plano->valor_mensal, 2, ',', '.') }}</strong>
                        <small>por mes</small>
                        <ul>
                            <li>{{ $diasTesteGratuito }} dias de teste gratuito</li>
                            <li>{{ $plano->quantidade_maxima_servicos ? $plano->quantidade_maxima_servicos.' servicos' : 'Servicos ilimitados' }}</li>
                            <li>{{ $plano->quantidade_maxima_profissionais ? $plano->quantidade_maxima_profissionais.' profissionais' : 'Profissionais ilimitados' }}</li>
                            <li>{{ $plano->permite_web_push ? 'Notificacoes inclusas' : 'Sem notificacoes' }}</li>
                            <li>{{ $plano->permite_relatorios ? 'Relatorios inclusos' : 'Sem relatorios' }}</li>
                        </ul>
                    </button>
                @empty
                    <div class="plano-card plano-card-vazio">
                        <span>Nenhum plano ativo</span>
                        <p>Entre em contato com o administrador para liberar novos cadastros.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>
</main>
</body>
</html>
