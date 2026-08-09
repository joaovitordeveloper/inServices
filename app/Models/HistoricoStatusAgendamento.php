<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistoricoStatusAgendamento extends Model
{
    use HasFactory;

    protected $table = 'historicos_status_agendamentos';

    protected $fillable = ['agendamento_id', 'status_anterior', 'novo_status', 'responsavel_type', 'responsavel_id', 'registrado_em', 'observacao'];

    protected function casts(): array
    {
        return ['registrado_em' => 'datetime'];
    }

    public function agendamento(): BelongsTo
    {
        return $this->belongsTo(Agendamento::class, 'agendamento_id');
    }
}
