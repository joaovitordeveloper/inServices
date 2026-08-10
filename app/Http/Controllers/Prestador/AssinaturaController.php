<?php

namespace App\Http\Controllers\Prestador;

use App\Http\Controllers\Controller;
use App\Models\Plano;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AssinaturaController extends Controller
{
    public function edit(): View
    {
        $prestador = $this->prestador();

        return view('prestador.assinatura.edit', [
            'prestador' => $prestador,
            'assinatura' => $prestador->assinatura,
            'planos' => Plano::where('ativo', true)->orderBy('valor_mensal')->get(),
            'servicosAtivos' => $prestador->servicos()->where('status', 'publicado')->count(),
            'profissionaisAtivos' => $prestador->profissionais()->where('ativo', true)->count(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $dados = $request->validate([
            'plano_id' => ['required', 'integer', 'exists:planos,id'],
        ]);
        $prestador = $this->prestador();
        $plano = Plano::where('ativo', true)->findOrFail($dados['plano_id']);
        $servicosAtivos = $prestador->servicos()->where('status', 'publicado')->count();
        $profissionaisAtivos = $prestador->profissionais()->where('ativo', true)->count();

        if ($plano->quantidade_maxima_servicos && $servicosAtivos > $plano->quantidade_maxima_servicos) {
            throw ValidationException::withMessages([
                'plano_id' => 'Este plano permite apenas '.$plano->quantidade_maxima_servicos.' servicos. Inative servicos antes de trocar.',
            ]);
        }

        if ($plano->quantidade_maxima_profissionais && $profissionaisAtivos > $plano->quantidade_maxima_profissionais) {
            throw ValidationException::withMessages([
                'plano_id' => 'Este plano permite apenas '.$plano->quantidade_maxima_profissionais.' profissionais. Inative profissionais antes de trocar.',
            ]);
        }

        $prestador->assinatura()->updateOrCreate(
            ['prestador_id' => $prestador->id],
            [
                'plano_id' => $plano->id,
                'status' => $prestador->assinatura?->status ?? 'ativa',
                'data_inicio' => $prestador->assinatura?->data_inicio ?? now()->toDateString(),
                'data_proximo_vencimento' => $prestador->assinatura?->data_proximo_vencimento ?? now()->addMonth()->toDateString(),
                'observacoes' => 'Plano alterado pelo prestador.',
            ],
        );

        return redirect()->route('prestador.assinatura.edit')->with('status', 'Plano atualizado.');
    }

    private function prestador()
    {
        return auth()->user()->perfilPrestador()->with('assinatura.plano')->firstOrFail();
    }
}
