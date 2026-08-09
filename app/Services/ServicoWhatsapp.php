<?php

namespace App\Services;

use InvalidArgumentException;

class ServicoWhatsapp
{
    public function __construct(private readonly ServicoTelefone $telefones) {}

    public function gerarLink(string $telefone, string $mensagem): string
    {
        $normalizado = $this->telefones->normalizarBrasil($telefone);

        if (! $this->telefones->telefoneValidoParaWhatsapp($normalizado)) {
            throw new InvalidArgumentException('Telefone invalido para WhatsApp.');
        }

        return 'https://wa.me/'.$normalizado.'?text='.rawurlencode($mensagem);
    }

    public function preencherModelo(string $modelo, array $dados): string
    {
        foreach ($dados as $chave => $valor) {
            $modelo = str_replace('{'.$chave.'}', (string) $valor, $modelo);
        }

        return $modelo;
    }
}
