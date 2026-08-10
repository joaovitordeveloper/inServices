<?php

namespace Database\Seeders;

use App\Models\Assinatura;
use App\Models\Cliente;
use App\Models\Mensalidade;
use App\Models\ModeloMensagemWhatsapp;
use App\Models\PerfilPrestador;
use App\Models\Plano;
use App\Models\Profissional;
use App\Models\RegraDisponibilidade;
use App\Models\Servico;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(AdministradorMasterSeeder::class);

        $adminNome = env('ADMIN_GERAL_NOME', 'Administrador Geral');
        $adminEmail = env('ADMIN_GERAL_EMAIL');
        $adminSenha = env('ADMIN_GERAL_SENHA');

        if ($adminEmail && $adminSenha) {
            User::firstOrCreate(
                ['email' => $adminEmail],
                ['name' => $adminNome, 'tipo' => 'administrador_geral', 'password' => Hash::make($adminSenha)]
            );
        }

        $plano = Plano::firstOrCreate(
            ['nome' => 'Profissional'],
            [
                'descricao' => 'Plano para prestadores com equipe e agenda publica.',
                'valor_mensal' => 149.90,
                'quantidade_maxima_servicos' => 30,
                'quantidade_maxima_profissionais' => 15,
                'quantidade_maxima_agendamentos_mes' => 600,
                'periodo_tolerancia_dias' => 5,
                'ativo' => true,
            ]
        );

        $usuarioPrestador = User::firstOrCreate(
            ['email' => 'demo@inservices.local'],
            ['name' => 'Salao Demo', 'tipo' => 'prestador', 'password' => Hash::make(Str::random(32))]
        );

        $prestador = PerfilPrestador::firstOrCreate(
            ['slug' => 'demo'],
            [
                'usuario_id' => $usuarioPrestador->id,
                'nome_publico' => 'Salao Demo',
                'descricao' => 'Ambiente de demonstracao com multiplos profissionais.',
                'telefone' => '(11) 99999-0000',
                'telefone_normalizado' => '5511999990000',
                'email_publico' => 'demo@inservices.local',
                'status' => 'ativo',
            ]
        );

        $assinatura = Assinatura::firstOrCreate(
            ['prestador_id' => $prestador->id],
            [
                'plano_id' => $plano->id,
                'status' => 'ativa',
                'data_inicio' => now()->startOfMonth(),
                'data_proximo_vencimento' => now()->addMonth()->startOfMonth(),
                'renovacao_automatica' => false,
            ]
        );

        Mensalidade::firstOrCreate(
            ['assinatura_id' => $assinatura->id, 'competencia' => now()->startOfMonth()->toDateString()],
            [
                'prestador_id' => $prestador->id,
                'plano_id' => $plano->id,
                'valor_original' => $plano->valor_mensal,
                'valor_final' => $plano->valor_mensal,
                'data_emissao' => now(),
                'data_vencimento' => now()->addDays(10),
                'status' => 'pendente',
            ]
        );

        $servico = Servico::firstOrCreate(
            ['prestador_id' => $prestador->id, 'slug' => 'corte-de-cabelo'],
            [
                'nome' => 'Corte de cabelo',
                'descricao' => 'Atendimento com horario marcado.',
                'duracao_minutos' => 45,
                'intervalo_adicional_minutos' => 15,
                'preco' => 80,
                'status' => 'publicado',
                'ordem_exibicao' => 1,
                'permite_escolher_profissional' => true,
            ]
        );

        foreach ([['Joao', 'Cabeleireiro'], ['Maria', 'Especialista em cortes']] as $indice => [$nome, $cargo]) {
            $profissional = Profissional::firstOrCreate(
                ['prestador_id' => $prestador->id, 'nome' => $nome],
                ['cargo' => $cargo, 'ativo' => true, 'ordem_exibicao' => $indice + 1]
            );

            $profissional->servicos()->syncWithoutDetaching([$servico->id => ['ativo' => true, 'ordem_exibicao' => $indice + 1]]);

            foreach ([1, 2, 3, 4, 5] as $diaSemana) {
                RegraDisponibilidade::firstOrCreate([
                    'profissional_id' => $profissional->id,
                    'dia_semana' => $diaSemana,
                    'horario_inicio' => $indice === 0 ? '08:00' : '09:00',
                    'horario_fim' => $indice === 0 ? '18:00' : '17:00',
                ]);
            }
        }

        Cliente::firstOrCreate(
            ['prestador_id' => $prestador->id, 'telefone_normalizado' => '5511988887777'],
            ['nome' => 'Cliente Demo', 'telefone' => '(11) 98888-7777', 'consentimento_privacidade_em' => now()]
        );

        foreach ([
            'confirmacao' => 'Ola, {nome_cliente}. Seu agendamento para {servico} com {profissional} esta confirmado para {data} as {horario}.',
            'contato' => '',
            'lembrete' => 'Ola, {nome_cliente}. Este e um lembrete do seu agendamento de {servico} com {profissional} em {data} as {horario}.',
        ] as $nome => $mensagem) {
            ModeloMensagemWhatsapp::firstOrCreate(['prestador_id' => $prestador->id, 'nome' => $nome], ['mensagem' => $mensagem, 'ativo' => true]);
        }
    }
}
