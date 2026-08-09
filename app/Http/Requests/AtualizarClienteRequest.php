<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AtualizarClienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->tipo === 'prestador';
    }

    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:255'],
            'telefone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'observacoes' => ['nullable', 'string'],
        ];
    }
}
