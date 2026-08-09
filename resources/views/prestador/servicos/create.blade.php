@extends('layouts.app', ['titulo' => 'Novo servico', 'subtitulo' => 'Cadastre um servico para aparecer no link publico.', 'prestador' => $prestador])

@section('content')
<section class="dashboard-panel">
    <h2>Cadastrar servico</h2>
    <form method="post" action="{{ route('prestador.servicos.store') }}" enctype="multipart/form-data" novalidate>
        @include('prestador.servicos._form')
    </form>
</section>
@endsection
