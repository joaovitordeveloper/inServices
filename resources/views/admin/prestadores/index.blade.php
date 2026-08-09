@extends('layouts.app', ['titulo' => 'Prestadores cadastrados', 'subtitulo' => 'Empresas e profissionais que criaram conta na plataforma.'])

@section('content')
<section class="dashboard-panel">
    <div class="section-title">
        <h2>Prestadores</h2>
    </div>
    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    <div class="table-responsive">
        <table class="table tabela-dados align-middle">
            <thead><tr><th>Prestador</th><th>Responsavel</th><th>Email</th><th>Plano</th><th>Status</th><th>Criado em</th><th>Acoes</th></tr></thead>
            <tbody>
            @foreach($prestadores as $prestador)
                <tr>
                    <td>{{ $prestador->nome_publico }}</td>
                    <td>{{ $prestador->usuario->name }}</td>
                    <td>{{ $prestador->usuario->email }}</td>
                    <td>{{ $prestador->assinatura?->plano?->nome ?? 'Sem plano' }}</td>
                    <td>{{ ucfirst($prestador->status) }}</td>
                    <td>{{ $prestador->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        <form method="post" action="{{ route('admin.prestadores.status', $prestador) }}" data-boxalert="{{ $prestador->status === 'ativo' ? 'Deseja desativar este prestador?' : 'Deseja ativar este prestador?' }}">
                            @csrf
                            @method('patch')
                            <button class="btn btn-sm {{ $prestador->status === 'ativo' ? 'btn-outline-secondary' : 'btn-outline-primary' }}" type="submit">
                                {{ $prestador->status === 'ativo' ? 'Desativar' : 'Ativar' }}
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</section>
@endsection
