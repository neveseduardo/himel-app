<?php

namespace App\Http\Requests;

use App\Models\FinancialCreditCard;
use Illuminate\Foundation\Http\FormRequest;

class StoreFinancialCreditCardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'card_type' => ['required', 'string', 'in:'.implode(',', FinancialCreditCard::getCardTypes())],
            'due_day' => ['required', 'integer', 'min:1', 'max:31'],
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
        ];
    }
}
