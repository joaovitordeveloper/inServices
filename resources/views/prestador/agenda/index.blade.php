@extends('layouts.app', ['titulo' => 'Agenda', 'subtitulo' => 'Cadastre os dias e horarios de atendimento dos profissionais.', 'prestador' => $prestador])

@section('content')
@if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif

<div class="agenda-layout">
    <section class="dashboard-panel agenda-form-panel">
        <h2>Novo horario</h2>
        @if($profissionais->isEmpty())
            <div class="plan-limit-alert">
                <strong>Nenhum profissional ativo</strong>
                <span>Cadastre ou ative um profissional antes de criar horarios de atendimento.</span>
                <a class="btn btn-template-primary btn-sm" href="{{ route('prestador.profissionais.index') }}">Ir para profissionais</a>
            </div>
        @endif
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
                <div class="col-12">
                    <label class="form-label">Dias de trabalho</label>
                    <select class="form-select select2-dias" name="dias_semana[]" multiple data-multiselect-tags data-placeholder="Selecione os dias">
                        @foreach([0 => 'Domingo', 1 => 'Segunda', 2 => 'Terca', 3 => 'Quarta', 4 => 'Quinta', 5 => 'Sexta', 6 => 'Sabado'] as $dia => $nome)
                            <option value="{{ $dia }}">{{ $nome }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">Escolha todos os dias em que esse profissional trabalha nesse horario.</small>
                </div>
                <div class="col-md-6"><label class="form-label">Inicio do trabalho</label><input class="form-control" name="horario_inicio" type="time" required></div>
                <div class="col-md-6"><label class="form-label">Fim do trabalho</label><input class="form-control" name="horario_fim" type="time" required></div>
                <div class="col-md-6"><label class="form-label">Inicio do almoco</label><input class="form-control" name="almoco_inicio" type="time"></div>
                <div class="col-md-6"><label class="form-label">Fim do almoco</label><input class="form-control" name="almoco_fim" type="time"></div>
            </div>
            <button class="btn btn-template-primary mt-3" type="submit" @disabled($profissionais->isEmpty())>Cadastrar horario</button>
        </form>
    </section>

    <section class="dashboard-panel agenda-table-panel">
        <h2>Horarios cadastrados</h2>
        <div class="table-responsive">
            <table class="table tabela-dados">
                <thead><tr><th>Profissional</th><th>Dia</th><th>Trabalho</th><th>Almoco</th><th>Acoes</th></tr></thead>
                <tbody>
                @foreach($regras as $regra)
                    <tr>
                        <td>{{ $regra->profissional->nome }}</td>
                        <td>{{ [0 => 'Domingo', 1 => 'Segunda', 2 => 'Terca', 3 => 'Quarta', 4 => 'Quinta', 5 => 'Sexta', 6 => 'Sabado'][$regra->dia_semana] }}</td>
                        <td>{{ substr($regra->horario_inicio, 0, 5) }} ate {{ substr($regra->horario_fim, 0, 5) }}</td>
                        <td>
                            @if($regra->almoco_inicio && $regra->almoco_fim)
                                {{ substr($regra->almoco_inicio, 0, 5) }} ate {{ substr($regra->almoco_fim, 0, 5) }}
                            @else
                                -
                            @endif
                        </td>
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
</div>
@endsection
