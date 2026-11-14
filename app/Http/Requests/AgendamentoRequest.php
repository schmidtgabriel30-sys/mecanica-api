<?php

namespace App\Http\Requests;

use App\Models\Agendamento;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AgendamentoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $store = $this->isMethod('post');
        $req = $store ? 'required' : 'sometimes';

        return [
            'veiculo_id' => $store
                ? ['required', Rule::exists('veiculos', 'id')->where('user_id', $this->user()->id)]
                : ['prohibited'],
            'servico_id' => $store
                ? ['required', Rule::exists('servicos', 'id')->where('ativo', true)]
                : ['prohibited'],
            'mecanico_id' => ['nullable', Rule::exists('mecanicos', 'id')->where('ativo', true)],
            'data_agendamento' => [$req, 'date', 'after_or_equal:today'],
            'periodo' => [$req, Rule::in(Agendamento::PERIODOS)],
            'observacoes' => ['nullable', 'string', 'max:500'],
            'status' => $store ? ['prohibited'] : ['sometimes', Rule::in(Agendamento::STATUS)],
        ];
    }
}
