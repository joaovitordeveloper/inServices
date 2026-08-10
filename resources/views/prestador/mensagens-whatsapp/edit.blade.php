@extends('layouts.app', ['titulo' => 'Mensagem WhatsApp', 'subtitulo' => 'Personalize o texto extra enviado ao cliente pelo botao do painel.', 'prestador' => $prestador])

@section('content')
@if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif

<section class="dashboard-panel">
    <h2>Modelo de contato</h2>
    <p class="section-subtitle">O sistema sempre envia a base do agendamento. O campo abaixo adiciona uma mensagem personalizada depois dela.</p>

    <div class="empty-state mb-3">
        <strong>Mensagem padrao:</strong><br>
        Ola, Joao tudo bem?<br><br>
        Seu horario hoje as 18:10 esta confirmado!<br>
        Corte degrade - R$ 45,00
    </div>

    <form method="post" action="{{ route('prestador.mensagens-whatsapp.update') }}">
        @csrf
        @method('put')

        <div class="mb-3">
            <label class="form-label" for="mensagem">Texto extra</label>
            <textarea class="form-control @error('mensagem') is-invalid @enderror" id="mensagem" name="mensagem" rows="6" placeholder="Ex: Chegue 10 minutos antes. Nosso endereco e...">{{ old('mensagem', $modelo->mensagem) }}</textarea>
            @error('mensagem')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="form-help mb-3">
            Campos disponiveis:
            <button class="tooltip-help" type="button" data-bs-toggle="tooltip" data-bs-title="Use estes campos no texto extra. O sistema troca automaticamente pelo nome do cliente, servico, profissional, data, horario e valor do agendamento.">?</button>
            <code>{nome_cliente}</code>,
            <code>{servico}</code>,
            <code>{profissional}</code>,
            <code>{data}</code>,
            <code>{data_relativa}</code>,
            <code>{horario}</code>.
            <code>{valor_servico}</code>.
        </div>

        <button class="btn btn-template-primary" type="submit">Salvar texto extra</button>
    </form>
</section>
@endsection
