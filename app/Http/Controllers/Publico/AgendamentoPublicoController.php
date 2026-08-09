<?php

namespace App\Http\Controllers\Publico;

use App\Actions\VerificarAcessoPrestador;
use App\Http\Controllers\Controller;
use App\Models\PerfilPrestador;
use App\Models\Servico;
use App\Services\CalcularHorariosDisponiveis;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AgendamentoPublicoController extends Controller
{
    public function index(PerfilPrestador $prestador, VerificarAcessoPrestador $acesso): View
    {
        return view('publico.agendar', [
            'prestador' => $prestador,
            'servicoSelecionado' => null,
            'servicos' => $prestador->servicos()->where('status', 'publicado')->orderBy('ordem_exibicao')->get(),
            'acesso' => $acesso->executar($prestador),
        ]);
    }

    public function servico(PerfilPrestador $prestador, Servico $servico, VerificarAcessoPrestador $acesso): View
    {
        abort_unless($servico->prestador_id === $prestador->id, 404);

        return view('publico.agendar', [
            'prestador' => $prestador,
            'servicoSelecionado' => $servico->load('profissionais'),
            'servicos' => $prestador->servicos()->where('status', 'publicado')->orderBy('ordem_exibicao')->get(),
            'acesso' => $acesso->executar($prestador),
        ]);
    }

    public function horarios(Request $request, PerfilPrestador $prestador, Servico $servico, CalcularHorariosDisponiveis $calcular): JsonResponse
    {
        abort_unless($servico->prestador_id === $prestador->id, 404);

        $data = CarbonImmutable::parse($request->query('data', now()->toDateString()), config('app.timezone'));

        return response()->json([
            'horarios' => $calcular->executar($servico, $data)->values(),
        ]);
    }
}
