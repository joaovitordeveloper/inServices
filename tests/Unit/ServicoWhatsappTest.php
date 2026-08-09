<?php

namespace Tests\Unit;

use App\Services\ServicoTelefone;
use App\Services\ServicoWhatsapp;
use PHPUnit\Framework\TestCase;

class ServicoWhatsappTest extends TestCase
{
    public function test_normaliza_telefone_brasileiro_e_codifica_mensagem(): void
    {
        $servico = new ServicoWhatsapp(new ServicoTelefone);

        $link = $servico->gerarLink('(11) 98888-7777', 'Ola, Cliente Demo. Confirmado as 14:00.');

        $this->assertStringStartsWith('https://wa.me/5511988887777?text=', $link);
        $this->assertStringContainsString('Confirmado%20as%2014%3A00', $link);
    }
}
