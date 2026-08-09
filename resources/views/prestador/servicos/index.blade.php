@extends('layouts.app', ['titulo' => 'Servicos', 'subtitulo' => 'Cadastre e publique os servicos do seu link publico.', 'prestador' => $prestador])

@section('content')
<section class="dashboard-panel">
    <div class="section-title">
        <h2>Servicos</h2>
        <a class="btn btn-template-primary btn-sm" href="{{ route('prestador.servicos.create') }}">Novo servico</a>
    </div>
    @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
    <div class="table-responsive">
        <table class="table tabela-dados align-middle">
            <thead><tr><th>Foto</th><th>Nome</th><th>Preco</th><th>Duracao</th><th>Profissionais</th><th>Status</th><th>Acoes</th></tr></thead>
            <tbody>
            @foreach($servicos as $servico)
                <tr>
                    <td>@if($servico->imagem)<img class="thumb-servico" src="{{ asset('storage/'.$servico->imagem) }}" alt="">@else - @endif</td>
                    <td>{{ $servico->nome }}</td>
                    <td>{{ $servico->preco ? 'R$ '.number_format($servico->preco, 2, ',', '.') : '-' }}</td>
                    <td>{{ $servico->duracao_minutos }} min</td>
                    <td>{{ $servico->profissionais_count }}</td>
                    <td>{{ ucfirst($servico->status) }}</td>
                    <td>
                        <div class="d-flex gap-2">
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('prestador.servicos.edit', $servico) }}">Editar</a>
                            <form method="post" action="{{ route('prestador.servicos.destroy', $servico) }}" data-boxalert="Deseja alterar o status deste servico?">
                                @csrf
                                @method('delete')
                                <button class="btn btn-sm btn-outline-secondary" type="submit">{{ $servico->status === 'publicado' ? 'Inativar' : 'Publicar' }}</button>
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
