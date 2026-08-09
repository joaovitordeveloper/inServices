<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SalvarRegraDisponibilidadeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->tipo === 'prestador';
    }

    public function rules(): array
    {
        return [
            'profissional_id' => ['required', 'integer', 'exists:profissionais,id'],
            'dia_semana' => ['required', 'integer', 'between:0,6'],
            'horario_inicio' => ['required', 'date_format:H:i'],
            'horario_fim' => ['required', 'date_format:H:i', 'after:horario_inicio'],
            'ativo' => ['nullable', 'boolean'],
        ];
    }
}
