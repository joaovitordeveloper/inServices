@extends('layouts.app', ['titulo' => 'Painel do prestador', 'subtitulo' => 'Resumo mensal da agenda, atendimentos e recebimentos.', 'prestador' => $prestador])

@section('after-header')
@if($prestador->assinatura?->status === 'teste')
    <section class="trial-banner">
        <div>
            <strong>Periodo de teste ativo</strong>
            <span>
                Voce esta usando o inServices em teste gratuito ate {{ $prestador->assinatura->periodo_gratuito_ate?->format('d/m/Y') ?? '-' }}.
                @if($prestador->assinatura->periodo_gratuito_ate)
                    Restam {{ max(0, today()->diffInDays($prestador->assinatura->periodo_gratuito_ate, false)) }} dia(s).
                @endif
            </span>
        </div>
        <a class="btn btn-template-primary btn-sm" href="{{ route('prestador.mensalidades.index') }}">Ver mensalidade</a>
    </section>
@endif
@endsection

@section('content')
<div data-home-dashboard data-home-dashboard-url="{{ route('prestador.painel.dados') }}">
<div class="prestador-hero"></div>

<div class="metric-grid dashboard-metrics prestador-metrics">
    <article class="metric-card metric-compact"><span>Total recebido no mes</span><strong data-home-metric="recebido_mes">R$ {{ number_format($recebidoMes, 2, ',', '.') }}</strong><small>Servicos agendados no periodo</small></article>
    <article class="metric-card metric-compact"><span>Atendimentos no mes</span><strong data-home-metric="atendimentos_mes">{{ $atendimentosMes }}</strong><small>Agendamentos nao cancelados</small></article>
    <article class="metric-card metric-compact"><span>Ticket medio</span><strong data-home-metric="ticket_medio_mes">R$ {{ number_format($ticketMedioMes, 2, ',', '.') }}</strong><small>Receita media por atendimento</small></article>
    <article class="metric-card metric-compact"><span>Agendamentos hoje</span><strong data-home-metric="agendamentos_hoje">{{ $agendamentosHoje }}</strong><small>Compromissos do dia</small></article>
</div>

<div class="content-grid prestador-resumo-grid">
    <section class="dashboard-panel">
        <div class="section-title">
            <div>
                <h2>Resumo do mes</h2>
                <p class="section-subtitle">{{ now()->translatedFormat('F \\d\\e Y') }}</p>
            </div>
            @if($prestador)
                <a class="btn btn-template-primary btn-sm" href="{{ route('publico.agendamento.index', ['prestador' => $prestador->uuid_publico]) }}">Link publico</a>
            @endif
        </div>

        <div class="resumo-linhas">
            <div class="resumo-linha"><span>Total recebido</span><strong data-home-metric="recebido_mes">R$ {{ number_format($recebidoMes, 2, ',', '.') }}</strong><i data-home-bar="percentual_recebido_mes" style="width: {{ $percentualRecebidoMes }}%"></i><small>Meta do mes: <span data-home-metric="meta_recebimento_mes">R$ {{ number_format($metaRecebimentoMes, 2, ',', '.') }}</span></small></div>
            <div class="resumo-linha"><span>Atendimentos</span><strong data-home-metric="atendimentos_mes">{{ $atendimentosMes }}</strong><i data-home-bar-fixed="atendimentos_mes" style="width: {{ min(100, $atendimentosMes * 10) }}%"></i></div>
            <div class="resumo-linha"><span>Ticket medio</span><strong data-home-metric="ticket_medio_mes">R$ {{ number_format($ticketMedioMes, 2, ',', '.') }}</strong><i data-home-bar-money="ticket_medio_mes" style="width: {{ min(100, $ticketMedioMes) }}%"></i></div>
        </div>
    </section>

    <section class="dashboard-panel">
        <div class="section-title">
            <div>
                <h2>Agenda de hoje</h2>
                <p class="section-subtitle">Agendamentos por profissional</p>
            </div>
            <strong class="today-count" data-home-metric="agendamentos_hoje">{{ $agendamentosHoje }}</strong>
        </div>

        <div class="agenda-profissionais" data-home-agenda-hoje>
            @forelse($agendamentosHojePorProfissional as $profissional)
                <article class="agenda-profissional-card">
                    <div>
                        <strong>{{ $profissional->nome }}</strong>
                        <span>{{ $profissional->agendamentos->count() }} hoje</span>
                    </div>

                    @forelse($profissional->agendamentos as $agendamento)
                        <div class="agenda-dia-item">
                            <time>{{ $agendamento->inicio_em->format('H:i') }}</time>
                            <span>{{ $agendamento->cliente->nome }}</span>
                            <small>{{ $agendamento->servico->nome }}</small>
                        </div>
                    @empty
                        <p class="empty-line">Sem agendamentos hoje.</p>
                    @endforelse
                </article>
            @empty
                <div class="empty-state">Nenhum profissional ativo cadastrado.</div>
            @endforelse
        </div>
    </section>

    <section class="dashboard-panel">
        <div class="section-title">
            <div>
                <h2>Resumo por profissional</h2>
                <p class="section-subtitle">Atendimentos e recebido no mes</p>
            </div>
        </div>

        <div class="professional-summary-list" data-home-resumo-profissionais>
            @forelse($resumoProfissionaisMes as $profissional)
                <article class="professional-summary-item">
                    <div>
                        <strong>{{ $profissional->nome }}</strong>
                        <span>{{ $profissional->total_mes }} atendimentos</span>
                    </div>
                    <b>R$ {{ number_format($profissional->recebido_mes, 2, ',', '.') }}</b>
                </article>
            @empty
                <div class="empty-state">Nenhum profissional ativo cadastrado.</div>
            @endforelse
        </div>
    </section>
</div>

<section class="dashboard-panel mt-4">
    <div class="section-title">
        <h2>Agendamentos de hoje em diante</h2>
    </div>
    <div class="table-responsive">
        <table class="table align-middle tabela-dados">
            <thead><tr><th>Cliente</th><th>Servico</th><th>Profissional</th><th>Horario</th><th>Acoes</th></tr></thead>
            <tbody>
            @foreach($proximosAgendamentos as $agendamento)
                <tr>
                    <td>{{ $agendamento->cliente->nome }}</td>
                    <td>{{ $agendamento->servico->nome }}</td>
                    <td>{{ $agendamento->profissional->nome }}</td>
                    <td>{{ $agendamento->inicio_em->format('d/m H:i') }}</td>
                    <td>
                        @if($agendamento->link_whatsapp)
                            <a class="btn btn-sm btn-outline-success" href="{{ $agendamento->link_whatsapp }}" target="_blank" rel="noopener">WhatsApp</a>
                        @else
                            <button class="btn btn-sm btn-outline-secondary" type="button" disabled>Sem telefone</button>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</section>
</div>
@endsection
