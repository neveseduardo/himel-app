<?php

namespace App\Http\Requests;

use App\Models\FinancialCategory;
use Illuminate\Foundation\Http\FormRequest;

class UpdateFinancialCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'direction' => ['sometimes', 'string', 'in:'.implode(',', FinancialCategory::getDirections())],
        ];
    }

    public function messages(): array
    {
        return [
            'name.string' => 'O nome deve ser um texto.',
            'name.max' => 'O nome não pode ter mais de 255 caracteres.',
            'direction.in' => 'A direção deve ser: INFLOW ou OUTFLOW.',
        ];
    }
}
