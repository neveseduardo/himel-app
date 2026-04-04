<?php

namespace App\Models;

use App\Models\Traits\HasUids;
use Database\Factories\FinancialPeriodFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialPeriod extends Model
{
    /** @use HasFactory<FinancialPeriodFactory> */
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

    public function scopeForUser($query, string $userUid)
    {
        return $query->where('user_uid', $userUid);
    }

    public function scopeForMonthYear($query, int $month, int $year)
    {
        return $query->where('month', $month)->where('year', $year);
    }
}
