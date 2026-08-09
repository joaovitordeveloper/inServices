<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModeloMensagemWhatsapp extends Model
{
    use HasFactory;

    protected $table = 'modelos_mensagem_whatsapp';

    protected $fillable = ['prestador_id', 'nome', 'mensagem', 'ativo'];

    protected function casts(): array
    {
        return ['ativo' => 'boolean'];
    }
}
