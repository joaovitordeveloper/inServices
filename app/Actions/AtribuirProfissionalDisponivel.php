<?php

namespace App\Actions;

use App\Models\Profissional;
use App\Models\Servico;
use App\Services\CalcularHorariosDisponiveis;
use Carbon\CarbonImmutable;

class AtribuirProfissionalDisponivel
{
    public function __construct(private readonly CalcularHorariosDisponiveis $horarios) {}

    public function executar(Servico $servico, CarbonImmutable $inicio): ?Profissional
    {
        $disponiveis = $this->horarios->executar($servico, $inicio)
            ->where('inicio', $inicio->toDateTimeString())
            ->pluck('profissional_id');

        if ($disponiveis->isEmpty()) {
            return null;
        }

        return Profissional::query()
            ->whereIn('id', $disponiveis)
            ->withCount(['agendamentos' => fn ($query) => $query->whereMonth('inicio_em', $inicio->month)->whereYear('inicio_em', $inicio->year)])
            ->orderBy('agendamentos_count')
            ->orderBy('id')
            ->first();
    }
}
