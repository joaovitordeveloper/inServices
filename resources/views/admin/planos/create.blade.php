@extends('layouts.app', ['titulo' => 'Novo plano', 'subtitulo' => 'Defina limites, valor mensal e recursos disponiveis.'])

@section('content')
<section class="dashboard-panel">
    <h2>Cadastrar plano</h2>
    <form method="post" action="{{ route('admin.planos.store') }}" novalidate>
        @include('admin.planos._form')
    </form>
</section>
@endsection
