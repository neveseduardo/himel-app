<?php

namespace App\Domain\CreditCard\Requests;

use App\Domain\CreditCard\Models\CreditCard;
use Illuminate\Foundation\Http\FormRequest;

class StoreCreditCardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'card_type' => ['required', 'string', 'in:'.implode(',', CreditCard::getCardTypes())],
            'due_day' => ['required', 'integer', 'min:1', 'max:31'],
            'closing_day' => ['required', 'integer', 'min:1', 'max:31'],
            'last_four_digits' => ['required', 'string', 'size:4'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome do cartão é obrigatório.',
            'name.string' => 'O nome deve ser um texto.',
            'name.max' => 'O nome não pode ter mais de 255 caracteres.',
            'card_type.required' => 'O tipo de cartão é obrigatório.',
            'card_type.in' => 'O tipo deve ser: PHYSICAL ou VIRTUAL.',
            'due_day.required' => 'O dia de vencimento é obrigatório.',
            'due_day.integer' => 'O dia de vencimento deve ser um número inteiro.',
            'due_day.min' => 'O dia de vencimento deve ser entre 1 e 31.',
            'due_day.max' => 'O dia de vencimento deve ser entre 1 e 31.',
            'closing_day.required' => 'O dia de fechamento é obrigatório.',
            'closing_day.integer' => 'O dia de fechamento deve ser um número inteiro.',
            'closing_day.min' => 'O dia de fechamento deve ser entre 1 e 31.',
            'closing_day.max' => 'O dia de fechamento deve ser entre 1 e 31.',
            'last_four_digits.required' => 'Os últimos 4 dígitos são obrigatórios.',
            'last_four_digits.size' => 'Deve ter exatamente 4 dígitos.',
        ];
    }
}
