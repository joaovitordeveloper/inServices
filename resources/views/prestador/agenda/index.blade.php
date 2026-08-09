@extends('layouts.app', ['titulo' => 'Agenda', 'subtitulo' => 'Cadastre profissionais e horarios de atendimento.', 'prestador' => $prestador])

@section('content')
@if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif

<div class="content-grid">
    <section class="dashboard-panel">
        <h2>Novo profissional</h2>
        <form method="post" action="{{ route('prestador.agenda.profissionais.store') }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Nome</label><input class="form-control" name="nome" required></div>
                <div class="col-md-6"><label class="form-label">Cargo</label><input class="form-control" name="cargo"></div>
                <div class="col-md-6"><label class="form-label">Telefone</label><input class="form-control" name="telefone"></div>
                <div class="col-md-6"><label class="form-label">Email</label><input class="form-control" name="email" type="email"></div>
            </div>
            <button class="btn btn-template-primary mt-3" type="submit">Cadastrar profissional</button>
        </form>
    </section>

    <section class="dashboard-panel">
        <h2>Novo horario</h2>
        <form method="post" action="{{ route('prestador.agenda.regras.store') }}">
            @csrf
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Profissional</label>
                    <select class="form-select" name="profissional_id" required>
                        <option value="">Selecione</option>
                        @foreach($profissionais as $profissional)
                            <option value="{{ $profissional->id }}">{{ $profissional->nome }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Dia</label>
                    <select class="form-select" name="dia_semana" required>
                        @foreach([0 => 'Domingo', 1 => 'Segunda', 2 => 'Terca', 3 => 'Quarta', 4 => 'Quinta', 5 => 'Sexta', 6 => 'Sabado'] as $dia => $nome)
                            <option value="{{ $dia }}">{{ $nome }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4"><label class="form-label">Inicio</label><input class="form-control" name="horario_inicio" type="time" required></div>
                <div class="col-md-4"><label class="form-label">Fim</label><input class="form-control" name="horario_fim" type="time" required></div>
            </div>
            <button class="btn btn-template-primary mt-3" type="submit">Cadastrar horario</button>
        </form>
    </section>
</div>

<section class="dashboard-panel mt-4">
    <h2>Horarios cadastrados</h2>
    <div class="table-responsive">
        <table class="table tabela-dados">
            <thead><tr><th>Profissional</th><th>Dia</th><th>Inicio</th><th>Fim</th><th>Acoes</th></tr></thead>
            <tbody>
            @foreach($regras as $regra)
                <tr>
                    <td>{{ $regra->profissional->nome }}</td>
                    <td>{{ [0 => 'Domingo', 1 => 'Segunda', 2 => 'Terca', 3 => 'Quarta', 4 => 'Quinta', 5 => 'Sexta', 6 => 'Sabado'][$regra->dia_semana] }}</td>
                    <td>{{ substr($regra->horario_inicio, 0, 5) }}</td>
                    <td>{{ substr($regra->horario_fim, 0, 5) }}</td>
                    <td>
                        <form method="post" action="{{ route('prestador.agenda.regras.destroy', $regra) }}" data-boxalert="Deseja remover este horario?">
                            @csrf
                            @method('delete')
                            <button class="btn btn-sm btn-outline-secondary" type="submit">Remover</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</section>
@endsection
