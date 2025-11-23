<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MatchingRule extends Model
{
    protected $fillable = [
        'name',
        'description',
        'vendor_pattern',
        'platform',
        'amount_min',
        'amount_max',
        'amount_tolerance',
        'date_tolerance_days',
        'discount_tolerance',
        'allow_splits',
        'max_split_parts',
        'priority',
        'is_active',
        'created_by',
        'ai_confidence',
        'times_used',
        'success_rate',
    ];

    protected $casts = [
        'amount_min' => 'decimal:2',
        'amount_max' => 'decimal:2',
        'amount_tolerance' => 'decimal:2',
        'discount_tolerance' => 'decimal:2',
        'ai_confidence' => 'decimal:2',
        'success_rate' => 'decimal:2',
        'allow_splits' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByPriority($query)
    {
        return $query->orderBy('priority', 'desc');
    }

    public function scopeAiCreated($query)
    {
        return $query->where('created_by', 'ai_suggested');
    }

    public function incrementUsage(): void
    {
        $this->increment('times_used');
    }

    public function matchesVendor(string $vendor): bool
    {
        if (empty($this->vendor_pattern)) {
            return true;
        }

        return (bool) preg_match('/' . $this->vendor_pattern . '/i', $vendor);
    }

    public function matchesAmount(float $amount): bool
    {
        if ($this->amount_min !== null && $amount < $this->amount_min) {
            return false;
        }

        if ($this->amount_max !== null && $amount > $this->amount_max) {
            return false;
        }

        return true;
    }
}
