<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFinancialCreditCardChargeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'credit_card_uid' => ['sometimes', 'uuid'],
            'amount' => ['sometimes', 'numeric', 'min:0.01'],
            'description' => ['sometimes', 'string', 'max:255'],
            'total_installments' => ['sometimes', 'integer', 'min:1', 'max:48'],
        ];
    }

    public function messages(): array
    {
        return [
            'credit_card_uid.uuid' => 'O cartão de crédito deve ser um UUID válido.',
            'amount.numeric' => 'O valor deve ser um número.',
            'amount.min' => 'O valor deve ser maior que zero.',
            'description.string' => 'A descrição deve ser um texto.',
            'description.max' => 'A descrição não pode ter mais de 255 caracteres.',
            'total_installments.integer' => 'O número de parcelas deve ser um número inteiro.',
            'total_installments.min' => 'O número de parcelas deve ser pelo menos 1.',
            'total_installments.max' => 'O número de parcelas não pode exceder 48.',
        ];
    }
}
