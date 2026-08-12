<?php

namespace Tests\Feature;

use App\Models\Agendamento;
use App\Models\Cliente;
use App\Models\PerfilPrestador;
use App\Models\Profissional;
use App\Models\Servico;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PainelPrestadorDadosTest extends TestCase
{
    use RefreshDatabase;

    public function test_prestador_consulta_dados_atualizados_da_home(): void
    {
        $usuario = User::factory()->create(['tipo' => 'prestador']);
        $prestador = PerfilPrestador::create([
            'usuario_id' => $usuario->id,
            'nome_publico' => 'Painel Teste',
            'slug' => 'painel-teste',
            'status' => 'ativo',
        ]);
        $cliente = Cliente::create(['prestador_id' => $prestador->id, 'nome' => 'Cliente', 'telefone' => '(11) 99999-0000', 'telefone_normalizado' => '5511999990000']);
        $servico = Servico::create(['prestador_id' => $prestador->id, 'nome' => 'Corte', 'slug' => 'corte', 'duracao_minutos' => 60, 'preco' => 50, 'status' => 'publicado']);
        $profissional = Profissional::create(['prestador_id' => $prestador->id, 'nome' => 'Ana', 'ativo' => true]);

        Agendamento::create([
            'prestador_id' => $prestador->id,
            'cliente_id' => $cliente->id,
            'servico_id' => $servico->id,
            'profissional_id' => $profissional->id,
            'inicio_em' => now()->setTime(10, 0),
            'fim_em' => now()->setTime(11, 0),
            'status' => 'pendente',
            'protocolo_publico' => 'AG-HOME',
            'chave_idempotencia' => 'home-dados',
        ]);

        $this->actingAs($usuario)
            ->getJson(route('prestador.painel.dados'))
            ->assertOk()
            ->assertJsonPath('metricas.atendimentos_mes', 1)
            ->assertJsonPath('metricas.recebido_mes', 'R$ 50,00')
            ->assertJsonPath('agenda_hoje.0.nome', 'Ana')
            ->assertJsonPath('resumo_profissionais.0.total_mes', 1);
    }
}
