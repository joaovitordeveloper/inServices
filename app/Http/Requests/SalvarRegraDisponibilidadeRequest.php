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
            'dias_semana' => ['required', 'array', 'min:1'],
            'dias_semana.*' => ['integer', 'between:0,6'],
            'horario_inicio' => ['required', 'date_format:H:i'],
            'horario_fim' => ['required', 'date_format:H:i', 'after:horario_inicio'],
            'almoco_inicio' => ['nullable', 'required_with:almoco_fim', 'date_format:H:i', 'after:horario_inicio', 'before:horario_fim'],
            'almoco_fim' => ['nullable', 'required_with:almoco_inicio', 'date_format:H:i', 'after:almoco_inicio', 'before:horario_fim'],
            'ativo' => ['nullable', 'boolean'],
        ];
    }
}
