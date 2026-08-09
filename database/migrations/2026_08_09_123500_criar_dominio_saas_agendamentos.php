<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->uuid('uuid_publico')->after('id')->unique();
            $table->string('tipo')->after('email')->default('prestador')->index();
            $table->string('telefone')->nullable()->after('tipo');
            $table->string('telefone_normalizado')->nullable()->after('telefone')->index();
        });

        Schema::create('planos', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid_publico')->unique();
            $table->string('nome');
            $table->text('descricao')->nullable();
            $table->decimal('valor_mensal', 10, 2);
            $table->unsignedInteger('quantidade_maxima_servicos')->nullable();
            $table->unsignedInteger('quantidade_maxima_profissionais')->nullable();
            $table->unsignedInteger('quantidade_maxima_agendamentos_mes')->nullable();
            $table->boolean('permite_web_push')->default(true);
            $table->boolean('permite_relatorios')->default(true);
            $table->unsignedInteger('periodo_tolerancia_dias')->default(5);
            $table->boolean('ativo')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('perfis_prestadores', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid_publico')->unique();
            $table->foreignId('usuario_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('nome_publico');
            $table->string('slug')->unique();
            $table->text('descricao')->nullable();
            $table->string('telefone')->nullable();
            $table->string('telefone_normalizado')->nullable();
            $table->string('email_publico')->nullable();
            $table->string('endereco')->nullable();
            $table->string('status')->default('pendente')->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('assinaturas', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid_publico')->unique();
            $table->foreignId('prestador_id')->unique()->constrained('perfis_prestadores')->cascadeOnDelete();
            $table->foreignId('plano_id')->constrained('planos');
            $table->string('status')->default('teste')->index();
            $table->date('data_inicio');
            $table->date('data_proximo_vencimento');
            $table->date('periodo_gratuito_ate')->nullable();
            $table->date('acesso_liberado_ate')->nullable();
            $table->date('data_cancelamento')->nullable();
            $table->boolean('renovacao_automatica')->default(false);
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });

        Schema::create('mensalidades', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid_publico')->unique();
            $table->foreignId('prestador_id')->constrained('perfis_prestadores')->cascadeOnDelete();
            $table->foreignId('assinatura_id')->constrained('assinaturas')->cascadeOnDelete();
            $table->foreignId('plano_id')->constrained('planos');
            $table->date('competencia');
            $table->decimal('valor_original', 10, 2);
            $table->decimal('desconto', 10, 2)->default(0);
            $table->decimal('acrescimo', 10, 2)->default(0);
            $table->decimal('valor_final', 10, 2);
            $table->date('data_emissao');
            $table->date('data_vencimento');
            $table->date('data_pagamento')->nullable();
            $table->string('status')->default('pendente')->index();
            $table->string('forma_pagamento')->nullable();
            $table->string('referencia_externa')->nullable();
            $table->text('observacao')->nullable();
            $table->timestamps();
            $table->unique(['assinatura_id', 'competencia']);
            $table->index(['prestador_id', 'status', 'data_vencimento']);
        });

        Schema::create('pagamentos', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid_publico')->unique();
            $table->foreignId('mensalidade_id')->constrained('mensalidades')->cascadeOnDelete();
            $table->decimal('valor', 10, 2);
            $table->date('data_pagamento');
            $table->string('forma_pagamento');
            $table->string('status')->default('em_analise')->index();
            $table->string('referencia_externa')->nullable();
            $table->string('comprovante')->nullable();
            $table->foreignId('confirmado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('confirmado_em')->nullable();
            $table->text('observacao')->nullable();
            $table->timestamps();
        });

        Schema::create('profissionais', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid_publico')->unique();
            $table->foreignId('prestador_id')->constrained('perfis_prestadores')->cascadeOnDelete();
            $table->string('nome');
            $table->text('descricao')->nullable();
            $table->string('foto')->nullable();
            $table->string('telefone')->nullable();
            $table->string('telefone_normalizado')->nullable();
            $table->string('email')->nullable();
            $table->string('cargo')->nullable();
            $table->boolean('ativo')->default(true)->index();
            $table->unsignedInteger('ordem_exibicao')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['prestador_id', 'ativo', 'ordem_exibicao']);
        });

        Schema::create('servicos', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid_publico')->unique();
            $table->foreignId('prestador_id')->constrained('perfis_prestadores')->cascadeOnDelete();
            $table->string('nome');
            $table->string('slug');
            $table->text('descricao')->nullable();
            $table->unsignedInteger('duracao_minutos');
            $table->unsignedInteger('intervalo_adicional_minutos')->default(0);
            $table->decimal('preco', 10, 2)->nullable();
            $table->string('imagem')->nullable();
            $table->string('status')->default('rascunho')->index();
            $table->unsignedInteger('ordem_exibicao')->default(0);
            $table->unsignedInteger('antecedencia_minima_minutos')->default(60);
            $table->unsignedInteger('limite_dias_futuros')->default(30);
            $table->boolean('permite_escolher_profissional')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['prestador_id', 'slug']);
        });

        Schema::create('profissional_servico', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profissional_id')->constrained('profissionais')->cascadeOnDelete();
            $table->foreignId('servico_id')->constrained('servicos')->cascadeOnDelete();
            $table->unsignedInteger('duracao_minutos')->nullable();
            $table->decimal('preco', 10, 2)->nullable();
            $table->unsignedInteger('intervalo_adicional_minutos')->nullable();
            $table->boolean('ativo')->default(true)->index();
            $table->unsignedInteger('ordem_exibicao')->default(0);
            $table->timestamps();
            $table->unique(['profissional_id', 'servico_id']);
        });

        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid_publico')->unique();
            $table->foreignId('prestador_id')->constrained('perfis_prestadores')->cascadeOnDelete();
            $table->string('nome');
            $table->string('telefone');
            $table->string('telefone_normalizado')->index();
            $table->string('email')->nullable();
            $table->text('observacoes')->nullable();
            $table->timestamp('consentimento_privacidade_em')->nullable();
            $table->timestamp('anonimizado_em')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['prestador_id', 'telefone_normalizado']);
        });

        Schema::create('dispositivos_clientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->string('token_hash', 128)->unique();
            $table->string('nome_dispositivo')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('expira_em');
            $table->timestamp('revogado_em')->nullable();
            $table->timestamp('ultimo_uso_em')->nullable();
            $table->timestamps();
        });

        Schema::create('regras_disponibilidade', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profissional_id')->constrained('profissionais')->cascadeOnDelete();
            $table->unsignedTinyInteger('dia_semana');
            $table->time('horario_inicio');
            $table->time('horario_fim');
            $table->boolean('ativo')->default(true);
            $table->timestamps();
            $table->index(['profissional_id', 'dia_semana', 'ativo']);
        });

        Schema::create('excecoes_disponibilidade', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profissional_id')->constrained('profissionais')->cascadeOnDelete();
            $table->date('data');
            $table->time('horario_inicio')->nullable();
            $table->time('horario_fim')->nullable();
            $table->string('tipo')->default('bloqueio');
            $table->string('motivo')->nullable();
            $table->timestamps();
            $table->index(['profissional_id', 'data', 'tipo']);
        });

        Schema::create('agendamentos', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid_publico')->unique();
            $table->string('protocolo_publico')->unique();
            $table->foreignId('prestador_id')->constrained('perfis_prestadores')->cascadeOnDelete();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('servico_id')->constrained('servicos');
            $table->foreignId('profissional_id')->constrained('profissionais');
            $table->dateTime('inicio_em');
            $table->dateTime('fim_em');
            $table->string('status')->default('pendente')->index();
            $table->string('chave_idempotencia')->nullable();
            $table->text('observacoes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['profissional_id', 'inicio_em', 'fim_em', 'deleted_at'], 'agendamento_profissional_periodo_unico');
            $table->unique(['prestador_id', 'chave_idempotencia'], 'agendamento_idempotente');
            $table->index(['prestador_id', 'inicio_em', 'status']);
        });

        Schema::create('historicos_status_agendamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agendamento_id')->constrained('agendamentos')->cascadeOnDelete();
            $table->string('status_anterior')->nullable();
            $table->string('novo_status');
            $table->nullableMorphs('responsavel', 'hist_ag_resp_idx');
            $table->timestamp('registrado_em');
            $table->text('observacao')->nullable();
            $table->timestamps();
        });

        Schema::create('modelos_mensagem_whatsapp', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prestador_id')->nullable()->constrained('perfis_prestadores')->cascadeOnDelete();
            $table->string('nome');
            $table->text('mensagem');
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });

        Schema::create('notificacoes', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid_publico')->unique();
            $table->nullableMorphs('destinatario', 'notif_dest_idx');
            $table->foreignId('prestador_id')->nullable()->constrained('perfis_prestadores')->cascadeOnDelete();
            $table->string('titulo');
            $table->text('corpo');
            $table->string('url')->nullable();
            $table->json('dados')->nullable();
            $table->timestamp('lida_em')->nullable();
            $table->timestamps();
        });

        Schema::create('inscricoes_push', function (Blueprint $table) {
            $table->id();
            $table->morphs('inscrito', 'push_inscrito_idx');
            $table->text('endpoint');
            $table->text('chave_publica');
            $table->text('token_autenticacao');
            $table->string('codificacao')->default('aes128gcm');
            $table->string('nome_dispositivo')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('ultimo_uso_em')->nullable();
            $table->timestamp('revogado_em')->nullable();
            $table->timestamps();
            $table->unique('endpoint', 'inscricao_push_endpoint_unico');
        });

        Schema::create('preferencias_notificacoes', function (Blueprint $table) {
            $table->id();
            $table->morphs('preferivel', 'pref_notif_idx');
            $table->boolean('operacionais_web_push')->default(true);
            $table->boolean('operacionais_email')->default(false);
            $table->boolean('promocionais_web_push')->default(false);
            $table->boolean('promocionais_email')->default(false);
            $table->timestamps();
        });

        Schema::create('logs_auditoria', function (Blueprint $table) {
            $table->id();
            $table->nullableMorphs('ator', 'log_ator_idx');
            $table->string('acao')->index();
            $table->nullableMorphs('auditavel', 'log_auditavel_idx');
            $table->json('dados')->nullable();
            $table->string('ip')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });

        Schema::create('configuracoes', function (Blueprint $table) {
            $table->id();
            $table->string('chave')->unique();
            $table->json('valor')->nullable();
            $table->string('descricao')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach ([
            'configuracoes',
            'logs_auditoria',
            'preferencias_notificacoes',
            'inscricoes_push',
            'notificacoes',
            'modelos_mensagem_whatsapp',
            'historicos_status_agendamentos',
            'agendamentos',
            'excecoes_disponibilidade',
            'regras_disponibilidade',
            'dispositivos_clientes',
            'clientes',
            'profissional_servico',
            'servicos',
            'profissionais',
            'pagamentos',
            'mensalidades',
            'assinaturas',
            'perfis_prestadores',
            'planos',
        ] as $tabela) {
            Schema::dropIfExists($tabela);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['uuid_publico', 'tipo', 'telefone', 'telefone_normalizado']);
        });
    }
};
