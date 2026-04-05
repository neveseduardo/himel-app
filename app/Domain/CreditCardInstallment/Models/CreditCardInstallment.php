<?php

namespace App\Domain\CreditCardInstallment\Models;

use App\Domain\CreditCardCharge\Models\CreditCardCharge;
use App\Domain\Shared\HasUids;
use App\Domain\Transaction\Models\Transaction;
use Database\Factories\CreditCardInstallmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditCardInstallment extends Model
{
    protected $table = 'financial_credit_card_installments';

    /** @use HasFactory<CreditCardInstallmentFactory> */
    use HasFactory, HasUids;

    protected $primaryKey = 'uid';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'uid',
        'credit_card_charge_uid',
        'transaction_uid',
        'installment_number',
        'due_date',
        'amount',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'installment_number' => 'integer',
        'due_date' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function charge(): BelongsTo
    {
        return $this->belongsTo(CreditCardCharge::class, 'credit_card_charge_uid', 'uid');
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'transaction_uid', 'uid');
    }
}
