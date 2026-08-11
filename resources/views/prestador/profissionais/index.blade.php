@extends('layouts.app', ['titulo' => 'Profissionais', 'subtitulo' => 'Cadastre, edite e gerencie os profissionais de atendimento.', 'prestador' => $prestador])

@section('content')
@if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
@if(session('erro'))<div class="alert alert-danger">{{ session('erro') }}</div>@endif

<section class="dashboard-panel">
    <h2>Novo profissional</h2>

    @if($errors->has('nome'))
        <div class="plan-limit-alert">
            <strong>Limite do plano atingido</strong>
            <span>{{ $errors->first('nome') }}</span>
            @if($limiteProfissionais)
                <small>Uso atual: {{ $totalProfissionaisAtivos }} de {{ $limiteProfissionais }} profissional(is) ativo(s).</small>
            @endif
            <a class="btn btn-template-primary btn-sm" href="{{ route('prestador.assinatura.edit') }}">Ver assinatura</a>
        </div>
    @elseif($limiteProfissionais && $totalProfissionaisAtivos >= $limiteProfissionais)
        <div class="plan-limit-alert">
            <strong>Seu plano chegou ao limite</strong>
            <span>Voce ja possui {{ $totalProfissionaisAtivos }} de {{ $limiteProfissionais }} profissional(is) ativo(s).</span>
            <small>Para adicionar outro profissional, inative um atual ou troque para um plano com limite maior.</small>
            <a class="btn btn-template-primary btn-sm" href="{{ route('prestador.assinatura.edit') }}">Trocar plano</a>
        </div>
    @endif

    <form method="post" action="{{ route('prestador.profissionais.store') }}">
        @include('prestador.profissionais._form')
        <button class="btn btn-template-primary mt-3" type="submit" @disabled($limiteProfissionais && $totalProfissionaisAtivos >= $limiteProfissionais)>Cadastrar profissional</button>
    </form>
</section>

<section class="dashboard-panel mt-4">
    <h2>Profissionais cadastrados</h2>
    <div class="table-responsive">
        <table class="table tabela-dados">
            <thead><tr><th>Nome</th><th>Cargo</th><th>Telefone</th><th>Email</th><th>Status</th><th>Agendamentos</th><th>Acoes</th></tr></thead>
            <tbody>
            @foreach($profissionais as $profissional)
                <tr>
                    <td>{{ $profissional->nome }}</td>
                    <td>{{ $profissional->cargo ?? '-' }}</td>
                    <td>{{ $profissional->telefone ?? '-' }}</td>
                    <td>{{ $profissional->email ?? '-' }}</td>
                    <td>{{ $profissional->ativo ? 'Ativo' : 'Inativo' }}</td>
                    <td>{{ $profissional->agendamentos_count }}</td>
                    <td>
                        <div class="table-actions">
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('prestador.profissionais.edit', $profissional) }}">Editar</a>
                            <form method="post" action="{{ route('prestador.profissionais.status', $profissional) }}" data-boxalert="{{ $profissional->ativo ? 'Deseja inativar este profissional?' : 'Deseja ativar este profissional?' }}">
                                @csrf
                                @method('patch')
                                <button class="btn btn-sm {{ $profissional->ativo ? 'btn-outline-secondary' : 'btn-template-primary' }}" type="submit">{{ $profissional->ativo ? 'Inativar' : 'Ativar' }}</button>
                            </form>
                            <form method="post" action="{{ route('prestador.profissionais.destroy', $profissional) }}" data-boxalert="Deseja remover este profissional?">
                                @csrf
                                @method('delete')
                                <button class="btn btn-sm btn-outline-danger" type="submit">Remover</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</section>
@endsection
