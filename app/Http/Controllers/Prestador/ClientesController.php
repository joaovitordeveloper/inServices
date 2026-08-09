<?php

namespace App\Http\Controllers\Prestador;

use App\Http\Controllers\Controller;
use App\Http\Requests\AtualizarClienteRequest;
use App\Models\Cliente;
use App\Services\ServicoTelefone;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ClientesController extends Controller
{
    public function index(): View
    {
        $prestador = $this->prestador();

        return view('prestador.clientes.index', [
            'prestador' => $prestador,
            'clientes' => $prestador->clientes()->latest()->get(),
        ]);
    }

    public function edit(Cliente $cliente): View
    {
        abort_unless($cliente->prestador_id === $this->prestador()->id, 404);

        return view('prestador.clientes.edit', [
            'prestador' => $this->prestador(),
            'cliente' => $cliente,
        ]);
    }

    public function update(AtualizarClienteRequest $request, Cliente $cliente, ServicoTelefone $telefones): RedirectResponse
    {
        abort_unless($cliente->prestador_id === $this->prestador()->id, 404);

        $cliente->update([
            'nome' => $request->string('nome')->toString(),
            'telefone' => $request->string('telefone')->toString(),
            'telefone_normalizado' => $telefones->normalizarBrasil($request->string('telefone')->toString()),
            'email' => $request->input('email'),
            'observacoes' => $request->input('observacoes'),
        ]);

        return redirect()->route('prestador.clientes.index')->with('status', 'Cliente atualizado com sucesso.');
    }

    private function prestador()
    {
        return auth()->user()->perfilPrestador()->firstOrFail();
    }
}
