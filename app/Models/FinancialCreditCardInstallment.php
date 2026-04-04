<?php

namespace App\Models;

use App\Models\Traits\HasUids;
use Database\Factories\FinancialCreditCardInstallmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialCreditCardInstallment extends Model
{
    /** @use HasFactory<FinancialCreditCardInstallmentFactory> */
    use HasFactory, HasUids;

    protected $primaryKey = 'uid';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'uid',
        'credit_card_charge_uid',
        'financial_transaction_uid',
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
        return $this->belongsTo(FinancialCreditCardCharge::class, 'credit_card_charge_uid', 'uid');
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(FinancialTransaction::class, 'financial_transaction_uid', 'uid');
    }
}
