<?php

namespace App\Models;

use App\Models\Traits\HasUids;
use Database\Factories\FinancialAccountFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinancialAccount extends Model
{
    /** @use HasFactory<FinancialAccountFactory> */
    use HasFactory, HasUids;

    protected $primaryKey = 'uid';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'uid',
        'user_uid',
        'name',
        'type',
        'balance',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
    ];

    public const TYPE_CHECKING = 'CHECKING';

    public const TYPE_SAVINGS = 'SAVINGS';

    public const TYPE_CASH = 'CASH';

    public const TYPE_OTHER = 'OTHER';

    public static function getTypes(): array
    {
        return [
            self::TYPE_CHECKING,
            self::TYPE_SAVINGS,
            self::TYPE_CASH,
            self::TYPE_OTHER,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_uid', 'uid');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(FinancialTransaction::class, 'financial_account_uid', 'uid');
    }

    public function transfersFrom(): HasMany
    {
        return $this->hasMany(FinancialTransfer::class, 'from_account_uid', 'uid');
    }

    public function transfersTo(): HasMany
    {
        return $this->hasMany(FinancialTransfer::class, 'to_account_uid', 'uid');
    }

    public function scopeForUser($query, string $userUid)
    {
        return $query->where('user_uid', $userUid);
    }
}
