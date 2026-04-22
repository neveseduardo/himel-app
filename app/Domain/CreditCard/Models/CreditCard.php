<?php

namespace App\Domain\CreditCard\Models;

use App\Domain\CreditCardCharge\Models\CreditCardCharge;
use App\Domain\Shared\HasUids;
use App\Domain\User\Models\User;
use Database\Factories\CreditCardFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CreditCard extends Model
{
    protected $table = 'credit_cards';

    /** @use HasFactory<CreditCardFactory> */
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
        'closing_day',
        'last_four_digits',
    ];

    protected $casts = [
        'due_day' => 'integer',
        'closing_day' => 'integer',
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
        return $this->hasMany(CreditCardCharge::class, 'credit_card_uid', 'uid');
    }

    public function scopeForUser($query, string $userUid)
    {
        return $query->where('user_uid', $userUid);
    }
}
