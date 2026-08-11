@extends('layouts.app', ['titulo' => 'Editar profissional', 'subtitulo' => 'Atualize os dados do profissional selecionado.', 'prestador' => $prestador])

@section('content')
<section class="dashboard-panel">
    <div class="section-title">
        <h2>{{ $profissional->nome }}</h2>
        <a class="btn btn-outline-secondary btn-sm" href="{{ route('prestador.profissionais.index') }}">Voltar</a>
    </div>

    <form method="post" action="{{ route('prestador.profissionais.update', $profissional) }}">
        @include('prestador.profissionais._form', ['profissional' => $profissional])
        <button class="btn btn-template-primary mt-3" type="submit">Salvar alteracoes</button>
    </form>
</section>
@endsection
