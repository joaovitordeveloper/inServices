<?php

namespace App\Enums;

enum StatusAssinatura: string
{
    case Teste = 'teste';
    case Ativa = 'ativa';
    case Pendente = 'pendente';
    case Vencida = 'vencida';
    case Suspensa = 'suspensa';
    case Cancelada = 'cancelada';
}
