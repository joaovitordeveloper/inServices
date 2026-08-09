@csrf

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label" for="nome">Nome</label>
        <input class="form-control @error('nome') is-invalid @enderror" id="nome" name="nome" value="{{ old('nome', $plano->nome) }}" required>
        @error('nome')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label" for="valor_mensal">Valor mensal</label>
        <div class="input-group">
            <span class="input-group-text">R$</span>
            <input class="form-control @error('valor_mensal') is-invalid @enderror" id="valor_mensal" name="valor_mensal" inputmode="decimal" value="{{ old('valor_mensal', number_format((float) $plano->valor_mensal, 2, ',', '.')) }}" data-mascara-moeda required>
            @error('valor_mensal')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="col-12">
        <label class="form-label" for="descricao">Descricao</label>
        <textarea class="form-control @error('descricao') is-invalid @enderror" id="descricao" name="descricao" rows="3">{{ old('descricao', $plano->descricao) }}</textarea>
        @error('descricao')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <label class="form-label" for="quantidade_maxima_servicos">Max. servicos</label>
        <input class="form-control @error('quantidade_maxima_servicos') is-invalid @enderror" id="quantidade_maxima_servicos" name="quantidade_maxima_servicos" type="number" min="1" value="{{ old('quantidade_maxima_servicos', $plano->quantidade_maxima_servicos) }}">
        @error('quantidade_maxima_servicos')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <label class="form-label" for="quantidade_maxima_profissionais">Max. profissionais</label>
        <input class="form-control @error('quantidade_maxima_profissionais') is-invalid @enderror" id="quantidade_maxima_profissionais" name="quantidade_maxima_profissionais" type="number" min="1" value="{{ old('quantidade_maxima_profissionais', $plano->quantidade_maxima_profissionais) }}">
        @error('quantidade_maxima_profissionais')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <label class="form-label" for="quantidade_maxima_agendamentos_mes">Max. agendamentos/mes</label>
        <input class="form-control @error('quantidade_maxima_agendamentos_mes') is-invalid @enderror" id="quantidade_maxima_agendamentos_mes" name="quantidade_maxima_agendamentos_mes" type="number" min="1" value="{{ old('quantidade_maxima_agendamentos_mes', $plano->quantidade_maxima_agendamentos_mes) }}">
        @error('quantidade_maxima_agendamentos_mes')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <label class="form-label" for="periodo_tolerancia_dias">Tolerancia em dias</label>
        <input class="form-control @error('periodo_tolerancia_dias') is-invalid @enderror" id="periodo_tolerancia_dias" name="periodo_tolerancia_dias" type="number" min="0" max="60" value="{{ old('periodo_tolerancia_dias', $plano->periodo_tolerancia_dias) }}" required>
        @error('periodo_tolerancia_dias')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <div class="form-check form-switch">
            <input class="form-check-input" id="permite_web_push" name="permite_web_push" type="checkbox" value="1" @checked(old('permite_web_push', $plano->permite_web_push))>
            <label class="form-check-label" for="permite_web_push">Permite notificacoes</label>
        </div>
        <div class="form-check form-switch">
            <input class="form-check-input" id="permite_relatorios" name="permite_relatorios" type="checkbox" value="1" @checked(old('permite_relatorios', $plano->permite_relatorios))>
            <label class="form-check-label" for="permite_relatorios">Permite relatorios</label>
        </div>
        <div class="form-check form-switch">
            <input class="form-check-input" id="ativo" name="ativo" type="checkbox" value="1" @checked(old('ativo', $plano->ativo))>
            <label class="form-check-label" for="ativo">Plano ativo para novos cadastros</label>
        </div>
    </div>
</div>

<div class="d-flex gap-2 mt-4">
    <button class="btn btn-template-primary" type="submit">Salvar plano</button>
    <a class="btn btn-outline-secondary" href="{{ route('admin.planos.index') }}">Cancelar</a>
</div>
