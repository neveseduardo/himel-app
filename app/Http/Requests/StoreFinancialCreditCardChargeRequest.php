<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFinancialCreditCardChargeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'credit_card_uid' => ['required', 'uuid'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'description' => ['required', 'string', 'max:255'],
            'total_installments' => ['required', 'integer', 'min:1', 'max:48'],
        ];
    }

    public function messages(): array
    {
        return [
            'credit_card_uid.required' => 'O cartão de crédito é obrigatório.',
            'credit_card_uid.uuid' => 'O cartão de crédito deve ser um UUID válido.',
            'amount.required' => 'O valor é obrigatório.',
            'amount.numeric' => 'O valor deve ser um número.',
            'amount.min' => 'O valor deve ser maior que zero.',
            'description.required' => 'A descrição é obrigatória.',
            'description.string' => 'A descrição deve ser um texto.',
            'description.max' => 'A descrição não pode ter mais de 255 caracteres.',
            'total_installments.required' => 'O número de parcelas é obrigatório.',
            'total_installments.integer' => 'O número de parcelas deve ser um número inteiro.',
            'total_installments.min' => 'O número de parcelas deve ser pelo menos 1.',
            'total_installments.max' => 'O número de parcelas não pode exceder 48.',
        ];
    }
}
