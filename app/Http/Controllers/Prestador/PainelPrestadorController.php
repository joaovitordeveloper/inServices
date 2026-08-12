<?php

namespace App\Http\Controllers\Prestador;

use App\Http\Controllers\Controller;
use App\Models\Agendamento;
use App\Models\Cliente;
use App\Models\ModeloMensagemWhatsapp;
use App\Models\Profissional;
use App\Models\Servico;
use App\Services\ServicoWhatsapp;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use InvalidArgumentException;

class PainelPrestadorController extends Controller
{
    public function __invoke(ServicoWhatsapp $whatsapp): View
    {
        return view('prestador.painel', $this->dadosPainel($whatsapp));
    }

    public function dados(ServicoWhatsapp $whatsapp): JsonResponse
    {
        $dados = $this->dadosPainel($whatsapp);

        return response()->json([
            'metricas' => [
                'recebido_mes' => 'R$ '.number_format($dados['recebidoMes'], 2, ',', '.'),
                'atendimentos_mes' => $dados['atendimentosMes'],
                'ticket_medio_mes' => 'R$ '.number_format($dados['ticketMedioMes'], 2, ',', '.'),
                'agendamentos_hoje' => $dados['agendamentosHoje'],
                'percentual_recebido_mes' => $dados['percentualRecebidoMes'],
                'meta_recebimento_mes' => 'R$ '.number_format($dados['metaRecebimentoMes'], 2, ',', '.'),
            ],
            'agenda_hoje' => $dados['agendamentosHojePorProfissional']->map(fn (Profissional $profissional): array => [
                'nome' => $profissional->nome,
                'total' => $profissional->agendamentos->count(),
                'agendamentos' => $profissional->agendamentos->map(fn (Agendamento $agendamento): array => [
                    'horario' => $agendamento->inicio_em->format('H:i'),
                    'cliente' => $agendamento->cliente->nome,
                    'servico' => $agendamento->servico->nome,
                ])->values(),
            ])->values(),
            'resumo_profissionais' => $dados['resumoProfissionaisMes']->map(fn (Profissional $profissional): array => [
                'nome' => $profissional->nome,
                'total_mes' => $profissional->total_mes,
                'recebido_mes' => 'R$ '.number_format($profissional->recebido_mes, 2, ',', '.'),
            ])->values(),
        ]);
    }

