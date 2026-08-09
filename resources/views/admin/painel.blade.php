@extends('layouts.app', ['titulo' => 'Administrador geral', 'subtitulo' => 'Controle global de prestadores, planos e mensalidades.'])

@section('content')
<div class="metric-grid dashboard-metrics">
    <article class="metric-card"><span>Prestadores ativos</span><strong>{{ $prestadoresAtivos }}</strong></article>
    <article class="metric-card"><span>Em teste</span><strong>{{ $prestadoresTeste }}</strong></article>
    <article class="metric-card"><span>Suspensos</span><strong>{{ $prestadoresSuspensos }}</strong></article>
    <article class="metric-card"><span>Mensalidades vencidas</span><strong>{{ $mensalidadesVencidas }}</strong></article>
    <article class="metric-card"><span>Receita mensal</span><strong>R$ {{ number_format($receitaMensal, 2, ',', '.') }}</strong></article>
</div>

<section>
    <h2>Prestadores por plano</h2>
    <div class="table-responsive">
        <table class="table tabela-dados">
            <thead><tr><th>Plano</th><th>Assinaturas</th><th>Status</th></tr></thead>
            <tbody>
            @foreach($planos as $plano)
                <tr><td>{{ $plano->nome }}</td><td>{{ $plano->assinaturas_count }}</td><td>{{ $plano->ativo ? 'Ativo' : 'Inativo' }}</td></tr>
            @endforeach
            </tbody>
        </table>
    </div>
</section>
@endsection
