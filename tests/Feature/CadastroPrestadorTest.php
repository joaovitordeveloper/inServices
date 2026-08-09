<?php

namespace Tests\Feature;

use App\Models\PerfilPrestador;
use App\Models\Plano;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CadastroPrestadorTest extends TestCase
{
    use RefreshDatabase;

    public function test_prestador_cria_conta_com_plano_e_aparece_para_admin_master(): void
    {
        $plano = Plano::create([
            'nome' => 'Inicial',
            'descricao' => 'Plano inicial',
            'valor_mensal' => 99,
            'ativo' => true,
        ]);

        $this->post('/cadastro', [
            'plano_id' => $plano->id,
            'nome_responsavel' => 'Ana Responsavel',
            'nome_publico' => 'Clinica Ana',
            'email' => 'ana@example.com',
            'telefone' => '(11) 97777-8888',
            'senha' => 'senha-segura',
            'senha_confirmation' => 'senha-segura',
            'aceite_privacidade' => '1',
        ])->assertRedirect('/painel');

        $this->assertDatabaseHas('users', ['email' => 'ana@example.com', 'tipo' => 'prestador']);
        $this->assertDatabaseHas('perfis_prestadores', ['nome_publico' => 'Clinica Ana', 'status' => 'teste']);
        $this->assertDatabaseHas('assinaturas', ['plano_id' => $plano->id, 'status' => 'teste']);

        $admin = User::factory()->create(['tipo' => 'administrador_geral']);

        $this->actingAs($admin)
            ->get('/admin/prestadores')
            ->assertOk()
            ->assertSee('Clinica Ana');
    }

    public function test_admin_master_ativa_e_desativa_prestador(): void
    {
        $admin = User::factory()->create(['tipo' => 'administrador_geral']);
        $prestadorUsuario = User::factory()->create(['tipo' => 'prestador']);
        $prestador = PerfilPrestador::create([
            'usuario_id' => $prestadorUsuario->id,
            'nome_publico' => 'Prestador Status',
            'slug' => 'prestador-status',
            'status' => 'ativo',
        ]);

        $this->actingAs($admin)
            ->patch('/admin/prestadores/'.$prestador->id.'/status')
            ->assertRedirect('/admin/prestadores');

        $this->assertSame('suspenso', $prestador->fresh()->status);

        $this->actingAs($admin)
            ->patch('/admin/prestadores/'.$prestador->id.'/status')
            ->assertRedirect('/admin/prestadores');

        $this->assertSame('ativo', $prestador->fresh()->status);
    }
}
