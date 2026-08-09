@extends('layouts.app', ['titulo' => 'Editar servico', 'subtitulo' => 'Atualize dados, foto e profissionais vinculados.', 'prestador' => $prestador])

@section('content')
<section class="dashboard-panel">
    <h2>{{ $servico->nome }}</h2>
    <form method="post" action="{{ route('prestador.servicos.update', $servico) }}" enctype="multipart/form-data" novalidate>
        @method('put')
        @include('prestador.servicos._form')
    </form>
</section>
@endsection
