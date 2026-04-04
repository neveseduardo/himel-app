<?php

namespace App\Models;

use App\Models\Traits\HasUids;
use Database\Factories\FinancialCreditCardChargeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinancialCreditCardCharge extends Model
{
    /** @use HasFactory<FinancialCreditCardChargeFactory> */
    use HasFactory, HasUids;

    protected $primaryKey = 'uid';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'uid',
        'credit_card_uid',
        'amount',
        'description',
        'total_installments',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'total_installments' => 'integer',
    ];

    public function creditCard(): BelongsTo
    {
        return $this->belongsTo(FinancialCreditCard::class, 'credit_card_uid', 'uid');
    }

    public function installments(): HasMany
    {
        return $this->hasMany(FinancialCreditCardInstallment::class, 'credit_card_charge_uid', 'uid');
    }
}
