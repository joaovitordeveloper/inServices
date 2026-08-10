<?php

namespace App\Http\Controllers\Prestador;

use App\Http\Controllers\Controller;
use App\Models\ModeloMensagemWhatsapp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MensagensWhatsappController extends Controller
{
    public function edit(): View
    {
        $prestador = $this->prestador();
        $modelo = $this->modeloContato($prestador->id);

        return view('prestador.mensagens-whatsapp.edit', [
            'prestador' => $prestador,
            'modelo' => $modelo,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $dados = $request->validate([
            'mensagem' => ['nullable', 'string', 'max:1000'],
        ]);

        $prestador = $this->prestador();
        $modelo = $this->modeloContato($prestador->id);

        $modelo->update([
            'mensagem' => $dados['mensagem'] ?? '',
            'ativo' => true,
        ]);

        return redirect()->route('prestador.mensagens-whatsapp.edit')->with('status', 'Texto extra do WhatsApp atualizado.');
    }

    private function modeloContato(int $prestadorId): ModeloMensagemWhatsapp
    {
        return ModeloMensagemWhatsapp::firstOrCreate(
            ['prestador_id' => $prestadorId, 'nome' => 'contato'],
            [
                'mensagem' => '',
                'ativo' => true,
            ],
        );
    }

    private function prestador()
    {
        return auth()->user()->perfilPrestador()->firstOrFail();
    }
}
