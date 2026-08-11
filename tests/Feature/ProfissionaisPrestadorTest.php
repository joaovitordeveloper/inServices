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

class ProfissionaisPrestadorTest extends TestCase
{
    use RefreshDatabase;

    public function test_prestador_edita_e_alterna_status_do_profissional(): void
    {
        $usuario = User::factory()->create(['tipo' => 'prestador']);
        $prestador = PerfilPrestador::create([
            'usuario_id' => $usuario->id,
            'nome_publico' => 'Profissionais Teste',
            'slug' => 'profissionais-teste',
            'status' => 'ativo',
        ]);
        $profissional = Profissional::create(['prestador_id' => $prestador->id, 'nome' => 'Ana', 'ativo' => true]);

        $this->actingAs($usuario)
            ->put(route('prestador.profissionais.update', $profissional), [
                'nome' => 'Ana Silva',
                'cargo' => 'Cabeleireira',
                'telefone' => '(11) 98888-7777',
                'email' => 'ana@example.com',
            ])
            ->assertRedirect(route('prestador.profissionais.index'));

        $this->assertDatabaseHas('profissionais', [
            'id' => $profissional->id,
            'nome' => 'Ana Silva',
            'cargo' => 'Cabeleireira',
            'ativo' => true,
        ]);

        $this->actingAs($usuario)
            ->patch(route('prestador.profissionais.status', $profissional))
            ->assertRedirect(route('prestador.profissionais.index'));

        $this->assertDatabaseHas('profissionais', ['id' => $profissional->id, 'ativo' => false]);
    }

    public function test_nao_remove_profissional_com_agendamento_futuro(): void
    {
        $usuario = User::factory()->create(['tipo' => 'prestador']);
        $prestador = PerfilPrestador::create([
            'usuario_id' => $usuario->id,
            'nome_publico' => 'Profissional Ocupado',
            'slug' => 'profissional-ocupado',
            'status' => 'ativo',
        ]);
        $cliente = Cliente::create(['prestador_id' => $prestador->id, 'nome' => 'Cliente', 'telefone' => '(11) 99999-0000', 'telefone_normalizado' => '5511999990000']);
        $servico = Servico::create(['prestador_id' => $prestador->id, 'nome' => 'Corte', 'slug' => 'corte', 'duracao_minutos' => 60, 'preco' => 45, 'status' => 'publicado']);
        $profissional = Profissional::create(['prestador_id' => $prestador->id, 'nome' => 'Ana', 'ativo' => true]);
        Agendamento::create([
            'prestador_id' => $prestador->id,
            'cliente_id' => $cliente->id,
            'servico_id' => $servico->id,
            'profissional_id' => $profissional->id,
            'inicio_em' => now()->addDay(),
            'fim_em' => now()->addDay()->addHour(),
            'status' => 'pendente',
            'protocolo_publico' => 'AG-TESTE',
            'chave_idempotencia' => 'profissional-ocupado',
        ]);

        $this->actingAs($usuario)
            ->delete(route('prestador.profissionais.destroy', $profissional))
            ->assertRedirect(route('prestador.profissionais.index'))
            ->assertSessionHas('erro');

        $this->assertDatabaseHas('profissionais', ['id' => $profissional->id, 'deleted_at' => null]);
    }
}
