<?php

namespace App\Domain\Period\Models;

use App\Domain\Shared\HasUids;
use App\Domain\Transaction\Models\Transaction;
use App\Domain\User\Models\User;
use Database\Factories\PeriodFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Period extends Model
{
    protected $table = 'financial_periods';

    /** @use HasFactory<PeriodFactory> */
    use HasFactory, HasUids;

    protected $primaryKey = 'uid';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'uid',
        'user_uid',
        'month',
        'year',
    ];

    protected $casts = [
        'month' => 'integer',
        'year' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_uid', 'uid');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'period_uid', 'uid');
    }

    public function scopeForUser($query, string $userUid)
    {
        return $query->where('user_uid', $userUid);
    }

    public function scopeForMonthYear($query, int $month, int $year)
    {
        return $query->where('month', $month)->where('year', $year);
    }
}
