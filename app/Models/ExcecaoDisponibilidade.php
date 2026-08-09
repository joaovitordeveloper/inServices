<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExcecaoDisponibilidade extends Model
{
    use HasFactory;

    protected $table = 'excecoes_disponibilidade';

    protected $fillable = ['profissional_id', 'data', 'horario_inicio', 'horario_fim', 'tipo', 'motivo'];

    protected function casts(): array
    {
        return ['data' => 'date'];
    }

    public function profissional(): BelongsTo
    {
        return $this->belongsTo(Profissional::class, 'profissional_id');
    }
}
