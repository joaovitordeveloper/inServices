<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pagamento extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'pagamentos';

    protected $fillable = ['uuid_publico', 'mensalidade_id', 'valor', 'data_pagamento', 'forma_pagamento', 'status', 'referencia_externa', 'comprovante', 'confirmado_por', 'confirmado_em', 'observacao'];

    protected function casts(): array
    {
        return ['valor' => 'decimal:2', 'data_pagamento' => 'date', 'confirmado_em' => 'datetime'];
    }

    public function uniqueIds(): array
    {
        return ['uuid_publico'];
    }

    public function mensalidade(): BelongsTo
    {
        return $this->belongsTo(Mensalidade::class, 'mensalidade_id');
    }
}
