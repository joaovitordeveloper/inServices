@extends('layouts.app', ['titulo' => 'Editar plano', 'subtitulo' => 'Atualize limites, valor mensal e recursos do plano.'])

@section('content')
<section class="dashboard-panel">
    <h2>{{ $plano->nome }}</h2>
    <form method="post" action="{{ route('admin.planos.update', $plano) }}" novalidate>
        @method('put')
        @include('admin.planos._form')
    </form>
</section>
@endsection
