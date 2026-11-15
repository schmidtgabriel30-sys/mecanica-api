<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // "login" aceita e-mail (clientes) ou nome de usuário (AdminA).
            // "email" continua aceito para compatibilidade.
            'login' => ['required_without:email', 'string', 'max:255'],
            'email' => ['required_without:login', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ];
    }
}
