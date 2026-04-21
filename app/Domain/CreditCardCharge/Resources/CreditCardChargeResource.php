<?php

namespace App\Domain\CreditCardCharge\Resources;

use App\Domain\CreditCard\Resources\CreditCardResource;
use App\Domain\CreditCardInstallment\Resources\CreditCardInstallmentResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CreditCardChargeResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uid' => $this->uid,
            'description' => $this->description,
            'amount' => $this->amount,
            'total_installments' => $this->total_installments,
            'purchase_date' => $this->purchase_date?->format('Y-m-d'),
            'credit_card' => new CreditCardResource($this->whenLoaded('creditCard')),
            'installments' => CreditCardInstallmentResource::collection($this->whenLoaded('installments')),
        ];
    }
}
