<?php

namespace App\Models;

use App\Models\Traits\HasUids;
use Database\Factories\FinancialTransactionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialTransaction extends Model
{
    /** @use HasFactory<FinancialTransactionFactory> */
    use HasFactory, HasUids;

    protected $primaryKey = 'uid';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'uid',
        'user_uid',
        'financial_account_uid',
        'financial_category_uid',
        'amount',
        'direction',
        'status',
        'source',
        'occurred_at',
        'due_date',
        'paid_at',
        'reference_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'occurred_at' => 'datetime',
        'due_date' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public const DIRECTION_INFLOW = 'INFLOW';

    public const DIRECTION_OUTFLOW = 'OUTFLOW';

    public const STATUS_PENDING = 'PENDING';

    public const STATUS_PAID = 'PAID';

    public const STATUS_OVERDUE = 'OVERDUE';

    public const SOURCE_MANUAL = 'MANUAL';

    public const SOURCE_CREDIT_CARD = 'CREDIT_CARD';

    public const SOURCE_FIXED = 'FIXED';

    public const SOURCE_TRANSFER = 'TRANSFER';

    public static function getDirections(): array
    {
        return [self::DIRECTION_INFLOW, self::DIRECTION_OUTFLOW];
    }

    public static function getStatuses(): array
    {
        return [self::STATUS_PENDING, self::STATUS_PAID, self::STATUS_OVERDUE];
    }

    public static function getSources(): array
    {
        return [self::SOURCE_MANUAL, self::SOURCE_CREDIT_CARD, self::SOURCE_FIXED, self::SOURCE_TRANSFER];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_uid', 'uid');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(FinancialAccount::class, 'financial_account_uid', 'uid');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(FinancialCategory::class, 'financial_category_uid', 'uid');
    }

    public function scopeForUser($query, string $userUid)
    {
        return $query->where('user_uid', $userUid);
    }

    public function scopePaid($query)
    {
        return $query->where('status', self::STATUS_PAID);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', self::STATUS_OVERDUE);
    }
}
