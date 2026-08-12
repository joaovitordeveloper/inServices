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
    <title>Agendar - {{ $prestador->nome_publico }}</title>
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    <link rel="manifest" href="/manifest.webmanifest">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="public-chat-page" data-public-chat-base="{{ route('publico.agendamento.index', ['prestador' => $prestador->uuid_publico]) }}" @if($servicoSelecionado) data-public-chat-service="1" @endif>
<main class="public-chat-shell" data-public-chat-shell>
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
                Ola! Vou te ajudar a encontrar um horario.
            </div>

            @if(! $acesso['permitido'])
                <div class="chat-bubble bot warning">Agenda temporariamente indisponivel.</div>
            @elseif($acesso['alerta'])
                <div class="chat-bubble bot warning">{{ $acesso['alerta'] }}</div>
            @endif

            @if(! $clienteIdentificado)
                <div class="chat-bubble bot">Antes de mostrar os servicos, me diga seu nome e telefone para continuarmos.</div>
                <form class="chat-input-panel" method="post" action="{{ route('publico.agendamento.identificar', ['prestador' => $prestador->uuid_publico]) }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" for="nome">Nome</label>
                        <input class="form-control @error('nome') is-invalid @enderror" id="nome" name="nome" value="{{ old('nome') }}" autocomplete="name" required>
                        @error('nome')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="telefone">Telefone</label>
                        <input class="form-control @error('telefone') is-invalid @enderror" id="telefone" name="telefone" value="{{ old('telefone') }}" inputmode="tel" autocomplete="tel" required>
                        @error('telefone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <button class="btn btn-template-primary w-100" type="submit">Continuar</button>
                </form>
            @else
                <div class="chat-bubble user">{{ $clienteIdentificado->nome }}</div>
                <button class="chat-menu-button" type="button" data-toggle-client-appointments @if($meusAgendamentos->isEmpty()) hidden @endif>
                    <span>Meus agendamentos</span>
                    <small data-client-appointments-count>{{ $meusAgendamentos->count() }}</small>
                </button>
                @if($meusAgendamentos->isNotEmpty())
                    <div class="chat-appointments" data-client-appointments hidden>
                        @foreach($meusAgendamentos as $agendamento)
                            <div class="chat-appointment-card">
                                <strong>{{ $agendamento->servico->nome }}</strong>
                                <span>{{ $agendamento->inicio_em->format('d/m/Y H:i') }} com {{ $agendamento->profissional->nome }}</span>
                                <small>Protocolo: {{ $agendamento->protocolo_publico }}</small>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="chat-appointments" data-client-appointments hidden></div>
                @endif
                @if($acesso['permitido'])
                    <div class="chat-bubble bot">Perfeito. Agora escolha o servico desejado.</div>

                    <div class="chat-options" data-service-strip @if($servicoSelecionado) hidden @endif>
                        @forelse($servicos as $servico)
                            <a class="chat-option {{ $servicoSelecionado?->id === $servico->id ? 'active' : '' }}" href="{{ route('publico.agendamento.servico', ['prestador' => $prestador->uuid_publico, 'servico' => $servico->uuid_publico]) }}" data-chat-service-link data-service-name="{{ $servico->nome }}" data-service-url="{{ route('publico.agendamento.horarios', ['prestador' => $prestador->uuid_publico, 'servico' => $servico->uuid_publico]) }}" data-confirm-url="{{ route('publico.agendamento.confirmar', ['prestador' => $prestador->uuid_publico, 'servico' => $servico->uuid_publico]) }}">
                                <span class="chat-service-thumb">
                                    @if($servico->imagem)
                                        <img src="{{ asset('storage/'.$servico->imagem) }}" alt="{{ $servico->nome }}">
                                    @else
                                        <span>iS</span>
                                    @endif
                                </span>
                                <span class="chat-service-info">
                                    <strong>{{ $servico->nome }}</strong>
                                    <span class="chat-service-meta">
                                        <span>{{ $servico->duracao_minutos }} min</span>
                                        @if($servico->preco)
                                            <span>R$ {{ number_format($servico->preco, 2, ',', '.') }}</span>
                                        @endif
                                    </span>
                                    @if($servico->descricao)
                                        <span class="chat-service-description">{{ $servico->descricao }}</span>
                                    @endif
                                </span>
                            </a>
                        @empty
                            <div class="chat-bubble bot warning">Nenhum servico publicado no momento.</div>
                        @endforelse
                    </div>
                @endif

                <div class="chat-service-flow" data-service-flow @if(! ($servicoSelecionado && $acesso['permitido'])) hidden @endif>
                    <div class="chat-bubble user service-choice" data-service-choice-name>{{ $servicoSelecionado?->nome }}</div>
                    <button class="chat-back-button" type="button" data-chat-back-service>Voltar e trocar servico</button>
                    <div class="chat-bubble bot">Certo. Escolha o melhor dia para eu buscar os horarios disponiveis.</div>

                    <div class="chat-input-panel chat-schedule-panel">
                        <label class="form-label">Selecione o dia e horario:</label>
                        <input id="data-agendamento" type="hidden" value="{{ now()->toDateString() }}">
                        <div class="chat-date-strip" data-date-strip></div>
                        <div class="chat-scroll-hint">Arraste para o lado para ver mais</div>
                        <div class="chat-slot-heading">Horarios disponiveis:</div>
                        <div id="horarios" class="slot-grid chat-slot-grid" data-url="{{ $servicoSelecionado ? route('publico.agendamento.horarios', ['prestador' => $prestador->uuid_publico, 'servico' => $servicoSelecionado->uuid_publico]) : '' }}">
                            <div class="empty-state">Carregando horarios...</div>
                        </div>
                    </div>

                    <div class="chat-bubble user date-choice" data-date-choice hidden></div>
                    <div class="chat-bubble user time-choice" data-time-choice hidden></div>
                    <div class="chat-bubble bot" data-final-chat-hint hidden>Perfeito. Agora vou confirmar seus dados para finalizar o agendamento.</div>
                </div>

                <div class="chat-send-panel" data-chat-send-panel hidden>
                    <span data-chat-send-text></span>
                    <button class="btn btn-template-primary btn-sm" type="button" data-chat-send-button>Enviar</button>
                </div>
            @endif
        </div>

        @if($clienteIdentificado)
            <div class="chat-finished-screen" data-chat-finished hidden>
                <div class="chat-finished-icon">
                    <span>✓</span>
                </div>
                <h2>Agendamento solicitado</h2>
                <p data-chat-finished-message>Seu agendamento foi salvo com sucesso.</p>
                <div class="chat-finished-actions">
                    <button class="btn btn-template-primary" type="button" data-final-show-appointments>Meus agendamentos</button>
                    <button class="btn btn-outline-primary" type="button" data-final-new-appointment>Solicitar novo agendamento</button>
                </div>
                <div class="chat-appointments final-appointments" data-final-appointments hidden></div>
            </div>
        @endif
    </section>
</main>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
