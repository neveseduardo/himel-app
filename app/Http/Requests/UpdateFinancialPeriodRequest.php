<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFinancialPeriodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'month' => ['sometimes', 'integer', 'min:1', 'max:12'],
            'year' => ['sometimes', 'integer', 'min:2000', 'max:2100'],
        ];
    }

    public function messages(): array
    {
        return [
            'month.integer' => 'O mês deve ser um número inteiro.',
            'month.min' => 'O mês deve ser entre 1 e 12.',
            'month.max' => 'O mês deve ser entre 1 e 12.',
            'year.integer' => 'O ano deve ser um número inteiro.',
            'year.min' => 'O ano deve ser maior que 2000.',
            'year.max' => 'O ano não pode exceder 2100.',
        ];
    }
}
