<?php

namespace App\Http\Controllers\Prestador;

use App\Http\Controllers\Controller;
use App\Http\Requests\SalvarProfissionalRequest;
use App\Http\Requests\SalvarRegraDisponibilidadeRequest;
use App\Models\RegraDisponibilidade;
use App\Services\ServicoTelefone;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AgendaController extends Controller
{
    public function index(): View
    {
        $prestador = $this->prestador();

        return view('prestador.agenda.index', [
            'prestador' => $prestador,
            'profissionais' => $prestador->profissionais()->with('regrasDisponibilidade')->orderBy('nome')->get(),
            'regras' => RegraDisponibilidade::with('profissional')
                ->whereHas('profissional', fn ($query) => $query->where('prestador_id', $prestador->id))
                ->orderBy('dia_semana')
                ->orderBy('horario_inicio')
                ->get(),
        ]);
    }

    public function storeProfissional(SalvarProfissionalRequest $request, ServicoTelefone $telefones): RedirectResponse
    {
        $this->prestador()->profissionais()->create([
            'nome' => $request->string('nome')->toString(),
            'cargo' => $request->input('cargo'),
            'telefone' => $request->input('telefone'),
            'telefone_normalizado' => $telefones->normalizarBrasil($request->input('telefone')),
            'email' => $request->input('email'),
            'ativo' => true,
        ]);

        return redirect()->route('prestador.agenda.index')->with('status', 'Profissional cadastrado com sucesso.');
    }

    public function storeRegra(SalvarRegraDisponibilidadeRequest $request): RedirectResponse
    {
        $prestador = $this->prestador();
        $profissional = $prestador->profissionais()->findOrFail($request->integer('profissional_id'));

        $profissional->regrasDisponibilidade()->create([
            'dia_semana' => $request->integer('dia_semana'),
            'horario_inicio' => $request->input('horario_inicio'),
            'horario_fim' => $request->input('horario_fim'),
            'ativo' => $request->boolean('ativo', true),
        ]);

        return redirect()->route('prestador.agenda.index')->with('status', 'Horario cadastrado com sucesso.');
    }

    public function destroyRegra(RegraDisponibilidade $regra): RedirectResponse
    {
        abort_unless($regra->profissional->prestador_id === $this->prestador()->id, 404);

        $regra->delete();

        return redirect()->route('prestador.agenda.index')->with('status', 'Horario removido.');
    }

    private function prestador()
    {
        return auth()->user()->perfilPrestador()->firstOrFail();
    }
}
