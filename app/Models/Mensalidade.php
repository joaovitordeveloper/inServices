<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mensalidade extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'mensalidades';

    protected $fillable = ['uuid_publico', 'prestador_id', 'assinatura_id', 'plano_id', 'competencia', 'valor_original', 'desconto', 'acrescimo', 'valor_final', 'data_emissao', 'data_vencimento', 'data_pagamento', 'status', 'forma_pagamento', 'referencia_externa', 'observacao'];

    protected function casts(): array
    {
        return ['competencia' => 'date', 'data_emissao' => 'date', 'data_vencimento' => 'date', 'data_pagamento' => 'date', 'valor_original' => 'decimal:2', 'desconto' => 'decimal:2', 'acrescimo' => 'decimal:2', 'valor_final' => 'decimal:2'];
    }

    public function uniqueIds(): array
    {
        return ['uuid_publico'];
    }

    public function prestador(): BelongsTo
    {
        return $this->belongsTo(PerfilPrestador::class, 'prestador_id');
    }

    public function assinatura(): BelongsTo
    {
        return $this->belongsTo(Assinatura::class, 'assinatura_id');
    }

    public function pagamentos(): HasMany
    {
        return $this->hasMany(Pagamento::class, 'mensalidade_id');
    }
}
