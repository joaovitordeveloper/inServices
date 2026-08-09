<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DispositivoCliente extends Model
{
    use HasFactory;

    protected $table = 'dispositivos_clientes';

    protected $fillable = ['cliente_id', 'token_hash', 'nome_dispositivo', 'user_agent', 'expira_em', 'revogado_em', 'ultimo_uso_em'];

    protected function casts(): array
    {
        return ['expira_em' => 'datetime', 'revogado_em' => 'datetime', 'ultimo_uso_em' => 'datetime'];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }
}
