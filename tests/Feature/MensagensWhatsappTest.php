<?php

namespace Tests\Feature;

use App\Models\Agendamento;
use App\Models\Cliente;
use App\Models\ModeloMensagemWhatsapp;
use App\Models\PerfilPrestador;
use App\Models\Profissional;
use App\Models\Servico;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MensagensWhatsappTest extends TestCase
{
    use RefreshDatabase;

    public function test_prestador_configura_mensagem_usada_no_whatsapp_do_painel(): void
    {
        $usuario = User::factory()->create(['tipo' => 'prestador']);
        $prestador = PerfilPrestador::create([
            'usuario_id' => $usuario->id,
            'nome_publico' => 'Salao Mensagem',
            'slug' => 'salao-mensagem',
            'status' => 'ativo',
        ]);
        $cliente = Cliente::create([
            'prestador_id' => $prestador->id,
            'nome' => 'Maria',
            'telefone' => '(11) 98888-7777',
            'telefone_normalizado' => '5511988887777',
        ]);
        $servico = Servico::create([
            'prestador_id' => $prestador->id,
            'nome' => 'Corte',
            'slug' => 'corte',
            'duracao_minutos' => 60,
            'preco' => 80,
            'status' => 'publicado',
        ]);
        $profissional = Profissional::create(['prestador_id' => $prestador->id, 'nome' => 'Ana', 'ativo' => true]);
        Agendamento::create([
            'prestador_id' => $prestador->id,
            'cliente_id' => $cliente->id,
            'servico_id' => $servico->id,
            'profissional_id' => $profissional->id,
            'inicio_em' => now()->addDay()->setTime(10, 0),
            'fim_em' => now()->addDay()->setTime(11, 0),
            'status' => 'pendente',
            'origem' => 'publico',
            'protocolo_publico' => 'AG-TESTE',
            'chave_idempotencia' => 'teste-whatsapp',
        ]);

        $this->actingAs($usuario)
            ->put(route('prestador.mensagens-whatsapp.update'), [
                'mensagem' => 'Oi {nome_cliente}, seu {servico} com {profissional} ficou para {data} as {horario}.',
            ])
            ->assertRedirect(route('prestador.mensagens-whatsapp.edit'));

        $this->assertDatabaseHas('modelos_mensagem_whatsapp', [
            'prestador_id' => $prestador->id,
            'nome' => 'contato',
            'mensagem' => 'Oi {nome_cliente}, seu {servico} com {profissional} ficou para {data} as {horario}.',
        ]);

        $this->actingAs($usuario)
            ->get(route('prestador.painel'))
            ->assertOk()
            ->assertSee(rawurlencode('Ola, Maria tudo bem?'), false)
            ->assertSee(rawurlencode('Seu horario dia '.now()->addDay()->format('d/m/Y').' as 10:00 esta confirmado!'), false)
            ->assertSee(rawurlencode('Corte - R$ 80,00'), false)
            ->assertSee(rawurlencode('Oi Maria, seu Corte com Ana ficou para '.now()->addDay()->format('d/m/Y').' as 10:00.'), false);

        $this->assertSame(1, ModeloMensagemWhatsapp::count());
    }
}
