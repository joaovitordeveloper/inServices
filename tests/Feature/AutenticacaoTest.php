<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AutenticacaoTest extends TestCase
{
    use RefreshDatabase;

    public function test_tela_principal_redireciona_usuario_nao_autenticado_para_login(): void
    {
        $this->get('/')->assertRedirect('/login');
        $this->get('/painel')->assertRedirect('/login');
    }

    public function test_usuario_autenticado_acessa_painel(): void
    {
        User::factory()->create([
            'email' => 'prestador@example.com',
            'password' => Hash::make('senha-segura'),
        ]);

        $this->post('/login', [
            'email' => 'prestador@example.com',
            'senha' => 'senha-segura',
        ])->assertRedirect('/painel');

        $this->get('/painel')->assertOk();
    }
}
