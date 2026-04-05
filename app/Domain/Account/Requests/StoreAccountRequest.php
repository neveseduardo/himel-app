<?php

namespace App\Domain\Account\Requests;

use App\Domain\Account\Models\Account;
use Illuminate\Foundation\Http\FormRequest;

class StoreAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:'.implode(',', Account::getTypes())],
            'balance' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome da conta é obrigatório.',
            'name.string' => 'O nome deve ser um texto.',
            'name.max' => 'O nome não pode ter mais de 255 caracteres.',
            'type.required' => 'O tipo da conta é obrigatório.',
            'type.in' => 'O tipo deve ser: CHECKING, SAVINGS, CASH ou OTHER.',
            'balance.numeric' => 'O saldo deve ser um número.',
            'balance.min' => 'O saldo não pode ser negativo.',
        ];
    }
}
