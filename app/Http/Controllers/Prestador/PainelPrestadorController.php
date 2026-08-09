<?php

namespace App\Http\Controllers\Prestador;

use App\Http\Controllers\Controller;
use App\Models\Agendamento;
use App\Models\Cliente;
use App\Models\Profissional;
use App\Models\Servico;
use Illuminate\View\View;

class PainelPrestadorController extends Controller
{
    public function __invoke(): View
    {
        $prestador = auth()->user()->perfilPrestador()->with('assinatura.plano')->firstOrFail();
        $agendamentosMes = Agendamento::with('servico')
            ->where('prestador_id', $prestador->id)
            ->whereMonth('inicio_em', now()->month)
            ->whereYear('inicio_em', now()->year)
            ->whereNotIn('status', ['cancelado_pelo_prestador', 'cancelado_pelo_cliente'])
            ->get();
        $recebidoMes = $agendamentosMes->sum(fn (Agendamento $agendamento) => (float) ($agendamento->servico->preco ?? 0));
        $atendimentosMes = $agendamentosMes->count();

        return view('prestador.painel', [
            'prestador' => $prestador,
            'agendamentosHoje' => Agendamento::when($prestador, fn ($query) => $query->where('prestador_id', $prestador->id))->whereDate('inicio_em', today())->count(),
            'atendimentosMes' => $atendimentosMes,
            'recebidoMes' => $recebidoMes,
            'ticketMedioMes' => $atendimentosMes > 0 ? $recebidoMes / $atendimentosMes : 0,
            'proximosAgendamentos' => Agendamento::with(['cliente', 'servico', 'profissional'])->when($prestador, fn ($query) => $query->where('prestador_id', $prestador->id))->where('inicio_em', '>=', now())->orderBy('inicio_em')->limit(8)->get(),
            'servicosAtivos' => Servico::when($prestador, fn ($query) => $query->where('prestador_id', $prestador->id))->where('status', 'publicado')->count(),
            'profissionaisAtivos' => Profissional::when($prestador, fn ($query) => $query->where('prestador_id', $prestador->id))->where('ativo', true)->count(),
            'clientesRecentes' => Cliente::when($prestador, fn ($query) => $query->where('prestador_id', $prestador->id))->latest()->limit(5)->get(),
        ]);
    }
}
