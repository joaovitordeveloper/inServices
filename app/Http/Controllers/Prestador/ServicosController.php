<?php

namespace App\Http\Controllers\Prestador;

use App\Http\Controllers\Controller;
use App\Http\Requests\SalvarServicoRequest;
use App\Models\PerfilPrestador;
use App\Models\Servico;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ServicosController extends Controller
{
    public function index(): View
    {
        $prestador = $this->prestador();

        return view('prestador.servicos.index', [
            'prestador' => $prestador,
            'servicos' => $prestador->servicos()->withCount('profissionais')->orderBy('ordem_exibicao')->get(),
        ]);
    }

    public function create(): View
    {
        $prestador = $this->prestador();

        return view('prestador.servicos.create', [
            'prestador' => $prestador,
            'servico' => new Servico([
                'duracao_minutos' => 60,
                'intervalo_adicional_minutos' => 0,
                'status' => 'publicado',
                'ordem_exibicao' => 0,
                'antecedencia_minima_minutos' => 30,
                'limite_dias_futuros' => 30,
                'permite_escolher_profissional' => true,
            ]),
            'profissionais' => $prestador->profissionais()->where('ativo', true)->orderBy('nome')->get(),
        ]);
    }

    public function store(SalvarServicoRequest $request): RedirectResponse
    {
        $prestador = $this->prestador()->load('assinatura.plano');
        $limite = $prestador->assinatura?->plano?->quantidade_maxima_servicos;

        if ($limite && $prestador->servicos()->count() >= $limite) {
            throw ValidationException::withMessages([
                'nome' => "Seu plano permite cadastrar ate {$limite} servico(s).",
            ]);
        }

        $servico = $prestador->servicos()->create($this->dados($request));
        $servico->profissionais()->sync($this->idsProfissionaisDoPrestador($prestador, $request->input('profissionais', [])));

        return redirect()->route('prestador.servicos.index')->with('status', 'Servico cadastrado com sucesso.');
    }

    public function edit(Servico $servico): View
    {
        $prestador = $this->prestador();
        abort_unless($servico->prestador_id === $prestador->id, 404);

        return view('prestador.servicos.edit', [
            'prestador' => $prestador,
            'servico' => $servico,
            'profissionais' => $prestador->profissionais()->where('ativo', true)->orderBy('nome')->get(),
        ]);
    }

    public function update(SalvarServicoRequest $request, Servico $servico): RedirectResponse
    {
        $prestador = $this->prestador();
        abort_unless($servico->prestador_id === $prestador->id, 404);

        $dados = $this->dados($request, $servico);
        $servico->update($dados);
        $servico->profissionais()->sync($this->idsProfissionaisDoPrestador($prestador, $request->input('profissionais', [])));

        return redirect()->route('prestador.servicos.index')->with('status', 'Servico atualizado com sucesso.');
    }

    public function destroy(Servico $servico): RedirectResponse
    {
        $prestador = $this->prestador();
        abort_unless($servico->prestador_id === $prestador->id, 404);

        $servico->update(['status' => $servico->status === 'publicado' ? 'inativo' : 'publicado']);

        return redirect()->route('prestador.servicos.index')->with('status', $servico->status === 'publicado' ? 'Servico publicado.' : 'Servico inativado.');
    }

    private function dados(SalvarServicoRequest $request, ?Servico $servico = null): array
    {
        $dados = [
            'nome' => $request->string('nome')->toString(),
            'slug' => $request->input('slug'),
            'descricao' => $request->input('descricao'),
            'duracao_minutos' => $request->integer('duracao_minutos'),
            'intervalo_adicional_minutos' => $request->integer('intervalo_adicional_minutos'),
            'preco' => $request->input('preco'),
            'status' => $request->input('status'),
            'ordem_exibicao' => $request->integer('ordem_exibicao'),
            'antecedencia_minima_minutos' => $request->integer('antecedencia_minima_minutos'),
            'limite_dias_futuros' => $request->integer('limite_dias_futuros'),
            'permite_escolher_profissional' => $request->boolean('permite_escolher_profissional'),
        ];

        if ($request->hasFile('imagem')) {
            if ($servico?->imagem) {
                Storage::disk('public')->delete($servico->imagem);
            }

            $dados['imagem'] = $request->file('imagem')->store('servicos', 'public');
        }

        return $dados;
    }

    private function prestador()
    {
        return auth()->user()->perfilPrestador()->firstOrFail();
    }

    private function idsProfissionaisDoPrestador(PerfilPrestador $prestador, array $ids): array
    {
        $ids = collect($ids)->map(fn ($id) => (int) $id)->unique()->values();

        if ($ids->isEmpty()) {
            return [];
        }

        $validos = $prestador->profissionais()
            ->whereIn('id', $ids)
            ->pluck('id')
            ->all();

        if (count($validos) !== $ids->count()) {
            throw ValidationException::withMessages([
                'profissionais' => 'Selecione apenas profissionais cadastrados neste prestador.',
            ]);
        }

        return $validos;
    }
}
