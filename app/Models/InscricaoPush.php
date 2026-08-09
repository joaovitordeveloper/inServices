<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InscricaoPush extends Model
{
    use HasFactory;

    protected $table = 'inscricoes_push';

    protected $fillable = ['inscrito_type', 'inscrito_id', 'endpoint', 'chave_publica', 'token_autenticacao', 'codificacao', 'nome_dispositivo', 'user_agent', 'ultimo_uso_em', 'revogado_em'];

    protected function casts(): array
    {
        return ['ultimo_uso_em' => 'datetime', 'revogado_em' => 'datetime'];
    }
}
