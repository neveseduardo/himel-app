<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFinancialFixedExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'financial_category_uid' => ['sometimes', 'uuid'],
            'name' => ['sometimes', 'string', 'max:255'],
            'amount' => ['sometimes', 'numeric', 'min:0.01'],
            'due_day' => ['sometimes', 'integer', 'min:1', 'max:31'],
            'active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'financial_category_uid.uuid' => 'A categoria deve ser um UUID válido.',
            'name.string' => 'O nome deve ser um texto.',
            'name.max' => 'O nome não pode ter mais de 255 caracteres.',
            'amount.numeric' => 'O valor deve ser um número.',
            'amount.min' => 'O valor deve ser maior que zero.',
            'due_day.integer' => 'O dia de vencimento deve ser um número inteiro.',
            'due_day.min' => 'O dia de vencimento deve ser entre 1 e 31.',
            'due_day.max' => 'O dia de vencimento deve ser entre 1 e 31.',
        ];
    }
}
