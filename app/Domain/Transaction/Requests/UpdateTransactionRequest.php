<?php

namespace App\Domain\Transaction\Requests;

use App\Domain\Transaction\Models\Transaction;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'account_uid' => ['sometimes', 'uuid'],
            'category_uid' => ['sometimes', 'uuid'],
            'amount' => ['sometimes', 'numeric', 'min:0.01'],
            'direction' => ['sometimes', 'string', 'in:'.implode(',', Transaction::getDirections())],
            'status' => ['sometimes', 'string', 'in:'.implode(',', Transaction::getStatuses())],
            'source' => ['sometimes', 'string', 'in:'.implode(',', Transaction::getSources())],
            'occurred_at' => ['sometimes', 'date'],
            'due_date' => ['nullable', 'date'],
            'paid_at' => ['nullable', 'date'],
            'reference_id' => ['nullable', 'uuid'],
        ];
    }

    public function messages(): array
    {
        return [
            'account_uid.uuid' => 'A conta deve ser um UUID válido.',
            'category_uid.uuid' => 'A categoria deve ser um UUID válido.',
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
