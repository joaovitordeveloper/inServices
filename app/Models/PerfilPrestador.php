<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class PerfilPrestador extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'perfis_prestadores';

    protected $fillable = ['uuid_publico', 'usuario_id', 'nome_publico', 'slug', 'descricao', 'telefone', 'telefone_normalizado', 'email_publico', 'endereco', 'status'];

    public function uniqueIds(): array
    {
        return ['uuid_publico'];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function assinatura(): HasOne
    {
        return $this->hasOne(Assinatura::class, 'prestador_id');
    }

    public function profissionais(): HasMany
    {
        return $this->hasMany(Profissional::class, 'prestador_id');
    }

    public function servicos(): HasMany
    {
        return $this->hasMany(Servico::class, 'prestador_id');
    }

    public function clientes(): HasMany
    {
        return $this->hasMany(Cliente::class, 'prestador_id');
    }

    public function agendamentos(): HasMany
    {
        return $this->hasMany(Agendamento::class, 'prestador_id');
    }
}
