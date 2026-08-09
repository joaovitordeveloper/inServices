<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notificacao extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'notificacoes';

    protected $fillable = ['uuid_publico', 'destinatario_type', 'destinatario_id', 'prestador_id', 'titulo', 'corpo', 'url', 'dados', 'lida_em'];

    protected function casts(): array
    {
        return ['dados' => 'array', 'lida_em' => 'datetime'];
    }

    public function uniqueIds(): array
    {
        return ['uuid_publico'];
    }
}
