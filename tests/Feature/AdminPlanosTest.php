<?php

namespace Tests\Feature;

use App\Models\Assinatura;
use App\Models\PerfilPrestador;
use App\Models\Plano;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPlanosTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_master_cadastra_edita_e_inativa_plano(): void
    {
        $admin = User::factory()->create(['tipo' => 'administrador_geral']);

        $this->actingAs($admin)
            ->post('/admin/planos', [
                'nome' => 'Premium',
                'descricao' => 'Plano completo',
                'valor_mensal' => '1.999,90',
                'quantidade_maxima_servicos' => 50,
                'quantidade_maxima_profissionais' => 20,
                'quantidade_maxima_agendamentos_mes' => 1000,
                'periodo_tolerancia_dias' => 7,
                'permite_web_push' => '1',
                'permite_relatorios' => '1',
                'ativo' => '1',
            ])
            ->assertRedirect('/admin/planos');

        $plano = Plano::where('nome', 'Premium')->firstOrFail();

        $this->assertSame('1999.90', $plano->valor_mensal);
        $this->assertTrue($plano->permite_web_push);
        $this->assertTrue($plano->ativo);

        $this->actingAs($admin)
            ->put('/admin/planos/'.$plano->id, [
                'nome' => 'Premium Plus',
                'valor_mensal' => '249,90',
                'periodo_tolerancia_dias' => 10,
                'permite_relatorios' => '1',
                'ativo' => '1',
            ])
            ->assertRedirect('/admin/planos');

        $this->assertDatabaseHas('planos', [
            'id' => $plano->id,
            'nome' => 'Premium Plus',
            'periodo_tolerancia_dias' => 10,
        ]);

        $this->actingAs($admin)
            ->delete('/admin/planos/'.$plano->id)
            ->assertRedirect('/admin/planos');

        $this->assertFalse($plano->fresh()->ativo);
    }

    public function test_nao_inativa_plano_com_assinatura_vinculada(): void
    {
        $admin = User::factory()->create(['tipo' => 'administrador_geral']);
        $usuarioPrestador = User::factory()->create(['tipo' => 'prestador']);
        $plano = Plano::create([
            'nome' => 'Plano com prestador',
            'valor_mensal' => 99,
            'periodo_tolerancia_dias' => 5,
            'ativo' => true,
        ]);
        $prestador = PerfilPrestador::create([
            'usuario_id' => $usuarioPrestador->id,
            'nome_publico' => 'Prestador Vinculado',
            'slug' => 'prestador-vinculado',
            'status' => 'ativo',
        ]);

        Assinatura::create([
            'prestador_id' => $prestador->id,
            'plano_id' => $plano->id,
            'status' => 'ativa',
            'data_inicio' => now()->toDateString(),
            'data_proximo_vencimento' => now()->addMonth()->toDateString(),
        ]);

        $this->actingAs($admin)
            ->delete('/admin/planos/'.$plano->id)
            ->assertRedirect('/admin/planos')
            ->assertSessionHas('erro');

        $this->assertTrue($plano->fresh()->ativo);
    }
}
