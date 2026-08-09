<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#3b6cff">
    <title>Agendar - {{ $prestador->nome_publico }}</title>
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="manifest" href="/manifest.webmanifest">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="public-chat-page">
<main class="public-chat-shell">
    <section class="public-chat-card">
        <header class="public-chat-header">
            <div>
                <span class="chat-avatar">iS</span>
            </div>
            <div>
                <h1>{{ $prestador->nome_publico }}</h1>
                <p>Assistente de agendamento</p>
            </div>
        </header>

        <div class="chat-thread">
            <div class="chat-bubble bot">
                Ola! Vou te ajudar a encontrar um horario. Primeiro escolha o servico desejado.
            </div>

            @if(! $acesso['permitido'])
                <div class="chat-bubble bot warning">Agenda temporariamente indisponivel.</div>
            @elseif($acesso['alerta'])
                <div class="chat-bubble bot warning">{{ $acesso['alerta'] }}</div>
            @endif

            <div class="chat-options">
                @foreach($servicos as $servico)
                    <a class="chat-option {{ $servicoSelecionado?->id === $servico->id ? 'active' : '' }}" href="{{ route('publico.agendamento.servico', [$prestador, $servico]) }}">
                        <strong>{{ $servico->nome }}</strong>
                        <span>{{ $servico->duracao_minutos }} min @if($servico->preco) · R$ {{ number_format($servico->preco, 2, ',', '.') }} @endif</span>
                    </a>
                @endforeach
            </div>

            @if($servicoSelecionado && $acesso['permitido'])
                <div class="chat-bubble user">{{ $servicoSelecionado->nome }}</div>
                <div class="chat-bubble bot">Perfeito. Agora escolha a data para eu buscar os horarios disponiveis.</div>

                <div class="chat-input-panel">
                    <label class="form-label" for="data-agendamento">Data do atendimento</label>
                    <input class="form-control" id="data-agendamento" type="date" value="{{ now()->toDateString() }}" min="{{ now()->toDateString() }}">
                </div>

                <div class="chat-bubble bot">Horarios disponiveis:</div>
                <div id="horarios" class="slot-grid chat-slot-grid" data-url="{{ route('publico.agendamento.horarios', [$prestador, $servicoSelecionado]) }}">
                    <div class="empty-state">Carregando horarios...</div>
                </div>

                <div class="chat-bubble bot">Depois de escolher um horario, seus dados serao confirmados para finalizar o agendamento.</div>
            @endif
        </div>
    </section>
</main>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
