<?php

namespace App\Domain\Account\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccountResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uid' => $this->uid,
            'name' => $this->name,
            'type' => $this->type,
            'balance' => $this->balance,
            'created_at' => $this->created_at,
        ];
    }
}