    private function dadosPainel(ServicoWhatsapp $whatsapp): array
    {
        $prestador = auth()->user()->perfilPrestador()->with('assinatura.plano')->firstOrFail();
        $modeloWhatsapp = ModeloMensagemWhatsapp::firstOrCreate(
            ['prestador_id' => $prestador->id, 'nome' => 'contato'],
            [
                'mensagem' => '',
                'ativo' => true,
            ],
        );
        $agendamentosMes = Agendamento::with('servico')
            ->where('prestador_id', $prestador->id)
            ->whereMonth('inicio_em', now()->month)
            ->whereYear('inicio_em', now()->year)
            ->whereNotIn('status', ['cancelado_pelo_prestador', 'cancelado_pelo_cliente'])
            ->get();
        $recebidoMes = $agendamentosMes->sum(fn (Agendamento $agendamento) => (float) ($agendamento->servico->preco ?? 0));
        $atendimentosMes = $agendamentosMes->count();
        $metaRecebimentoMes = max(1, (float) ($prestador->assinatura?->plano?->valor_mensal ?? 0));
        $agendamentosHojePorProfissional = Profissional::with(['agendamentos' => fn ($query) => $query
            ->with(['cliente', 'servico'])
            ->where('prestador_id', $prestador->id)
            ->whereDate('inicio_em', today())
            ->whereNotIn('status', ['cancelado_pelo_prestador', 'cancelado_pelo_cliente'])
            ->orderBy('inicio_em'),
        ])
            ->where('prestador_id', $prestador->id)
            ->where('ativo', true)
            ->orderBy('nome')
            ->get();
        $resumoProfissionaisMes = Profissional::with(['agendamentos' => fn ($query) => $query
            ->with('servico')
            ->where('prestador_id', $prestador->id)
            ->whereMonth('inicio_em', now()->month)
            ->whereYear('inicio_em', now()->year)
            ->whereNotIn('status', ['cancelado_pelo_prestador', 'cancelado_pelo_cliente']),
        ])
            ->where('prestador_id', $prestador->id)
            ->where('ativo', true)
            ->orderBy('nome')
            ->get()
            ->map(function (Profissional $profissional): Profissional {
                $profissional->total_mes = $profissional->agendamentos->count();
                $profissional->recebido_mes = $profissional->agendamentos->sum(fn (Agendamento $agendamento) => (float) ($agendamento->servico->preco ?? 0));

                return $profissional;
            });

        return [
            'prestador' => $prestador,
            'agendamentosHoje' => Agendamento::when($prestador, fn ($query) => $query->where('prestador_id', $prestador->id))->whereDate('inicio_em', today())->count(),
            'atendimentosMes' => $atendimentosMes,
            'recebidoMes' => $recebidoMes,
            'metaRecebimentoMes' => $metaRecebimentoMes,
            'percentualRecebidoMes' => min(100, ($recebidoMes / $metaRecebimentoMes) * 100),
            'ticketMedioMes' => $atendimentosMes > 0 ? $recebidoMes / $atendimentosMes : 0,
            'agendamentosHojePorProfissional' => $agendamentosHojePorProfissional,
            'resumoProfissionaisMes' => $resumoProfissionaisMes,
            'proximosAgendamentos' => Agendamento::with(['cliente', 'servico', 'profissional'])
                ->when($prestador, fn ($query) => $query->where('prestador_id', $prestador->id))
                ->where('inicio_em', '>=', today())
                ->orderBy('inicio_em')
                ->get()
                ->map(function (Agendamento $agendamento) use ($modeloWhatsapp, $whatsapp): Agendamento {
                    $dadosMensagem = [
                        'nome_cliente' => $agendamento->cliente->nome,
                        'servico' => $agendamento->servico->nome,
                        'profissional' => $agendamento->profissional->nome,
                        'data' => $agendamento->inicio_em->format('d/m/Y'),
                        'data_relativa' => $agendamento->inicio_em->isToday() ? 'hoje' : 'dia '.$agendamento->inicio_em->format('d/m/Y'),
                        'horario' => $agendamento->inicio_em->format('H:i'),
                        'valor_servico' => 'R$ '.number_format((float) ($agendamento->servico->preco ?? 0), 2, ',', '.'),
                    ];

                    $mensagemPadrao = $whatsapp->preencherModelo(
                        "Ola, {nome_cliente} tudo bem?\n\nSeu horario {data_relativa} as {horario} esta confirmado!\n{servico} - {valor_servico}",
                        $dadosMensagem,
                    );
                    $complemento = trim($whatsapp->preencherModelo($modeloWhatsapp->mensagem ?? '', $dadosMensagem));
                    $mensagem = $complemento !== '' ? $mensagemPadrao."\n\n".$complemento : $mensagemPadrao;

                    try {
                        $agendamento->link_whatsapp = $whatsapp->gerarLink($agendamento->cliente->telefone_normalizado ?? '', $mensagem);
                    } catch (InvalidArgumentException) {
                        $agendamento->link_whatsapp = null;
                    }

                    return $agendamento;
                }),
            'servicosAtivos' => Servico::when($prestador, fn ($query) => $query->where('prestador_id', $prestador->id))->where('status', 'publicado')->count(),
            'profissionaisAtivos' => Profissional::when($prestador, fn ($query) => $query->where('prestador_id', $prestador->id))->where('ativo', true)->count(),
            'clientesRecentes' => Cliente::when($prestador, fn ($query) => $query->where('prestador_id', $prestador->id))->latest()->limit(5)->get(),
        ];
    }
}
