@extends('layouts.app', ['titulo' => 'Mensalidades', 'subtitulo' => 'Controle financeiro dos prestadores.'])

@section('content')
<section class="dashboard-panel">
    <h2>Mensalidades</h2>
    <div class="table-responsive">
        <table class="table tabela-dados">
            <thead><tr><th>Prestador</th><th>Plano</th><th>Competencia</th><th>Vencimento</th><th>Valor</th><th>Status</th></tr></thead>
            <tbody>
            @foreach($mensalidades as $mensalidade)
                <tr>
                    <td>{{ $mensalidade->prestador->nome_publico }}</td>
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
