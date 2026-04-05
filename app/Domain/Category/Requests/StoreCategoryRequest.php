<?php

namespace App\Domain\Category\Requests;

use App\Domain\Category\Models\Category;
use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'direction' => ['required', 'string', 'in:'.implode(',', Category::getDirections())],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome da categoria é obrigatório.',
            'name.string' => 'O nome deve ser um texto.',
            'name.max' => 'O nome não pode ter mais de 255 caracteres.',
            'direction.required' => 'A direção é obrigatória.',
            'direction.in' => 'A direção deve ser: INFLOW ou OUTFLOW.',
        ];
    }
}
