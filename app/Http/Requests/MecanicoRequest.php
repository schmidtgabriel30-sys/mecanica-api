<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MecanicoRequest extends FormRequest
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
            'especialidade' => ['nullable', 'string', 'max:120'],
            'telefone' => ['nullable', 'string', 'max:20'],
            'ativo' => ['sometimes', 'boolean'],
        ];
    }
}
