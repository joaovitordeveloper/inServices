<?php

namespace Tests\Feature;

use App\Models\Notificacao;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificacoesTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_lista_notificacoes_do_sino(): void
    {
        $usuario = User::factory()->create(['tipo' => 'prestador']);

        Notificacao::create([
            'destinatario_type' => User::class,
            'destinatario_id' => $usuario->id,
            'titulo' => 'Novo agendamento',
            'corpo' => 'Cliente agendou Corte.',
            'url' => route('prestador.painel'),
        ]);

        $this->actingAs($usuario)
            ->getJson(route('notificacoes.index'))
            ->assertOk()
            ->assertJsonPath('nao_lidas', 1)
            ->assertJsonPath('notificacoes.0.titulo', 'Novo agendamento');
    }
}
