<?php

namespace App\Services;

use App\Enums\StatusAssinatura;
use App\Models\PerfilPrestador;
use Carbon\CarbonImmutable;

class ServicoAcessoPrestador
{
    public function verificar(PerfilPrestador $prestador): array
    {
        $assinatura = $prestador->assinatura()->with('plano')->first();
        $hoje = CarbonImmutable::today(config('app.timezone'));

        if (in_array($prestador->status, ['bloqueado', 'cancelado'], true)) {
            return ['permitido' => false, 'alerta' => 'Conta bloqueada ou cancelada.'];
        }

        if (! $assinatura || ! $assinatura->plano?->ativo) {
            return ['permitido' => false, 'alerta' => 'Plano ou assinatura indisponivel.'];
        }

        if ($assinatura->acesso_liberado_ate && $hoje->lte($assinatura->acesso_liberado_ate)) {
            return ['permitido' => true, 'alerta' => 'Acesso temporario liberado manualmente.'];
        }

        if ($assinatura->periodo_gratuito_ate && $hoje->lte($assinatura->periodo_gratuito_ate)) {
            return ['permitido' => true, 'alerta' => null];
        }

        if ($assinatura->status === StatusAssinatura::Cancelada->value) {
            return ['permitido' => false, 'alerta' => 'Assinatura cancelada.'];
        }

        $mensalidadeVencida = $assinatura->mensalidades()
            ->where('status', 'vencida')
            ->orderBy('data_vencimento')
            ->first();

        if (! $mensalidadeVencida) {
            return ['permitido' => in_array($assinatura->status, ['ativa', 'teste'], true), 'alerta' => null];
        }

        $limiteTolerancia = CarbonImmutable::parse($mensalidadeVencida->data_vencimento)
            ->addDays($assinatura->plano->periodo_tolerancia_dias);

        if ($hoje->lte($limiteTolerancia)) {
            return ['permitido' => true, 'alerta' => 'Mensalidade vencida dentro do periodo de tolerancia.'];
        }

        return ['permitido' => false, 'alerta' => 'Acesso operacional suspenso por inadimplencia.'];
    }
}
