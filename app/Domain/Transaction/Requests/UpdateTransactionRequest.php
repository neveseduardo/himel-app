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

    protected function prepareForValidation(): void
    {
        $direction = $this->input('direction');
        if (! $direction) {
            $transaction = Transaction::where('uid', $this->route('uid'))->first();
            $direction = $transaction?->direction;
        }

        if ($direction === 'INFLOW') {
            $this->mergeIfMissing([
                'status' => 'PAID',
                'source' => 'MANUAL',
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'account_uid' => ['sometimes', 'uuid'],
            'amount' => ['sometimes', 'numeric', 'min:0.01'],
            'direction' => ['sometimes', 'string', 'in:'.implode(',', Transaction::getDirections())],
            'occurred_at' => ['sometimes', 'date'],
            'category_uid' => ['required_if:direction,OUTFLOW', 'nullable', 'uuid'],
            'status' => ['required_if:direction,OUTFLOW', 'nullable', 'string', 'in:'.implode(',', Transaction::getStatuses())],
            'source' => ['required_if:direction,OUTFLOW', 'nullable', 'string', 'in:'.implode(',', Transaction::getSources())],
            'due_date' => ['nullable', 'date'],
            'paid_at' => ['nullable', 'date'],
            'reference_id' => ['nullable', 'uuid'],
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'account_uid.uuid' => 'A conta deve ser um UUID válido.',
            'category_uid.required_if' => 'A categoria é obrigatória para saídas.',
            'category_uid.uuid' => 'A categoria deve ser um UUID válido.',
            'amount.numeric' => 'O valor deve ser um número.',
            'amount.min' => 'O valor deve ser maior que zero.',
            'direction.in' => 'A direção deve ser: INFLOW ou OUTFLOW.',
            'status.required_if' => 'O status é obrigatório para saídas.',
            'status.in' => 'O status deve ser: PENDING, PAID ou OVERDUE.',
            'source.required_if' => 'A origem é obrigatória para saídas.',
            'source.in' => 'A origem deve ser: MANUAL, CREDIT_CARD, FIXED ou TRANSFER.',
            'occurred_at.date' => 'A data de ocorrência deve ser uma data válida.',
            'due_date.date' => 'A data de vencimento deve ser uma data válida.',
            'paid_at.date' => 'A data de pagamento deve ser uma data válida.',
            'reference_id.uuid' => 'A referência deve ser um UUID válido.',
        ];
    }
}
