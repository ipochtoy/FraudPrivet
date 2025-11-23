<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Match extends Model
{
    protected $table = 'matches';

    protected $fillable = [
        'match_type',
        'confidence',
        'order_id',
        'transaction_id',
        'transaction_ids',
        'amount_difference',
        'discount_amount',
        'discount_type',
        'ai_suggested',
        'ai_explanation',
        'verified',
        'verified_by',
        'verified_at',
        'notes',
    ];

    protected $casts = [
        'confidence' => 'decimal:2',
        'amount_difference' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'ai_suggested' => 'boolean',
        'verified' => 'boolean',
        'verified_at' => 'datetime',
        'transaction_ids' => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function scopeVerified($query)
    {
        return $query->where('verified', true);
    }

    public function scopeUnverified($query)
    {
        return $query->where('verified', false);
    }

    public function scopeAiSuggested($query)
    {
        return $query->where('ai_suggested', true);
    }

    public function isSplitMatch(): bool
    {
        return $this->match_type === 'split' && !empty($this->transaction_ids);
    }
}
