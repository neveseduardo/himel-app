<?php

namespace App\Domain\Transfer\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'from_account_uid' => ['required', 'uuid'],
            'to_account_uid' => ['required', 'uuid', 'different:from_account_uid'],
            'amount' => ['required', 'numeric', 'min:0.01'],
        ];
    }

    public function messages(): array
    {
        return [
            'from_account_uid.required' => 'A conta de origem é obrigatória.',
            'from_account_uid.uuid' => 'A conta de origem deve ser um UUID válido.',
            'to_account_uid.required' => 'A conta de destino é obrigatória.',
            'to_account_uid.uuid' => 'A conta de destino deve ser um UUID válido.',
            'to_account_uid.different' => 'A conta de destino deve ser diferente da conta de origem.',
            'amount.required' => 'O valor é obrigatório.',
            'amount.numeric' => 'O valor deve ser um número.',
            'amount.min' => 'O valor deve ser maior que zero.',
        ];
    }
}
