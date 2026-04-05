<?php

namespace App\Domain\CreditCardInstallment\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCreditCardInstallmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'credit_card_charge_uid' => ['required', 'uuid'],
            'installment_number' => ['required', 'integer', 'min:1'],
            'due_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'paid_at' => ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'credit_card_charge_uid.required' => 'A compra é obrigatória.',
            'credit_card_charge_uid.uuid' => 'A compra deve ser um UUID válido.',
            'installment_number.required' => 'O número da parcela é obrigatório.',
            'installment_number.integer' => 'O número da parcela deve ser um número inteiro.',
            'installment_number.min' => 'O número da parcela deve ser pelo menos 1.',
            'due_date.required' => 'A data de vencimento é obrigatória.',
            'due_date.date' => 'A data de vencimento deve ser uma data válida.',
            'amount.required' => 'O valor é obrigatório.',
            'amount.numeric' => 'O valor deve ser um número.',
            'amount.min' => 'O valor deve ser maior que zero.',
            'paid_at.date' => 'A data de pagamento deve ser uma data válida.',
        ];
    }
}
