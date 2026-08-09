<?php

namespace App\Http\Requests;

use App\Support\Moeda;
use Illuminate\Foundation\Http\FormRequest;

class SalvarPlanoRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'valor_mensal' => Moeda::paraDecimal($this->input('valor_mensal')),
        ]);
    }

    public function authorize(): bool
    {
        return $this->user()?->tipo === 'administrador_geral';
    }

    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:255'],
            'descricao' => ['nullable', 'string'],
            'valor_mensal' => ['required', 'numeric', 'min:0'],
            'quantidade_maxima_servicos' => ['nullable', 'integer', 'min:1'],
            'quantidade_maxima_profissionais' => ['nullable', 'integer', 'min:1'],
            'quantidade_maxima_agendamentos_mes' => ['nullable', 'integer', 'min:1'],
            'periodo_tolerancia_dias' => ['required', 'integer', 'min:0', 'max:60'],
            'permite_web_push' => ['nullable', 'boolean'],
            'permite_relatorios' => ['nullable', 'boolean'],
            'ativo' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'nome.required' => 'Informe o nome do plano.',
            'valor_mensal.required' => 'Informe o valor mensal.',
            'valor_mensal.numeric' => 'Informe um valor mensal valido.',
            'periodo_tolerancia_dias.required' => 'Informe a tolerancia em dias.',
        ];
    }
}
