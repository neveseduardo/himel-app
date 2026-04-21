<?php

namespace App\Domain\FixedExpense\Resources;

use App\Domain\Category\Resources\CategoryResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FixedExpenseResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uid' => $this->uid,
            'description' => $this->name,
            'amount' => $this->amount,
            'due_day' => $this->due_day,
            'active' => $this->active,
            'category' => new CategoryResource($this->whenLoaded('category')),
        ];
    }
}
