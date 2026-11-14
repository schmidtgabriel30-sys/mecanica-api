<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ServicoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $req = $this->isMethod('post') ? 'required' : 'sometimes';

        return [
            'nome' => [$req, 'string', 'max:120'],
            'descricao' => ['nullable', 'string', 'max:500'],
            'preco' => [$req, 'numeric', 'min:0'],
            'duracao_minutos' => [$req, 'integer', 'between:10,600'],
            'ativo' => ['sometimes', 'boolean'],
        ];
    }
}
