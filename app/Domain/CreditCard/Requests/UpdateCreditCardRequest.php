<?php

namespace App\Domain\CreditCard\Requests;

use App\Domain\CreditCard\Models\CreditCard;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCreditCardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'card_type' => ['sometimes', 'string', 'in:'.implode(',', CreditCard::getCardTypes())],
            'due_day' => ['sometimes', 'integer', 'min:1', 'max:31'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.string' => 'O nome deve ser um texto.',
            'name.max' => 'O nome não pode ter mais de 255 caracteres.',
            'card_type.in' => 'O tipo deve ser: PHYSICAL ou VIRTUAL.',
            'due_day.integer' => 'O dia de vencimento deve ser um número inteiro.',
            'due_day.min' => 'O dia de vencimento deve ser entre 1 e 31.',
            'due_day.max' => 'O dia de vencimento deve ser entre 1 e 31.',
        ];
    }
}
