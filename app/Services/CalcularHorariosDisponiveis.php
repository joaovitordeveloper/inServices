<?php

namespace App\Services;

use App\Models\Profissional;
use App\Models\Servico;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class CalcularHorariosDisponiveis
{
    public function executar(Servico $servico, CarbonImmutable $data, ?Profissional $profissional = null): Collection
    {
        $profissionais = $profissional
            ? collect([$profissional])
            : $servico->profissionais()->wherePivot('ativo', true)->where('profissionais.ativo', true)->get();

        return $profissionais->flatMap(fn (Profissional $item) => $this->horariosDoProfissional($servico, $item, $data))
            ->sortBy(['inicio', 'profissional_id'])
            ->values();
    }

    private function horariosDoProfissional(Servico $servico, Profissional $profissional, CarbonImmutable $data): Collection
    {
        $pivot = $profissional->servicos()->where('servicos.id', $servico->id)->first()?->pivot;
        $duracao = (int) ($pivot?->duracao_minutos ?: $servico->duracao_minutos);
        $intervalo = (int) ($pivot?->intervalo_adicional_minutos ?? $servico->intervalo_adicional_minutos);
        $passo = max(15, $duracao + $intervalo);
        $diaSemana = (int) $data->dayOfWeek;

        $regras = $profissional->regrasDisponibilidade()
            ->where('dia_semana', $diaSemana)
            ->where('ativo', true)
            ->get();

        $bloqueios = $profissional->excecoesDisponibilidade()->whereDate('data', $data->toDateString())->get();
        $ocupados = $profissional->agendamentos()
            ->whereDate('inicio_em', $data->toDateString())
            ->whereNotIn('status', ['cancelado_pelo_prestador', 'cancelado_pelo_cliente'])
            ->get(['inicio_em', 'fim_em']);

        return $regras->flatMap(function ($regra) use ($data, $duracao, $passo, $bloqueios, $ocupados, $profissional) {
            $inicio = $data->setTimeFromTimeString($regra->horario_inicio);
            $fim = $data->setTimeFromTimeString($regra->horario_fim);
            $horarios = collect();

            for ($cursor = $inicio; $cursor->addMinutes($duracao)->lte($fim); $cursor = $cursor->addMinutes($passo)) {
                $termino = $cursor->addMinutes($duracao);

                if ($this->periodoLivre($cursor, $termino, $bloqueios, $ocupados)) {
                    $horarios->push([
                        'profissional_id' => $profissional->id,
                        'profissional' => $profissional->nome,
                        'inicio' => $cursor->toDateTimeString(),
                        'fim' => $termino->toDateTimeString(),
                    ]);
                }
            }

            return $horarios;
        });
    }

    private function periodoLivre(CarbonImmutable $inicio, CarbonImmutable $fim, Collection $bloqueios, Collection $ocupados): bool
    {
        foreach ($bloqueios as $bloqueio) {
            if ($bloqueio->horario_inicio === null || $bloqueio->horario_fim === null) {
                return false;
            }

            if ($inicio->lt(CarbonImmutable::parse($bloqueio->data.' '.$bloqueio->horario_fim)) && $fim->gt(CarbonImmutable::parse($bloqueio->data.' '.$bloqueio->horario_inicio))) {
                return false;
            }
        }

        foreach ($ocupados as $ocupado) {
            if ($inicio->lt($ocupado->fim_em) && $fim->gt($ocupado->inicio_em)) {
                return false;
            }
        }

        return true;
    }
}
