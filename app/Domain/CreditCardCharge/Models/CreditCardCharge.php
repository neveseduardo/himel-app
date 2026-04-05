<?php

namespace App\Domain\CreditCardCharge\Models;

use App\Domain\CreditCard\Models\CreditCard;
use App\Domain\CreditCardInstallment\Models\CreditCardInstallment;
use App\Domain\Shared\HasUids;
use Database\Factories\CreditCardChargeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CreditCardCharge extends Model
{
    protected $table = 'financial_credit_card_charges';

    /** @use HasFactory<CreditCardChargeFactory> */
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
        return $this->belongsTo(CreditCard::class, 'credit_card_uid', 'uid');
    }

    public function installments(): HasMany
    {
        return $this->hasMany(CreditCardInstallment::class, 'credit_card_charge_uid', 'uid');
    }
}
