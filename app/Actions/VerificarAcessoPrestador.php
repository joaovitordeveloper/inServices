<?php

namespace App\Actions;

use App\Models\PerfilPrestador;
use App\Services\ServicoAcessoPrestador;

class VerificarAcessoPrestador
{
    public function __construct(private readonly ServicoAcessoPrestador $servico) {}

    public function executar(PerfilPrestador $prestador): array
    {
        return $this->servico->verificar($prestador);
    }
}
