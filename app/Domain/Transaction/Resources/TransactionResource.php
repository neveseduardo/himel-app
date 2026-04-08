<?php

namespace App\Domain\Transaction\Resources;

use App\Domain\Account\Resources\AccountResource;
use App\Domain\Category\Resources\CategoryResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uid' => $this->uid,
            'amount' => $this->amount,
            'direction' => $this->direction,
            'status' => $this->status,
            'source' => $this->source,
            'description' => $this->description,
            'occurred_at' => $this->occurred_at,
            'due_date' => $this->due_date,
            'paid_at' => $this->paid_at,
            'reference_id' => $this->reference_id,
            'account' => new AccountResource($this->whenLoaded('account')),
            'category' => new CategoryResource($this->whenLoaded('category')),
        ];
    }
}
