@csrf
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label d-flex align-items-center gap-2" for="nome">Nome <button class="tooltip-help" type="button" data-bs-toggle="tooltip" title="Nome publico do servico exibido no link de agendamento.">?</button></label>
        <input class="form-control @error('nome') is-invalid @enderror" id="nome" name="nome" value="{{ old('nome', $servico->nome) }}" required>
        @error('nome')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <label class="form-label d-flex align-items-center gap-2" for="preco">Preco <button class="tooltip-help" type="button" data-bs-toggle="tooltip" title="Valor cobrado pelo atendimento. A mascara de moeda converte para o formato correto ao salvar.">?</button></label>
        <div class="input-group">
            <span class="input-group-text">R$</span>
            <input class="form-control @error('preco') is-invalid @enderror" id="preco" name="preco" value="{{ old('preco', number_format((float) $servico->preco, 2, ',', '.')) }}" data-mascara-moeda>
            @error('preco')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="col-md-3">
        <label class="form-label d-flex align-items-center gap-2" for="status">Status <button class="tooltip-help" type="button" data-bs-toggle="tooltip" title="Publicado aparece no link publico. Rascunho e inativo ficam ocultos para clientes.">?</button></label>
        <select class="form-select" id="status" name="status">
            @foreach(['publicado' => 'Publicado', 'rascunho' => 'Rascunho', 'inativo' => 'Inativo'] as $valor => $texto)
                <option value="{{ $valor }}" @selected(old('status', $servico->status) === $valor)>{{ $texto }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label d-flex align-items-center gap-2" for="duracao_minutos">Duracao <button class="tooltip-help" type="button" data-bs-toggle="tooltip" title="Tempo principal do atendimento, em minutos. Exemplo: 60 para uma hora.">?</button></label>
        <input class="form-control" id="duracao_minutos" name="duracao_minutos" type="number" min="5" value="{{ old('duracao_minutos', $servico->duracao_minutos) }}" required>
    </div>
    <div class="col-md-3">
        <label class="form-label d-flex align-items-center gap-2" for="intervalo_adicional_minutos">Intervalo <button class="tooltip-help" type="button" data-bs-toggle="tooltip" title="Tempo extra bloqueado depois do atendimento para descanso, limpeza ou deslocamento.">?</button></label>
        <input class="form-control" id="intervalo_adicional_minutos" name="intervalo_adicional_minutos" type="number" min="0" value="{{ old('intervalo_adicional_minutos', $servico->intervalo_adicional_minutos) }}" required>
    </div>
    <div class="col-md-3">
        <label class="form-label d-flex align-items-center gap-2" for="antecedencia_minima_minutos">Antecedencia <button class="tooltip-help" type="button" data-bs-toggle="tooltip" title="Tempo minimo antes do horario para permitir agendamento. Exemplo: 30 impede marcar para menos de meia hora a partir de agora.">?</button></label>
        <input class="form-control" id="antecedencia_minima_minutos" name="antecedencia_minima_minutos" type="number" min="0" value="{{ old('antecedencia_minima_minutos', $servico->antecedencia_minima_minutos) }}" required>
    </div>
    <div class="col-md-3">
        <label class="form-label d-flex align-items-center gap-2" for="limite_dias_futuros">
            Limite futuro
            <button class="tooltip-help" type="button" data-bs-toggle="tooltip" data-bs-placement="top" title="Quantidade maxima de dias no futuro em que o cliente podera agendar este servico. Exemplo: 30 permite escolher datas ate 30 dias a partir de hoje.">?</button>
        </label>
        <input class="form-control" id="limite_dias_futuros" name="limite_dias_futuros" type="number" min="1" value="{{ old('limite_dias_futuros', $servico->limite_dias_futuros) }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label d-flex align-items-center gap-2" for="ordem_exibicao">Ordem <button class="tooltip-help" type="button" data-bs-toggle="tooltip" title="Define a posicao do servico na lista publica. Numeros menores aparecem primeiro.">?</button></label>
        <input class="form-control" id="ordem_exibicao" name="ordem_exibicao" type="number" min="0" value="{{ old('ordem_exibicao', $servico->ordem_exibicao) }}" required>
    </div>
    <div class="col-md-8">
        <label class="form-label d-flex align-items-center gap-2" for="imagem">Foto do servico <button class="tooltip-help" type="button" data-bs-toggle="tooltip" title="Imagem exibida para ajudar o cliente a reconhecer o servico no link publico. Aceita PNG, JPG e WEBP.">?</button></label>
        <input class="form-control @error('imagem') is-invalid @enderror" id="imagem" name="imagem" type="file" accept="image/png,image/jpeg,image/webp">
        @error('imagem')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <label class="form-label d-flex align-items-center gap-2" for="descricao">Descricao <button class="tooltip-help" type="button" data-bs-toggle="tooltip" title="Resumo opcional com detalhes importantes para o cliente antes de agendar.">?</button></label>
        <textarea class="form-control" id="descricao" name="descricao" rows="3">{{ old('descricao', $servico->descricao) }}</textarea>
    </div>
    <div class="col-12">
        <label class="form-label d-flex align-items-center gap-2">Profissionais que executam <button class="tooltip-help" type="button" data-bs-toggle="tooltip" title="Vincule por ID os profissionais que podem atender este servico. Nomes repetidos nao afetam a validacao.">?</button></label>
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
            <label class="form-check-label" for="permite_escolher_profissional">Cliente pode escolher profissional <button class="tooltip-help ms-2" type="button" data-bs-toggle="tooltip" title="Quando ativo, futuramente o cliente podera escolher quem vai atender. Quando desativo, o sistema pode atribuir automaticamente.">?</button></label>
        </div>
    </div>
</div>
<div class="d-flex gap-2 mt-4">
    <button class="btn btn-template-primary" type="submit">Salvar servico</button>
    <a class="btn btn-outline-secondary" href="{{ route('prestador.servicos.index') }}">Cancelar</a>
</div>
