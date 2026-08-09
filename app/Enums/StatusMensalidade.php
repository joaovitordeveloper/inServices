<?php

namespace App\Enums;

enum StatusMensalidade: string
{
    case Pendente = 'pendente';
    case Paga = 'paga';
    case Vencida = 'vencida';
    case Cancelada = 'cancelada';
    case Isenta = 'isenta';
    case EmAnalise = 'em_analise';
}
