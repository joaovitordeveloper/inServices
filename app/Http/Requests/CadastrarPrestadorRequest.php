<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class CadastrarPrestadorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'plano_id' => ['required', 'exists:planos,id'],
            'nome_responsavel' => ['required', 'string', 'max:255'],
            'nome_publico' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'telefone' => ['required', 'string', 'max:20'],
            'senha' => ['required', 'confirmed', Password::min(8)],
            'aceite_privacidade' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'plano_id.required' => 'Escolha um plano.',
            'nome_responsavel.required' => 'Informe o nome do responsavel.',
            'nome_publico.required' => 'Informe o nome publico do prestador.',
            'email.required' => 'Informe o e-mail.',
            'email.email' => 'Informe um e-mail valido.',
            'email.unique' => 'Este e-mail ja esta cadastrado.',
            'telefone.required' => 'Informe o telefone.',
            'senha.required' => 'Informe a senha.',
            'senha.confirmed' => 'A confirmacao da senha nao confere.',
            'aceite_privacidade.accepted' => 'Aceite a politica de privacidade para continuar.',
        ];
    }
}
