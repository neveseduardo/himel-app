<?php

namespace App\Domain\Account\Models;

use App\Domain\Shared\HasUids;
use App\Domain\Transaction\Models\Transaction;
use App\Domain\Transfer\Models\Transfer;
use App\Domain\User\Models\User;
use Database\Factories\AccountFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    protected $table = 'accounts';

    /** @use HasFactory<AccountFactory> */
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
        return $this->hasMany(Transaction::class, 'account_uid', 'uid');
    }

    public function transfersFrom(): HasMany
    {
        return $this->hasMany(Transfer::class, 'from_account_uid', 'uid');
    }

    public function transfersTo(): HasMany
    {
        return $this->hasMany(Transfer::class, 'to_account_uid', 'uid');
    }

    public function scopeForUser($query, string $userUid)
    {
        return $query->where('user_uid', $userUid);
    }
}
