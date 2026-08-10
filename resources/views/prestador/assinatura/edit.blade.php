@extends('layouts.app', ['titulo' => 'Assinatura', 'subtitulo' => 'Veja seu plano atual e altere quando precisar.', 'prestador' => $prestador])

@section('content')
@if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
@error('plano_id')<div class="alert alert-danger">{{ $message }}</div>@enderror

<section class="dashboard-panel">
    <div class="section-title">
        <div>
            <h2>Plano atual</h2>
            <p class="section-subtitle">{{ $assinatura?->plano?->nome ?? 'Sem plano configurado' }}</p>
        </div>
        <strong class="subscription-due">Vence em {{ $assinatura?->data_proximo_vencimento?->format('d/m/Y') ?? '-' }}</strong>
    </div>

    <div class="subscription-current">
        <span>{{ $servicosAtivos }} servicos ativos</span>
        <span>{{ $profissionaisAtivos }} profissionais ativos</span>
        <span>Status: {{ ucfirst($assinatura?->status ?? 'sem assinatura') }}</span>
    </div>
</section>

<section class="dashboard-panel mt-4">
    <div class="section-title">
        <h2>Trocar plano</h2>
    </div>

    <form method="post" action="{{ route('prestador.assinatura.update') }}">
        @csrf
        @method('put')

        <div class="subscription-plan-grid">
            @foreach($planos as $plano)
                <label class="subscription-plan-card {{ $assinatura?->plano_id === $plano->id ? 'active' : '' }}">
                    <input type="radio" name="plano_id" value="{{ $plano->id }}" @checked(old('plano_id', $assinatura?->plano_id) == $plano->id)>
                    <span>{{ $plano->nome }}</span>
                    <strong>R$ {{ number_format($plano->valor_mensal, 2, ',', '.') }}</strong>
                    <small>por mes</small>
                    <ul>
                        <li>{{ $plano->quantidade_maxima_servicos ? $plano->quantidade_maxima_servicos.' servicos' : 'Servicos ilimitados' }}</li>
                        <li>{{ $plano->quantidade_maxima_profissionais ? $plano->quantidade_maxima_profissionais.' profissionais' : 'Profissionais ilimitados' }}</li>
                        <li>{{ $plano->quantidade_maxima_agendamentos_mes ? $plano->quantidade_maxima_agendamentos_mes.' agendamentos por mes' : 'Agendamentos ilimitados' }}</li>
                        <li>{{ $plano->permite_web_push ? 'Notificacoes inclusas' : 'Sem notificacoes' }}</li>
                        <li>{{ $plano->permite_relatorios ? 'Relatorios inclusos' : 'Sem relatorios' }}</li>
                    </ul>
                </label>
            @endforeach
        </div>

        <button class="btn btn-template-primary mt-3" type="submit">Atualizar assinatura</button>
    </form>
</section>
@endsection
