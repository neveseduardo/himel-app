<?php

namespace App\Domain\CreditCardInstallment\Resources;

use App\Domain\Transaction\Resources\TransactionResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CreditCardInstallmentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uid' => $this->uid,
            'installment_number' => $this->installment_number,
            'amount' => $this->amount,
            'due_date' => $this->due_date,
            'paid_at' => $this->paid_at,
            'transaction' => new TransactionResource($this->whenLoaded('transaction')),
        ];
    }
}
