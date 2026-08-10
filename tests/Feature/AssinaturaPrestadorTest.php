<?php

namespace Tests\Feature;

use App\Models\Assinatura;
use App\Models\PerfilPrestador;
use App\Models\Plano;
use App\Models\Profissional;
use App\Models\Servico;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssinaturaPrestadorTest extends TestCase
{
    use RefreshDatabase;

    public function test_prestador_visualiza_e_troca_plano(): void
    {
        $usuario = User::factory()->create(['tipo' => 'prestador']);
        $prestador = PerfilPrestador::create([
            'usuario_id' => $usuario->id,
            'nome_publico' => 'Assinatura Teste',
            'slug' => 'assinatura-teste',
            'status' => 'ativo',
        ]);
        $planoAtual = Plano::create(['nome' => 'Basico', 'valor_mensal' => 19.90, 'ativo' => true]);
        $novoPlano = Plano::create(['nome' => 'Plus', 'valor_mensal' => 29.90, 'ativo' => true]);
        Assinatura::create([
            'prestador_id' => $prestador->id,
            'plano_id' => $planoAtual->id,
            'status' => 'ativa',
            'data_inicio' => now()->toDateString(),
            'data_proximo_vencimento' => now()->addMonth()->toDateString(),
        ]);

        $this->actingAs($usuario)
            ->get(route('prestador.assinatura.edit'))
            ->assertOk()
            ->assertSee('Basico')
            ->assertSee('Plus');

        $this->actingAs($usuario)
            ->put(route('prestador.assinatura.update'), ['plano_id' => $novoPlano->id])
            ->assertRedirect(route('prestador.assinatura.edit'));

        $this->assertDatabaseHas('assinaturas', [
            'prestador_id' => $prestador->id,
            'plano_id' => $novoPlano->id,
        ]);
    }

    public function test_nao_troca_para_plano_abaixo_do_uso_atual(): void
    {
        $usuario = User::factory()->create(['tipo' => 'prestador']);
        $prestador = PerfilPrestador::create([
            'usuario_id' => $usuario->id,
            'nome_publico' => 'Assinatura Limite',
            'slug' => 'assinatura-limite',
            'status' => 'ativo',
        ]);
        $planoAtual = Plano::create(['nome' => 'Plus', 'valor_mensal' => 29.90, 'ativo' => true]);
        $planoLimitado = Plano::create([
            'nome' => 'Individual',
            'valor_mensal' => 19.90,
            'quantidade_maxima_servicos' => 1,
            'quantidade_maxima_profissionais' => 1,
            'ativo' => true,
        ]);
        Assinatura::create([
            'prestador_id' => $prestador->id,
            'plano_id' => $planoAtual->id,
            'status' => 'ativa',
            'data_inicio' => now()->toDateString(),
            'data_proximo_vencimento' => now()->addMonth()->toDateString(),
        ]);
        Servico::create(['prestador_id' => $prestador->id, 'nome' => 'Corte', 'slug' => 'corte', 'duracao_minutos' => 60, 'preco' => 45, 'status' => 'publicado']);
        Servico::create(['prestador_id' => $prestador->id, 'nome' => 'Barba', 'slug' => 'barba', 'duracao_minutos' => 30, 'preco' => 25, 'status' => 'publicado']);
        Profissional::create(['prestador_id' => $prestador->id, 'nome' => 'Ana', 'ativo' => true]);

        $this->actingAs($usuario)
            ->put(route('prestador.assinatura.update'), ['plano_id' => $planoLimitado->id])
            ->assertSessionHasErrors('plano_id');

        $this->assertDatabaseHas('assinaturas', [
            'prestador_id' => $prestador->id,
            'plano_id' => $planoAtual->id,
        ]);
    }
}
