<?php

namespace App\Http\Requests;

use App\Support\Moeda;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class SalvarServicoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->tipo === 'prestador';
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'preco' => Moeda::paraDecimal($this->input('preco')),
            'slug' => Str::slug($this->input('nome')),
        ]);
    }

    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255'],
            'descricao' => ['nullable', 'string'],
            'duracao_minutos' => ['required', 'integer', 'min:5', 'max:1440'],
            'intervalo_adicional_minutos' => ['required', 'integer', 'min:0', 'max:240'],
            'preco' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:rascunho,publicado,inativo'],
            'ordem_exibicao' => ['required', 'integer', 'min:0'],
            'antecedencia_minima_minutos' => ['required', 'integer', 'min:0'],
            'limite_dias_futuros' => ['required', 'integer', 'min:1', 'max:365'],
            'permite_escolher_profissional' => ['nullable', 'boolean'],
            'profissionais' => ['array'],
            'profissionais.*' => ['integer', 'exists:profissionais,id'],
        ];
    }
}
