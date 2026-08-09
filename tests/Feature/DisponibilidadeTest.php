<?php

namespace Tests\Feature;

use App\Models\PerfilPrestador;
use App\Models\Profissional;
use App\Models\RegraDisponibilidade;
use App\Models\Servico;
use App\Models\User;
use App\Services\CalcularHorariosDisponiveis;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DisponibilidadeTest extends TestCase
{
    use RefreshDatabase;

    public function test_oferece_multiplas_vagas_no_mesmo_horario_para_profissionais_diferentes(): void
    {
        $prestador = PerfilPrestador::create([
            'usuario_id' => User::factory()->create()->id,
            'nome_publico' => 'Prestador Teste',
            'slug' => 'prestador-teste',
            'status' => 'ativo',
        ]);

        $servico = Servico::create([
            'prestador_id' => $prestador->id,
            'nome' => 'Corte',
            'slug' => 'corte',
            'duracao_minutos' => 60,
            'status' => 'publicado',
        ]);

        foreach (['Joao', 'Maria'] as $nome) {
            $profissional = Profissional::create(['prestador_id' => $prestador->id, 'nome' => $nome, 'ativo' => true]);
            $profissional->servicos()->attach($servico->id, ['ativo' => true]);
            RegraDisponibilidade::create(['profissional_id' => $profissional->id, 'dia_semana' => 1, 'horario_inicio' => '14:00', 'horario_fim' => '16:00']);
        }

        $horarios = app(CalcularHorariosDisponiveis::class)->executar($servico, CarbonImmutable::parse('2026-08-10'));

        $this->assertCount(2, $horarios->where('inicio', '2026-08-10 14:00:00'));
    }
}
