<?php

namespace App\Domain\Transaction\Requests;

use App\Domain\Transaction\Models\Transaction;
use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('direction') === 'INFLOW') {
            $this->mergeIfMissing([
                'status' => 'PAID',
                'source' => 'MANUAL',
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'account_uid' => ['required', 'uuid'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'direction' => ['required', 'string', 'in:'.implode(',', Transaction::getDirections())],
            'occurred_at' => ['required', 'date'],
            'category_uid' => ['required_if:direction,OUTFLOW', 'nullable', 'uuid'],
            'status' => ['required_if:direction,OUTFLOW', 'nullable', 'string', 'in:'.implode(',', Transaction::getStatuses())],
            'source' => ['required_if:direction,OUTFLOW', 'nullable', 'string', 'in:'.implode(',', Transaction::getSources())],
            'due_date' => ['nullable', 'date'],
            'paid_at' => ['nullable', 'date'],
            'reference_id' => ['nullable', 'uuid'],
            'description' => ['nullable', 'string', 'max:255'],
            'period_uid' => ['nullable', 'uuid', 'exists:periods,uid'],
        ];
    }

    public function messages(): array
    {
        return [
            'account_uid.required' => 'A conta é obrigatória.',
            'account_uid.uuid' => 'A conta deve ser um UUID válido.',
            'category_uid.required_if' => 'A categoria é obrigatória para saídas.',
            'category_uid.uuid' => 'A categoria deve ser um UUID válido.',
            'amount.required' => 'O valor é obrigatório.',
            'amount.numeric' => 'O valor deve ser um número.',
            'amount.min' => 'O valor deve ser maior que zero.',
            'direction.required' => 'A direção é obrigatória.',
            'direction.in' => 'A direção deve ser: INFLOW ou OUTFLOW.',
            'status.required_if' => 'O status é obrigatório para saídas.',
            'status.in' => 'O status deve ser: PENDING, PAID ou OVERDUE.',
            'source.required_if' => 'A origem é obrigatória para saídas.',
            'source.in' => 'A origem deve ser: MANUAL, CREDIT_CARD, FIXED ou TRANSFER.',
            'occurred_at.required' => 'A data de ocorrência é obrigatória.',
            'occurred_at.date' => 'A data de ocorrência deve ser uma data válida.',
            'due_date.date' => 'A data de vencimento deve ser uma data válida.',
            'paid_at.date' => 'A data de pagamento deve ser uma data válida.',
            'reference_id.uuid' => 'A referência deve ser um UUID válido.',
            'period_uid.uuid' => 'O período deve ser um UUID válido.',
            'period_uid.exists' => 'O período informado não existe.',
        ];
    }
}
