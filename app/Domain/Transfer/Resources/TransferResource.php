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
            'occurred_at' => $this->created_at,
            'created_at' => $this->created_at,
            'from_account' => $this->whenLoaded('fromAccount', fn () => (new AccountResource($this->fromAccount))->resolve()),
            'to_account' => $this->whenLoaded('toAccount', fn () => (new AccountResource($this->toAccount))->resolve()),
        ];
    }
}
