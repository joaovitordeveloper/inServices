<?php

namespace Tests\Feature;

use App\Models\Assinatura;
use App\Models\Cliente;
use App\Models\PerfilPrestador;
use App\Models\Plano;
use App\Models\Profissional;
use App\Models\RegraDisponibilidade;
use App\Models\Servico;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgendamentoPublicoTest extends TestCase
{
    use RefreshDatabase;

    public function test_link_publico_pede_cliente_antes_de_mostrar_servicos(): void
    {
        $prestador = PerfilPrestador::create([
            'usuario_id' => User::factory()->create()->id,
            'nome_publico' => 'Salao Publico',
            'slug' => 'salao-publico',
            'status' => 'ativo',
        ]);

        $plano = Plano::create([
            'nome' => 'Profissional',
            'valor_mensal' => 149.90,
            'ativo' => true,
        ]);

        Assinatura::create([
            'prestador_id' => $prestador->id,
            'plano_id' => $plano->id,
            'status' => 'teste',
            'data_inicio' => now()->toDateString(),
            'data_proximo_vencimento' => now()->addMonth()->toDateString(),
            'periodo_gratuito_ate' => now()->addDays(7)->toDateString(),
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
        $profissional->servicos()->attach($servico->id, ['ativo' => true]);
        RegraDisponibilidade::create([
            'profissional_id' => $profissional->id,
            'dia_semana' => 1,
            'horario_inicio' => '09:00',
            'horario_fim' => '11:00',
        ]);

        $rotaIndex = route('publico.agendamento.index', ['prestador' => $prestador->uuid_publico]);
        $rotaServico = route('publico.agendamento.servico', [
            'prestador' => $prestador->uuid_publico,
            'servico' => $servico->uuid_publico,
        ]);

        $this->assertStringContainsString($prestador->uuid_publico, $rotaIndex);
        $this->assertStringNotContainsString($prestador->slug, $rotaIndex);

        $this->get($rotaIndex)
            ->assertOk()
            ->assertSee('Antes de mostrar os servicos')
            ->assertDontSee('Corte');

        $this->get($rotaServico)
            ->assertRedirect($rotaIndex);

        $this->post(route('publico.agendamento.identificar', ['prestador' => $prestador->uuid_publico]), [
            'nome' => 'Cliente Teste',
            'telefone' => '(11) 99999-0000',
        ])->assertRedirect($rotaIndex);

        $this->get($rotaIndex)
            ->assertOk()
            ->assertSee('Cliente Teste')
            ->assertSee('Corte');

        $this->assertDatabaseHas('clientes', [
            'prestador_id' => $prestador->id,
            'telefone_normalizado' => '5511999990000',
        ]);

        $this->postJson(route('publico.agendamento.confirmar', [
            'prestador' => $prestador->uuid_publico,
            'servico' => $servico->uuid_publico,
        ]), [
            'profissional_id' => $profissional->id,
            'inicio' => '2026-08-10 09:00:00',
            'fim' => '2026-08-10 10:00:00',
        ])
            ->assertCreated()
            ->assertJsonPath('agendamento.servico', 'Corte');

        $this->assertDatabaseHas('agendamentos', [
            'prestador_id' => $prestador->id,
            'servico_id' => $servico->id,
            'profissional_id' => $profissional->id,
            'status' => 'pendente',
        ]);
        $this->assertDatabaseHas('notificacoes', [
            'prestador_id' => $prestador->id,
            'destinatario_type' => Cliente::class,
            'titulo' => 'Agendamento confirmado',
        ]);
    }
}
