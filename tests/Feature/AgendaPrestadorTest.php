<?php

namespace Tests\Feature;

use App\Models\Assinatura;
use App\Models\PerfilPrestador;
use App\Models\Plano;
use App\Models\Profissional;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgendaPrestadorTest extends TestCase
{
    use RefreshDatabase;

    public function test_prestador_cadastra_horario_para_varios_dias_com_almoco(): void
    {
        $usuario = User::factory()->create(['tipo' => 'prestador']);
        $prestador = PerfilPrestador::create([
            'usuario_id' => $usuario->id,
            'nome_publico' => 'Agenda Teste',
            'slug' => 'agenda-teste',
            'status' => 'ativo',
        ]);
        $profissional = Profissional::create(['prestador_id' => $prestador->id, 'nome' => 'Teste', 'ativo' => true]);

        $this->actingAs($usuario)
            ->post(route('prestador.agenda.regras.store'), [
                'profissional_id' => $profissional->id,
                'dias_semana' => [1, 2, 3],
                'horario_inicio' => '08:00',
                'horario_fim' => '18:00',
                'almoco_inicio' => '12:00',
                'almoco_fim' => '13:00',
            ])
            ->assertRedirect(route('prestador.agenda.index'));

        foreach ([1, 2, 3] as $diaSemana) {
            $this->assertDatabaseHas('regras_disponibilidade', [
                'profissional_id' => $profissional->id,
                'dia_semana' => $diaSemana,
                'horario_inicio' => '08:00',
                'horario_fim' => '18:00',
                'almoco_inicio' => '12:00',
                'almoco_fim' => '13:00',
            ]);
        }
    }

    public function test_nao_cadastra_profissional_acima_do_limite_do_plano(): void
    {
        $usuario = User::factory()->create(['tipo' => 'prestador']);
        $prestador = PerfilPrestador::create([
            'usuario_id' => $usuario->id,
            'nome_publico' => 'Agenda Limite',
            'slug' => 'agenda-limite',
            'status' => 'ativo',
        ]);
        $plano = Plano::create([
            'nome' => 'Individual',
            'valor_mensal' => 29.90,
            'quantidade_maxima_profissionais' => 1,
            'ativo' => true,
        ]);
        Assinatura::create([
            'prestador_id' => $prestador->id,
            'plano_id' => $plano->id,
            'status' => 'teste',
            'data_inicio' => now()->toDateString(),
            'data_proximo_vencimento' => now()->addMonth()->toDateString(),
        ]);
        Profissional::create(['prestador_id' => $prestador->id, 'nome' => 'Ja existe', 'ativo' => true]);

        $this->actingAs($usuario)
            ->post(route('prestador.agenda.profissionais.store'), [
                'nome' => 'Segundo profissional',
            ])
            ->assertSessionHasErrors('nome');

        $this->assertSame(1, $prestador->profissionais()->count());
    }
}
