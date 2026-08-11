@csrf
@isset($profissional)
    @method('put')
@endisset

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Nome</label>
        <input class="form-control @error('nome') is-invalid @enderror" name="nome" value="{{ old('nome', $profissional->nome ?? '') }}" required>
        @error('nome')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Cargo</label>
        <input class="form-control @error('cargo') is-invalid @enderror" name="cargo" value="{{ old('cargo', $profissional->cargo ?? '') }}">
        @error('cargo')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Telefone</label>
        <input class="form-control @error('telefone') is-invalid @enderror" name="telefone" value="{{ old('telefone', $profissional->telefone ?? '') }}">
        @error('telefone')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Email</label>
        <input class="form-control @error('email') is-invalid @enderror" name="email" type="email" value="{{ old('email', $profissional->email ?? '') }}">
        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
