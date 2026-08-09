<?php

namespace App\Enums;

enum StatusAgendamento: string
{
    case Pendente = 'pendente';
    case Confirmado = 'confirmado';
    case Concluido = 'concluido';
    case CanceladoPeloPrestador = 'cancelado_pelo_prestador';
    case CanceladoPeloCliente = 'cancelado_pelo_cliente';
    case NaoCompareceu = 'nao_compareceu';
}
