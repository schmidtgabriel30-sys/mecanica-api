<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VeiculoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('placa')) {
            $this->merge([
                'placa' => strtoupper(str_replace(['-', ' '], '', (string) $this->input('placa'))),
            ]);
        }
    }

    public function rules(): array
    {
        $req = $this->isMethod('post') ? 'required' : 'sometimes';

        return [
            'marca' => [$req, 'string', 'max:60'],
            'modelo' => [$req, 'string', 'max:80'],
            'placa' => [$req, 'string', 'max:10', Rule::unique('veiculos', 'placa')->ignore($this->route('veiculo'))],
            'ano' => [$req, 'integer', 'between:1950,'.((int) date('Y') + 1)],
        ];
    }
}
