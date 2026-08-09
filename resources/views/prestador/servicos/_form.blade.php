@csrf
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label" for="nome">Nome</label>
        <input class="form-control @error('nome') is-invalid @enderror" id="nome" name="nome" value="{{ old('nome', $servico->nome) }}" required>
        @error('nome')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <label class="form-label" for="preco">Preco</label>
        <div class="input-group">
            <span class="input-group-text">R$</span>
            <input class="form-control @error('preco') is-invalid @enderror" id="preco" name="preco" value="{{ old('preco', number_format((float) $servico->preco, 2, ',', '.')) }}" data-mascara-moeda>
            @error('preco')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="col-md-3">
        <label class="form-label" for="status">Status</label>
        <select class="form-select" id="status" name="status">
            @foreach(['publicado' => 'Publicado', 'rascunho' => 'Rascunho', 'inativo' => 'Inativo'] as $valor => $texto)
                <option value="{{ $valor }}" @selected(old('status', $servico->status) === $valor)>{{ $texto }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label" for="duracao_minutos">Duracao</label>
        <input class="form-control" id="duracao_minutos" name="duracao_minutos" type="number" min="5" value="{{ old('duracao_minutos', $servico->duracao_minutos) }}" required>
    </div>
    <div class="col-md-3">
        <label class="form-label" for="intervalo_adicional_minutos">Intervalo</label>
        <input class="form-control" id="intervalo_adicional_minutos" name="intervalo_adicional_minutos" type="number" min="0" value="{{ old('intervalo_adicional_minutos', $servico->intervalo_adicional_minutos) }}" required>
    </div>
    <div class="col-md-3">
        <label class="form-label" for="antecedencia_minima_minutos">Antecedencia</label>
        <input class="form-control" id="antecedencia_minima_minutos" name="antecedencia_minima_minutos" type="number" min="0" value="{{ old('antecedencia_minima_minutos', $servico->antecedencia_minima_minutos) }}" required>
    </div>
    <div class="col-md-3">
        <label class="form-label" for="limite_dias_futuros">Limite futuro</label>
        <input class="form-control" id="limite_dias_futuros" name="limite_dias_futuros" type="number" min="1" value="{{ old('limite_dias_futuros', $servico->limite_dias_futuros) }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label" for="ordem_exibicao">Ordem</label>
        <input class="form-control" id="ordem_exibicao" name="ordem_exibicao" type="number" min="0" value="{{ old('ordem_exibicao', $servico->ordem_exibicao) }}" required>
    </div>
    <div class="col-md-8">
        <label class="form-label" for="imagem">Foto do servico</label>
        <input class="form-control @error('imagem') is-invalid @enderror" id="imagem" name="imagem" type="file" accept="image/png,image/jpeg,image/webp">
        @error('imagem')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <label class="form-label" for="descricao">Descricao</label>
        <textarea class="form-control" id="descricao" name="descricao" rows="3">{{ old('descricao', $servico->descricao) }}</textarea>
    </div>
    <div class="col-12">
        <label class="form-label">Profissionais que executam</label>
        <div class="check-grid">
            @foreach($profissionais as $profissional)
                <label class="form-check">
                    <input class="form-check-input" name="profissionais[]" type="checkbox" value="{{ $profissional->id }}" @checked(in_array($profissional->id, old('profissionais', $servico->exists ? $servico->profissionais()->pluck('profissionais.id')->all() : [])))>
                    <span class="form-check-label">{{ $profissional->nome }}</span>
                </label>
            @endforeach
        </div>
    </div>
    <div class="col-12">
        <div class="form-check form-switch">
            <input class="form-check-input" id="permite_escolher_profissional" name="permite_escolher_profissional" type="checkbox" value="1" @checked(old('permite_escolher_profissional', $servico->permite_escolher_profissional))>
            <label class="form-check-label" for="permite_escolher_profissional">Cliente pode escolher profissional</label>
        </div>
    </div>
</div>
<div class="d-flex gap-2 mt-4">
    <button class="btn btn-template-primary" type="submit">Salvar servico</button>
    <a class="btn btn-outline-secondary" href="{{ route('prestador.servicos.index') }}">Cancelar</a>
</div>
