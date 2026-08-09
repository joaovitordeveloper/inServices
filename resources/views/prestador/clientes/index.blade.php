@extends('layouts.app', ['titulo' => 'Clientes', 'subtitulo' => 'Consulte e ajuste dados dos clientes quando necessario.', 'prestador' => $prestador])

@section('content')
<section class="dashboard-panel">
    <h2>Clientes</h2>
    @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
    <div class="table-responsive">
        <table class="table tabela-dados">
            <thead><tr><th>Nome</th><th>Telefone</th><th>Email</th><th>Criado em</th><th>Acoes</th></tr></thead>
            <tbody>
            @foreach($clientes as $cliente)
                <tr>
                    <td>{{ $cliente->nome }}</td>
                    <td>{{ $cliente->telefone }}</td>
                    <td>{{ $cliente->email ?? '-' }}</td>
                    <td>{{ $cliente->created_at->format('d/m/Y H:i') }}</td>
                    <td><a class="btn btn-sm btn-outline-primary" href="{{ route('prestador.clientes.edit', $cliente) }}">Editar</a></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</section>
@endsection
