<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Servico extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'servicos';

    protected $fillable = ['uuid_publico', 'prestador_id', 'nome', 'slug', 'descricao', 'duracao_minutos', 'intervalo_adicional_minutos', 'preco', 'imagem', 'status', 'ordem_exibicao', 'antecedencia_minima_minutos', 'limite_dias_futuros', 'permite_escolher_profissional'];

    protected function casts(): array
    {
        return ['preco' => 'decimal:2', 'permite_escolher_profissional' => 'boolean'];
    }

    public function uniqueIds(): array
    {
        return ['uuid_publico'];
    }

    public function prestador(): BelongsTo
    {
        return $this->belongsTo(PerfilPrestador::class, 'prestador_id');
    }

    public function profissionais(): BelongsToMany
    {
        return $this->belongsToMany(Profissional::class, 'profissional_servico')
            ->withPivot(['duracao_minutos', 'preco', 'intervalo_adicional_minutos', 'ativo', 'ordem_exibicao'])
            ->withTimestamps();
    }
}
