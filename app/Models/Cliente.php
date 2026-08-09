<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cliente extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'clientes';

    protected $fillable = ['uuid_publico', 'prestador_id', 'nome', 'telefone', 'telefone_normalizado', 'email', 'observacoes', 'consentimento_privacidade_em', 'anonimizado_em'];

    protected function casts(): array
    {
        return ['consentimento_privacidade_em' => 'datetime', 'anonimizado_em' => 'datetime'];
    }

    public function uniqueIds(): array
    {
        return ['uuid_publico'];
    }

    public function prestador(): BelongsTo
    {
        return $this->belongsTo(PerfilPrestador::class, 'prestador_id');
    }

    public function agendamentos(): HasMany
    {
        return $this->hasMany(Agendamento::class, 'cliente_id');
    }

    public function dispositivos(): HasMany
    {
        return $this->hasMany(DispositivoCliente::class, 'cliente_id');
    }
}
