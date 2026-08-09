<?php

namespace App\Services;

class ServicoTelefone
{
    public function normalizarBrasil(?string $telefone): ?string
    {
        if ($telefone === null) {
            return null;
        }

        $numeros = preg_replace('/\D+/', '', $telefone);

        if ($numeros === '') {
            return null;
        }

        if (str_starts_with($numeros, '55')) {
            return $numeros;
        }

        if (strlen($numeros) >= 10 && strlen($numeros) <= 11) {
            return '55'.$numeros;
        }

        return $numeros;
    }

    public function telefoneValidoParaWhatsapp(?string $telefone): bool
    {
        $normalizado = $this->normalizarBrasil($telefone);

        return $normalizado !== null && strlen($normalizado) >= 12 && strlen($normalizado) <= 14;
    }
}
