@extends('layouts.app', ['titulo' => 'Painel do prestador', 'subtitulo' => 'Resumo mensal da agenda, atendimentos e recebimentos.', 'prestador' => $prestador])

@section('content')
<div class="prestador-hero"></div>

<div class="metric-grid dashboard-metrics prestador-metrics">
    <article class="metric-card metric-compact"><span>Total recebido no mes</span><strong>R$ {{ number_format($recebidoMes, 2, ',', '.') }}</strong><small>Servicos agendados no periodo</small></article>
    <article class="metric-card metric-compact"><span>Atendimentos no mes</span><strong>{{ $atendimentosMes }}</strong><small>Agendamentos nao cancelados</small></article>
    <article class="metric-card metric-compact"><span>Ticket medio</span><strong>R$ {{ number_format($ticketMedioMes, 2, ',', '.') }}</strong><small>Receita media por atendimento</small></article>
    <article class="metric-card metric-compact"><span>Agendamentos hoje</span><strong>{{ $agendamentosHoje }}</strong><small>Compromissos do dia</small></article>
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
            <div class="resumo-linha"><span>Total recebido</span><strong>R$ {{ number_format($recebidoMes, 2, ',', '.') }}</strong><i style="width: {{ $percentualRecebidoMes }}%"></i><small>Meta do mes: R$ {{ number_format($metaRecebimentoMes, 2, ',', '.') }}</small></div>
            <div class="resumo-linha"><span>Atendimentos</span><strong>{{ $atendimentosMes }}</strong><i style="width: {{ min(100, $atendimentosMes * 10) }}%"></i></div>
            <div class="resumo-linha"><span>Ticket medio</span><strong>R$ {{ number_format($ticketMedioMes, 2, ',', '.') }}</strong><i style="width: {{ min(100, $ticketMedioMes) }}%"></i></div>
        </div>
    </section>

    <section class="dashboard-panel">
        <div class="section-title">
            <div>
                <h2>Agenda de hoje</h2>
                <p class="section-subtitle">Agendamentos por profissional</p>
            </div>
            <strong class="today-count">{{ $agendamentosHoje }}</strong>
        </div>

        <div class="agenda-profissionais">
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

        <div class="professional-summary-list">
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
@endsection
