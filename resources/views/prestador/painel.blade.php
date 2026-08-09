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
                <a class="btn btn-template-primary btn-sm" href="{{ route('publico.agendamento.index', $prestador) }}">Link publico</a>
            @endif
        </div>

        <div class="resumo-linhas">
            <div class="resumo-linha"><span>Total recebido</span><strong>R$ {{ number_format($recebidoMes, 2, ',', '.') }}</strong><i style="width: 100%"></i></div>
            <div class="resumo-linha"><span>Atendimentos</span><strong>{{ $atendimentosMes }}</strong><i style="width: {{ min(100, $atendimentosMes * 10) }}%"></i></div>
            <div class="resumo-linha"><span>Servicos ativos</span><strong>{{ $servicosAtivos }}</strong><i style="width: {{ min(100, $servicosAtivos * 20) }}%"></i></div>
            <div class="resumo-linha"><span>Profissionais ativos</span><strong>{{ $profissionaisAtivos }}</strong><i style="width: {{ min(100, $profissionaisAtivos * 20) }}%"></i></div>
            <div class="resumo-linha"><span>Clientes recentes</span><strong>{{ $clientesRecentes->count() }}</strong><i style="width: {{ min(100, $clientesRecentes->count() * 20) }}%"></i></div>
        </div>
    </section>

    <section class="dashboard-panel">
        <h2>Plano atual</h2>
        <div class="plano-resumo">
            <span>{{ $prestador?->assinatura?->plano?->nome ?? 'Nao configurado' }}</span>
            <strong>{{ ucfirst($prestador?->assinatura?->status ?? 'sem assinatura') }}</strong>
            <p>Proximo vencimento: {{ $prestador?->assinatura?->data_proximo_vencimento?->format('d/m/Y') ?? '-' }}</p>
        </div>
    </section>
</div>

<section class="dashboard-panel mt-4">
    <div class="section-title">
        <h2>Proximos agendamentos</h2>
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
                    <td><a class="btn btn-sm btn-outline-success" href="https://wa.me/{{ $agendamento->cliente->telefone_normalizado }}?text={{ rawurlencode('Ola, '.$agendamento->cliente->nome.'. Precisamos falar sobre seu agendamento.') }}" target="_blank" rel="noopener">WhatsApp</a></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</section>
@endsection
