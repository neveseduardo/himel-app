<?php

namespace App\Domain\FixedExpense\Models;

use App\Domain\Category\Models\Category;
use App\Domain\Shared\HasUids;
use App\Domain\User\Models\User;
use Database\Factories\FixedExpenseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FixedExpense extends Model
{
    protected $table = 'financial_fixed_expenses';

    /** @use HasFactory<FixedExpenseFactory> */
    use HasFactory, HasUids;

    protected $primaryKey = 'uid';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'uid',
        'user_uid',
        'category_uid',
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

    protected $appends = ['description'];

    public function getDescriptionAttribute(): string
    {
        return $this->name;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_uid', 'uid');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_uid', 'uid');
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
