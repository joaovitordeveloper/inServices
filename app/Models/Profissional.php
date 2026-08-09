<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Profissional extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'profissionais';

    protected $fillable = ['uuid_publico', 'prestador_id', 'nome', 'descricao', 'foto', 'telefone', 'telefone_normalizado', 'email', 'cargo', 'ativo', 'ordem_exibicao'];

    protected function casts(): array
    {
        return ['ativo' => 'boolean'];
    }

    public function uniqueIds(): array
    {
        return ['uuid_publico'];
    }

    public function prestador(): BelongsTo
    {
        return $this->belongsTo(PerfilPrestador::class, 'prestador_id');
    }

    public function servicos(): BelongsToMany
    {
        return $this->belongsToMany(Servico::class, 'profissional_servico')
            ->withPivot(['duracao_minutos', 'preco', 'intervalo_adicional_minutos', 'ativo', 'ordem_exibicao'])
            ->withTimestamps();
    }

    public function regrasDisponibilidade(): HasMany
    {
        return $this->hasMany(RegraDisponibilidade::class, 'profissional_id');
    }

    public function excecoesDisponibilidade(): HasMany
    {
        return $this->hasMany(ExcecaoDisponibilidade::class, 'profissional_id');
    }

    public function agendamentos(): HasMany
    {
        return $this->hasMany(Agendamento::class, 'profissional_id');
    }
}
