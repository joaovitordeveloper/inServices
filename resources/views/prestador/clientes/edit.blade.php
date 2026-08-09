@extends('layouts.app', ['titulo' => 'Editar cliente', 'subtitulo' => 'Atualize telefone, email e observacoes.', 'prestador' => $prestador])

@section('content')
<section class="dashboard-panel">
    <h2>{{ $cliente->nome }}</h2>
    <form method="post" action="{{ route('prestador.clientes.update', $cliente) }}">
        @csrf
        @method('put')
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Nome</label><input class="form-control" name="nome" value="{{ old('nome', $cliente->nome) }}" required></div>
            <div class="col-md-6"><label class="form-label">Telefone</label><input class="form-control" name="telefone" value="{{ old('telefone', $cliente->telefone) }}" required></div>
            <div class="col-md-6"><label class="form-label">Email</label><input class="form-control" name="email" type="email" value="{{ old('email', $cliente->email) }}"></div>
            <div class="col-12"><label class="form-label">Observacoes</label><textarea class="form-control" name="observacoes" rows="4">{{ old('observacoes', $cliente->observacoes) }}</textarea></div>
        </div>
        <div class="d-flex gap-2 mt-4">
            <button class="btn btn-template-primary" type="submit">Salvar cliente</button>
            <a class="btn btn-outline-secondary" href="{{ route('prestador.clientes.index') }}">Cancelar</a>
        </div>
    </form>
</section>
@endsection
