<?php

namespace App\Models;

use App\Models\Traits\HasUids;
use Database\Factories\FinancialCategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinancialCategory extends Model
{
    /** @use HasFactory<FinancialCategoryFactory> */
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
        return $this->hasMany(FinancialTransaction::class, 'financial_category_uid', 'uid');
    }

    public function fixedExpenses(): HasMany
    {
        return $this->hasMany(FinancialFixedExpense::class, 'financial_category_uid', 'uid');
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
