<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EntrarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'senha' => ['required', 'string'],
            'lembrar' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Informe o e-mail.',
            'email.email' => 'Informe um e-mail valido.',
            'senha.required' => 'Informe a senha.',
        ];
    }
}
