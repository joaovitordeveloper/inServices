<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegraDisponibilidade extends Model
{
    use HasFactory;

    protected $table = 'regras_disponibilidade';

    protected $fillable = ['profissional_id', 'dia_semana', 'horario_inicio', 'horario_fim', 'almoco_inicio', 'almoco_fim', 'ativo'];

    protected function casts(): array
    {
        return ['ativo' => 'boolean'];
    }

    public function profissional(): BelongsTo
    {
        return $this->belongsTo(Profissional::class, 'profissional_id');
    }
}
