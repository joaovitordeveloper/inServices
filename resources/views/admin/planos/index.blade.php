@extends('layouts.app', ['titulo' => 'Planos', 'subtitulo' => 'Planos disponiveis para novos prestadores.'])

@section('content')
<section class="dashboard-panel">
    <div class="section-title">
        <h2>Planos</h2>
        <a class="btn btn-template-primary btn-sm" href="{{ route('admin.planos.create') }}">Novo plano</a>
    </div>
    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    @if(session('erro'))
        <div class="alert alert-warning">{{ session('erro') }}</div>
    @endif
    <div class="table-responsive">
        <table class="table tabela-dados">
            <thead><tr><th>Nome</th><th>Valor mensal</th><th>Assinaturas</th><th>Notificacoes</th><th>Relatorios</th><th>Status</th><th>Acoes</th></tr></thead>
            <tbody>
            @foreach($planos as $plano)
                <tr>
                    <td>{{ $plano->nome }}</td>
                    <td>R$ {{ number_format($plano->valor_mensal, 2, ',', '.') }}</td>
                    <td>{{ $plano->assinaturas_count }}</td>
                    <td>{{ $plano->permite_web_push ? 'Sim' : 'Nao' }}</td>
                    <td>{{ $plano->permite_relatorios ? 'Sim' : 'Nao' }}</td>
                    <td>{{ $plano->ativo ? 'Ativo' : 'Inativo' }}</td>
                    <td>
                        <div class="d-flex gap-2">
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.planos.edit', $plano) }}">Editar</a>
                            @if($plano->ativo && $plano->assinaturas_count > 0)
                                <button class="btn btn-sm btn-outline-secondary" type="button" disabled title="Plano com prestadores cadastrados nao pode ser inativado">Inativar</button>
                            @else
                                <form method="post" action="{{ route('admin.planos.destroy', $plano) }}" data-boxalert="Deseja alterar o status deste plano?">
                                    @csrf
                                    @method('delete')
                                    <button class="btn btn-sm btn-outline-secondary" type="submit">{{ $plano->ativo ? 'Inativar' : 'Ativar' }}</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</section>
@endsection
