<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SalvarPlanoRequest;
use App\Models\Plano;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PlanosController extends Controller
{
    public function index(): View
    {
        return view('admin.planos.index', [
            'planos' => Plano::withCount('assinaturas')->orderBy('nome')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.planos.create', [
            'plano' => new Plano([
                'valor_mensal' => 0,
                'periodo_tolerancia_dias' => 5,
                'permite_web_push' => true,
                'permite_relatorios' => true,
                'ativo' => true,
            ]),
        ]);
    }

    public function store(SalvarPlanoRequest $request): RedirectResponse
    {
        Plano::create($this->dadosPlano($request));

        return redirect()->route('admin.planos.index')->with('status', 'Plano cadastrado com sucesso.');
    }

    public function edit(Plano $plano): View
    {
        return view('admin.planos.edit', [
            'plano' => $plano,
        ]);
    }

    public function update(SalvarPlanoRequest $request, Plano $plano): RedirectResponse
    {
        $plano->update($this->dadosPlano($request));

        return redirect()->route('admin.planos.index')->with('status', 'Plano atualizado com sucesso.');
    }

    public function destroy(Plano $plano): RedirectResponse
    {
        if ($plano->ativo && $plano->assinaturas()->exists()) {
            return redirect()
                ->route('admin.planos.index')
                ->with('erro', 'Nao e possivel inativar um plano que possui prestador cadastrado ou assinatura vinculada.');
        }

        $plano->update(['ativo' => ! $plano->ativo]);

        return redirect()->route('admin.planos.index')->with('status', $plano->ativo ? 'Plano ativado.' : 'Plano inativado.');
    }

    private function dadosPlano(SalvarPlanoRequest $request): array
    {
        return [
            'nome' => $request->string('nome')->toString(),
            'descricao' => $request->input('descricao'),
            'valor_mensal' => $request->input('valor_mensal'),
            'quantidade_maxima_servicos' => $request->input('quantidade_maxima_servicos'),
            'quantidade_maxima_profissionais' => $request->input('quantidade_maxima_profissionais'),
            'quantidade_maxima_agendamentos_mes' => $request->input('quantidade_maxima_agendamentos_mes'),
            'periodo_tolerancia_dias' => $request->integer('periodo_tolerancia_dias'),
            'permite_web_push' => $request->boolean('permite_web_push'),
            'permite_relatorios' => $request->boolean('permite_relatorios'),
            'ativo' => $request->boolean('ativo'),
        ];
    }
}
