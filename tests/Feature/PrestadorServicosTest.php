<?php

namespace Tests\Feature;

use App\Models\Assinatura;
use App\Models\PerfilPrestador;
use App\Models\Plano;
use App\Models\Profissional;
use App\Models\Servico;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PrestadorServicosTest extends TestCase
{
    use RefreshDatabase;

    public function test_prestador_cadastra_servico_com_foto(): void
    {
        Storage::fake('public');

        $usuario = User::factory()->create(['tipo' => 'prestador']);
        $prestador = PerfilPrestador::create([
            'usuario_id' => $usuario->id,
            'nome_publico' => 'Prestador Servico',
            'slug' => 'prestador-servico',
            'status' => 'ativo',
        ]);
        $profissional = Profissional::create(['prestador_id' => $prestador->id, 'nome' => 'Maria', 'ativo' => true]);

        $this->actingAs($usuario)
            ->post('/painel/servicos', [
                'nome' => 'Corte especial',
                'descricao' => 'Servico com foto',
                'duracao_minutos' => 45,
                'intervalo_adicional_minutos' => 15,
                'preco' => '89,90',
                'status' => 'publicado',
                'ordem_exibicao' => 1,
                'antecedencia_minima_minutos' => 60,
                'limite_dias_futuros' => 30,
                'permite_escolher_profissional' => '1',
                'profissionais' => [$profissional->id],
                'imagem' => $this->imagemJpegFake(),
            ])
            ->assertRedirect('/painel/servicos');

        $this->assertDatabaseHas('servicos', [
            'prestador_id' => $prestador->id,
            'nome' => 'Corte especial',
            'preco' => '89.90',
        ]);

        Storage::disk('public')->assertExists($prestador->servicos()->firstOrFail()->imagem);
    }

    public function test_nao_cadastra_servico_acima_do_limite_do_plano(): void
    {
        $usuario = User::factory()->create(['tipo' => 'prestador']);
        $prestador = PerfilPrestador::create([
            'usuario_id' => $usuario->id,
            'nome_publico' => 'Prestador Limite',
            'slug' => 'prestador-limite',
            'status' => 'ativo',
        ]);
        $plano = Plano::create([
            'nome' => 'Servico unico',
            'valor_mensal' => 19.90,
            'quantidade_maxima_servicos' => 1,
            'ativo' => true,
        ]);
        Assinatura::create([
            'prestador_id' => $prestador->id,
            'plano_id' => $plano->id,
            'status' => 'teste',
            'data_inicio' => now()->toDateString(),
            'data_proximo_vencimento' => now()->addMonth()->toDateString(),
        ]);
        Servico::create([
            'prestador_id' => $prestador->id,
            'nome' => 'Ja existe',
            'slug' => 'ja-existe',
            'duracao_minutos' => 30,
            'status' => 'publicado',
        ]);

        $this->actingAs($usuario)
            ->post(route('prestador.servicos.store'), [
                'nome' => 'Segundo servico',
                'duracao_minutos' => 30,
                'intervalo_adicional_minutos' => 0,
                'preco' => '10,00',
                'status' => 'publicado',
                'ordem_exibicao' => 1,
                'antecedencia_minima_minutos' => 30,
                'limite_dias_futuros' => 30,
            ])
            ->assertSessionHasErrors('nome');

        $this->assertSame(1, $prestador->servicos()->count());
    }

    private function imagemJpegFake(): UploadedFile
    {
        $arquivo = tempnam(sys_get_temp_dir(), 'servico');
        file_put_contents($arquivo, base64_decode('/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAP//////////////////////////////////////////////////////////////////////////////////////2wBDAf//////////////////////////////////////////////////////////////////////////////////////wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAX/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIQAxAAAAH/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/9oACAEBAAEFAqf/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oACAEDAQE/ASP/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oACAECAQE/ASP/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/9oACAEBAAY/Al//xAAUEAEAAAAAAAAAAAAAAAAAAAAA/9oACAEBAAE/IV//2gAMAwEAAgADAAAAEP/EFBQRAQAAAAAAAAAAAAAAAAAAARD/2gAIAQMBAT8QH//EFBQRAQAAAAAAAAAAAAAAAAAAARD/2gAIAQIBAT8QH//EFBQBAQAAAAAAAAAAAAAAAAAAARD/2gAIAQEAAT8QH//Z'));

        return new UploadedFile($arquivo, 'servico.jpg', 'image/jpeg', null, true);
    }
}
