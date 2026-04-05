<?php

namespace App\Domain\Category\Requests;

use App\Domain\Category\Models\Category;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'direction' => ['sometimes', 'string', 'in:'.implode(',', Category::getDirections())],
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
