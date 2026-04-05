<?php

namespace App\Domain\Period\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePeriodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
        ];
    }

    public function messages(): array
    {
        return [
            'month.required' => 'O mês é obrigatório.',
            'month.integer' => 'O mês deve ser um número inteiro.',
            'month.min' => 'O mês deve ser entre 1 e 12.',
            'month.max' => 'O mês deve ser entre 1 e 12.',
            'year.required' => 'O ano é obrigatório.',
            'year.integer' => 'O ano deve ser um número inteiro.',
            'year.min' => 'O ano deve ser maior que 2000.',
            'year.max' => 'O ano não pode exceder 2100.',
        ];
    }
}
