@extends('layouts.app', ['titulo' => 'Mensalidade', 'subtitulo' => 'Acompanhe a mensalidade do seu plano.', 'prestador' => $prestador])

@section('content')
<section class="dashboard-panel">
    <div class="section-title">
        <div>
            <h2>Pagamento da mensalidade</h2>
            <p class="section-subtitle">{{ $prestador->assinatura?->plano?->nome ?? 'Plano nao configurado' }}</p>
        </div>
        <span class="subscription-due">{{ ucfirst($prestador->assinatura?->status ?? 'sem assinatura') }}</span>
    </div>

    @if($mensalidadeAberta)
        <div class="payment-current-card">
            <div>
                <span>Valor em aberto</span>
                <strong>R$ {{ number_format($mensalidadeAberta->valor_final, 2, ',', '.') }}</strong>
                <small>Vencimento em {{ $mensalidadeAberta->data_vencimento->format('d/m/Y') }}</small>
            </div>
            <button class="btn btn-template-primary" type="button" disabled>Pagamento em breve</button>
        </div>
    @else
        <div class="empty-state">Nenhuma mensalidade em aberto.</div>
    @endif
</section>

<section class="dashboard-panel mt-4">
    <h2>Historico de mensalidades</h2>
    <div class="table-responsive">
        <table class="table tabela-dados">
            <thead><tr><th>Plano</th><th>Competencia</th><th>Vencimento</th><th>Valor</th><th>Status</th></tr></thead>
            <tbody>
            @foreach($mensalidades as $mensalidade)
                <tr>
                    <td>{{ $mensalidade->assinatura->plano->nome }}</td>
                    <td>{{ $mensalidade->competencia->format('m/Y') }}</td>
                    <td>{{ $mensalidade->data_vencimento->format('d/m/Y') }}</td>
                    <td>R$ {{ number_format($mensalidade->valor_final, 2, ',', '.') }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $mensalidade->status)) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</section>
@endsection
