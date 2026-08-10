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
    <title>Entrar - {{ config('app.name') }}</title>
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    <link rel="manifest" href="/manifest.webmanifest">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="login-page">
<main class="login-split">
    <section class="login-form-panel" aria-labelledby="titulo-login">
        <div class="login-form-inner">
            <div class="login-brand">inServices</div>
            <h1 id="titulo-login">Entrar</h1>
            <p>Acesse sua conta para controlar agenda, servicos e mensalidades.</p>

            <form method="post" action="{{ route('login.store') }}" novalidate>
                @csrf

                <div class="mb-3">
                    <label class="form-label" for="email">Email</label>
                    <input class="form-control @error('email') is-invalid @enderror" id="email" name="email" type="email" value="{{ old('email', 'developer.joaovitor@gmail.com') }}" autocomplete="email" autofocus required>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="senha">Senha</label>
                    <input class="form-control @error('senha') is-invalid @enderror" id="senha" name="senha" type="password" autocomplete="current-password" required>
                    @error('senha')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <button class="btn btn-template-primary w-100" type="submit">Entrar</button>
                <p class="login-secondary-link">Ainda nao tem conta? <a href="{{ route('cadastro.prestador') }}">Criar conta</a></p>
            </form>
        </div>
    </section>
    <section class="login-art-panel" aria-label="Resumo do sistema">
        <div class="login-outline-shape"></div>
        <div class="login-solid-shape"></div>
        <div class="login-art-copy">
            <h2>Sua agenda em um so lugar</h2>
            <p>Organize servicos, profissionais, clientes e mensalidades com clareza todos os dias.</p>
        </div>
    </section>
</main>
</body>
</html>
