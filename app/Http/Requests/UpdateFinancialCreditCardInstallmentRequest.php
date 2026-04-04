<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFinancialCreditCardInstallmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'credit_card_charge_uid' => ['sometimes', 'uuid'],
            'installment_number' => ['sometimes', 'integer', 'min:1'],
            'due_date' => ['sometimes', 'date'],
            'amount' => ['sometimes', 'numeric', 'min:0.01'],
            'paid_at' => ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'credit_card_charge_uid.uuid' => 'A compra deve ser um UUID válido.',
            'installment_number.integer' => 'O número da parcela deve ser um número inteiro.',
            'installment_number.min' => 'O número da parcela deve ser pelo menos 1.',
            'due_date.date' => 'A data de vencimento deve ser uma data válida.',
            'amount.numeric' => 'O valor deve ser um número.',
            'amount.min' => 'O valor deve ser maior que zero.',
            'paid_at.date' => 'A data de pagamento deve ser uma data válida.',
        ];
    }
}
