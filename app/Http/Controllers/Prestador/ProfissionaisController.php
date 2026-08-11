<?php

namespace App\Http\Controllers\Prestador;

use App\Http\Controllers\Controller;
use App\Http\Requests\SalvarProfissionalRequest;
use App\Models\Profissional;
use App\Services\ServicoTelefone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProfissionaisController extends Controller
{
    public function index(): View
    {
        $prestador = $this->prestador()->load('assinatura.plano');
        $limite = $prestador->assinatura?->plano?->quantidade_maxima_profissionais;
        $totalAtivos = $prestador->profissionais()->where('ativo', true)->count();

        return view('prestador.profissionais.index', [
            'prestador' => $prestador,
            'profissionais' => $prestador->profissionais()->withCount('agendamentos')->orderByDesc('ativo')->orderBy('nome')->get(),
            'limiteProfissionais' => $limite,
            'totalProfissionaisAtivos' => $totalAtivos,
        ]);
    }

    public function store(SalvarProfissionalRequest $request, ServicoTelefone $telefones): RedirectResponse
    {
        $prestador = $this->prestador()->load('assinatura.plano');
        $this->validarLimitePlano($prestador->assinatura?->plano?->quantidade_maxima_profissionais, $prestador->profissionais()->where('ativo', true)->count());

        $prestador->profissionais()->create([
            'nome' => $request->string('nome')->toString(),
            'cargo' => $request->input('cargo'),
            'telefone' => $request->input('telefone'),
            'telefone_normalizado' => $telefones->normalizarBrasil($request->input('telefone')),
            'email' => $request->input('email'),
            'ativo' => true,
        ]);

        return redirect()->route('prestador.profissionais.index')->with('status', 'Profissional cadastrado com sucesso.');
    }

    public function edit(Profissional $profissional): View
    {
        $prestador = $this->prestador();
        abort_unless($profissional->prestador_id === $prestador->id, 404);

        return view('prestador.profissionais.edit', [
            'prestador' => $prestador,
            'profissional' => $profissional,
        ]);
    }

    public function update(SalvarProfissionalRequest $request, Profissional $profissional, ServicoTelefone $telefones): RedirectResponse
    {
        abort_unless($profissional->prestador_id === $this->prestador()->id, 404);

        $profissional->update([
            'nome' => $request->string('nome')->toString(),
            'cargo' => $request->input('cargo'),
            'telefone' => $request->input('telefone'),
            'telefone_normalizado' => $telefones->normalizarBrasil($request->input('telefone')),
            'email' => $request->input('email'),
        ]);

        return redirect()->route('prestador.profissionais.index')->with('status', 'Profissional atualizado.');
    }

    public function alternarStatus(Profissional $profissional): RedirectResponse
    {
        $prestador = $this->prestador()->load('assinatura.plano');
        abort_unless($profissional->prestador_id === $prestador->id, 404);

        if (! $profissional->ativo) {
            $this->validarLimitePlano(
                $prestador->assinatura?->plano?->quantidade_maxima_profissionais,
                $prestador->profissionais()->where('ativo', true)->count(),
            );
        }

        $profissional->update(['ativo' => ! $profissional->ativo]);

        return redirect()->route('prestador.profissionais.index')->with('status', $profissional->ativo ? 'Profissional ativado.' : 'Profissional inativado.');
    }

    public function destroy(Profissional $profissional): RedirectResponse
    {
        abort_unless($profissional->prestador_id === $this->prestador()->id, 404);

        if ($profissional->agendamentos()->where('inicio_em', '>=', today())->exists()) {
            return redirect()
                ->route('prestador.profissionais.index')
                ->with('erro', 'Este profissional possui agendamentos de hoje em diante. Inative-o para manter o historico.');
        }

        $profissional->delete();

        return redirect()->route('prestador.profissionais.index')->with('status', 'Profissional removido.');
    }

    private function validarLimitePlano(?int $limite, int $totalAtivos): void
    {
        if ($limite && $totalAtivos >= $limite) {
            throw ValidationException::withMessages([
                'nome' => "Seu plano atual permite no maximo {$limite} profissional(is). Para cadastrar outro profissional, altere sua assinatura.",
            ]);
        }
    }

    private function prestador()
    {
        return auth()->user()->perfilPrestador()->firstOrFail();
    }
}
