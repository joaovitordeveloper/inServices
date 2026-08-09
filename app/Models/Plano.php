<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plano extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'planos';

    protected $fillable = ['uuid_publico', 'nome', 'descricao', 'valor_mensal', 'quantidade_maxima_servicos', 'quantidade_maxima_profissionais', 'quantidade_maxima_agendamentos_mes', 'permite_web_push', 'permite_relatorios', 'periodo_tolerancia_dias', 'ativo'];

    protected function casts(): array
    {
        return ['valor_mensal' => 'decimal:2', 'permite_web_push' => 'boolean', 'permite_relatorios' => 'boolean', 'ativo' => 'boolean'];
    }

    public function uniqueIds(): array
    {
        return ['uuid_publico'];
    }

    public function assinaturas(): HasMany
    {
        return $this->hasMany(Assinatura::class, 'plano_id');
    }
}
