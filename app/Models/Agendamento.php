<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Agendamento extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'agendamentos';

    protected $fillable = ['uuid_publico', 'protocolo_publico', 'prestador_id', 'cliente_id', 'servico_id', 'profissional_id', 'inicio_em', 'fim_em', 'status', 'chave_idempotencia', 'observacoes'];

    protected function casts(): array
    {
        return ['inicio_em' => 'datetime', 'fim_em' => 'datetime'];
    }

    public function uniqueIds(): array
    {
        return ['uuid_publico'];
    }

    public function prestador(): BelongsTo
    {
        return $this->belongsTo(PerfilPrestador::class, 'prestador_id');
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function servico(): BelongsTo
    {
        return $this->belongsTo(Servico::class, 'servico_id');
    }

    public function profissional(): BelongsTo
    {
        return $this->belongsTo(Profissional::class, 'profissional_id');
    }

    public function historicos(): HasMany
    {
        return $this->hasMany(HistoricoStatusAgendamento::class, 'agendamento_id');
    }
}
