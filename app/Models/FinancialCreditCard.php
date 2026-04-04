<?php

namespace App\Models;

use App\Models\Traits\HasUids;
use Database\Factories\FinancialCreditCardFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinancialCreditCard extends Model
{
    /** @use HasFactory<FinancialCreditCardFactory> */
    use HasFactory, HasUids;

    protected $primaryKey = 'uid';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'uid',
        'user_uid',
        'name',
        'card_type',
        'due_day',
    ];

    protected $casts = [
        'due_day' => 'integer',
    ];

    public const CARD_TYPE_PHYSICAL = 'PHYSICAL';

    public const CARD_TYPE_VIRTUAL = 'VIRTUAL';

    public static function getCardTypes(): array
    {
        return [self::CARD_TYPE_PHYSICAL, self::CARD_TYPE_VIRTUAL];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_uid', 'uid');
    }

    public function charges(): HasMany
    {
        return $this->hasMany(FinancialCreditCardCharge::class, 'credit_card_uid', 'uid');
    }

    public function scopeForUser($query, string $userUid)
    {
        return $query->where('user_uid', $userUid);
    }
}
