<?php

namespace App\Http\Requests;

use App\Models\FinancialTransaction;
use Illuminate\Foundation\Http\FormRequest;

class StoreFinancialTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'financial_account_uid' => ['required', 'uuid'],
            'financial_category_uid' => ['required', 'uuid'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'direction' => ['required', 'string', 'in:'.implode(',', FinancialTransaction::getDirections())],
            'status' => ['required', 'string', 'in:'.implode(',', FinancialTransaction::getStatuses())],
            'source' => ['required', 'string', 'in:'.implode(',', FinancialTransaction::getSources())],
            'occurred_at' => ['required', 'date'],
            'due_date' => ['nullable', 'date'],
            'paid_at' => ['nullable', 'date'],
            'reference_id' => ['nullable', 'uuid'],
        ];
    }

    public function messages(): array
    {
        return [
            'financial_account_uid.required' => 'A conta é obrigatória.',
            'financial_account_uid.uuid' => 'A conta deve ser um UUID válido.',
            'financial_category_uid.required' => 'A categoria é obrigatória.',
            'financial_category_uid.uuid' => 'A categoria deve ser um UUID válido.',
            'amount.required' => 'O valor é obrigatório.',
            'amount.numeric' => 'O valor deve ser um número.',
            'amount.min' => 'O valor deve ser maior que zero.',
            'direction.required' => 'A direção é obrigatória.',
            'direction.in' => 'A direção deve ser: INFLOW ou OUTFLOW.',
            'status.required' => 'O status é obrigatório.',
            'status.in' => 'O status deve ser: PENDING, PAID ou OVERDUE.',
            'source.required' => 'A origem é obrigatória.',
            'source.in' => 'A origem deve ser: MANUAL, CREDIT_CARD, FIXED ou TRANSFER.',
            'occurred_at.required' => 'A data de ocorrência é obrigatória.',
            'occurred_at.date' => 'A data de ocorrência deve ser uma data válida.',
            'due_date.date' => 'A data de vencimento deve ser uma data válida.',
            'paid_at.date' => 'A data de pagamento deve ser uma data válida.',
            'reference_id.uuid' => 'A referência deve ser um UUID válido.',
        ];
    }
}
