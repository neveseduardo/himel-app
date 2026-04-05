<?php

namespace App\Domain\Category\Models;

use App\Domain\FixedExpense\Models\FixedExpense;
use App\Domain\Shared\HasUids;
use App\Domain\Transaction\Models\Transaction;
use App\Domain\User\Models\User;
use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $table = 'financial_categories';

    /** @use HasFactory<CategoryFactory> */
    use HasFactory, HasUids;

    protected $primaryKey = 'uid';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'uid',
        'user_uid',
        'name',
        'direction',
    ];

    public const DIRECTION_INFLOW = 'INFLOW';

    public const DIRECTION_OUTFLOW = 'OUTFLOW';

    public static function getDirections(): array
    {
        return [
            self::DIRECTION_INFLOW,
            self::DIRECTION_OUTFLOW,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_uid', 'uid');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'category_uid', 'uid');
    }

    public function fixedExpenses(): HasMany
    {
        return $this->hasMany(FixedExpense::class, 'category_uid', 'uid');
    }

    public function scopeForUser($query, string $userUid)
    {
        return $query->where('user_uid', $userUid);
    }

    public function scopeInflow($query)
    {
        return $query->where('direction', self::DIRECTION_INFLOW);
    }

    public function scopeOutflow($query)
    {
        return $query->where('direction', self::DIRECTION_OUTFLOW);
    }
}
