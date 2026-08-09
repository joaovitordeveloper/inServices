<?php

namespace App\Support;

class Moeda
{
    public static function paraDecimal(mixed $valor): ?string
    {
        if ($valor === null || $valor === '') {
            return null;
        }

        if (is_int($valor) || is_float($valor)) {
            return number_format((float) $valor, 2, '.', '');
        }

        $normalizado = preg_replace('/[^\d,.-]/', '', (string) $valor);

        if (str_contains($normalizado, ',')) {
            $normalizado = str_replace('.', '', $normalizado);
            $normalizado = str_replace(',', '.', $normalizado);
        }

        return is_numeric($normalizado) ? number_format((float) $normalizado, 2, '.', '') : null;
    }

    public static function paraReal(mixed $valor): string
    {
        return number_format((float) ($valor ?: 0), 2, ',', '.');
    }
}
