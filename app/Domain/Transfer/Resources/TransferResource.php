<?php

namespace App\Domain\Transfer\Resources;

use App\Domain\Account\Resources\AccountResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransferResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uid' => $this->uid,
            'amount' => $this->amount,
            'created_at' => $this->created_at,
            'from_account' => new AccountResource($this->whenLoaded('fromAccount')),
            'to_account' => new AccountResource($this->whenLoaded('toAccount')),
        ];
    }
}
