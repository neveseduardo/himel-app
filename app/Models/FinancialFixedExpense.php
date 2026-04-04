<?php

namespace App\Models;

use App\Models\Traits\HasUids;
use Database\Factories\FinancialFixedExpenseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialFixedExpense extends Model
{
    /** @use HasFactory<FinancialFixedExpenseFactory> */
    use HasFactory, HasUids;

    protected $primaryKey = 'uid';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'uid',
        'user_uid',
        'financial_category_uid',
        'name',
        'amount',
        'due_day',
        'active',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'due_day' => 'integer',
        'active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_uid', 'uid');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(FinancialCategory::class, 'financial_category_uid', 'uid');
    }

    public function scopeForUser($query, string $userUid)
    {
        return $query->where('user_uid', $userUid);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
