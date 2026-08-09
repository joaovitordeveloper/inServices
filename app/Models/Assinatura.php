<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assinatura extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'assinaturas';

    protected $fillable = ['uuid_publico', 'prestador_id', 'plano_id', 'status', 'data_inicio', 'data_proximo_vencimento', 'periodo_gratuito_ate', 'acesso_liberado_ate', 'data_cancelamento', 'renovacao_automatica', 'observacoes'];

    protected function casts(): array
    {
        return ['data_inicio' => 'date', 'data_proximo_vencimento' => 'date', 'periodo_gratuito_ate' => 'date', 'acesso_liberado_ate' => 'date', 'data_cancelamento' => 'date', 'renovacao_automatica' => 'boolean'];
    }

    public function uniqueIds(): array
    {
        return ['uuid_publico'];
    }

    public function prestador(): BelongsTo
    {
        return $this->belongsTo(PerfilPrestador::class, 'prestador_id');
    }

    public function plano(): BelongsTo
    {
        return $this->belongsTo(Plano::class, 'plano_id');
    }

    public function mensalidades(): HasMany
    {
        return $this->hasMany(Mensalidade::class, 'assinatura_id');
    }
}
