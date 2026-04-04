<?php

namespace App\Http\Requests;

use App\Models\FinancialTransaction;
use Illuminate\Foundation\Http\FormRequest;

class UpdateFinancialTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'financial_account_uid' => ['sometimes', 'uuid'],
            'financial_category_uid' => ['sometimes', 'uuid'],
            'amount' => ['sometimes', 'numeric', 'min:0.01'],
            'direction' => ['sometimes', 'string', 'in:'.implode(',', FinancialTransaction::getDirections())],
            'status' => ['sometimes', 'string', 'in:'.implode(',', FinancialTransaction::getStatuses())],
            'source' => ['sometimes', 'string', 'in:'.implode(',', FinancialTransaction::getSources())],
            'occurred_at' => ['sometimes', 'date'],
            'due_date' => ['nullable', 'date'],
            'paid_at' => ['nullable', 'date'],
            'reference_id' => ['nullable', 'uuid'],
        ];
    }

    public function messages(): array
    {
        return [
            'financial_account_uid.uuid' => 'A conta deve ser um UUID válido.',
            'financial_category_uid.uuid' => 'A categoria deve ser um UUID válido.',
            'amount.numeric' => 'O valor deve ser um número.',
            'amount.min' => 'O valor deve ser maior que zero.',
            'direction.in' => 'A direção deve ser: INFLOW ou OUTFLOW.',
            'status.in' => 'O status deve ser: PENDING, PAID ou OVERDUE.',
            'source.in' => 'A origem deve ser: MANUAL, CREDIT_CARD, FIXED ou TRANSFER.',
            'occurred_at.date' => 'A data de ocorrência deve ser uma data válida.',
            'due_date.date' => 'A data de vencimento deve ser uma data válida.',
            'paid_at.date' => 'A data de pagamento deve ser uma data válida.',
            'reference_id.uuid' => 'A referência deve ser um UUID válido.',
        ];
    }
}
