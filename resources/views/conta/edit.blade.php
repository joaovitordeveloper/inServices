@extends('layouts.app', ['titulo' => 'Minha conta', 'subtitulo' => 'Altere sua senha de acesso.'])

@section('content')
<section class="dashboard-panel">
    <h2>Alterar senha</h2>
    @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
    <form method="post" action="{{ route('conta.senha.update') }}">
        @csrf
        @method('put')
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Nova senha</label>
                <input class="form-control @error('senha') is-invalid @enderror" name="senha" type="password" required>
                @error('senha')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Confirmar senha</label>
                <input class="form-control" name="senha_confirmation" type="password" required>
            </div>
        </div>
        <button class="btn btn-template-primary mt-4" type="submit">Alterar senha</button>
    </form>
</section>
@endsection
