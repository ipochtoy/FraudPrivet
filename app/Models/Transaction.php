<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Transaction extends Model
{
    protected $fillable = [
        'external_id',
        'transaction_date',
        'amount',
        'vendor',
        'vendor_raw',
        'memo',
        'category',
        'source',
        'card_last4',
        'match_status',
        'match_confidence',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount' => 'decimal:2',
        'match_confidence' => 'decimal:2',
    ];

    public function matches(): HasMany
    {
        return $this->hasMany(Match::class);
    }

    public function aiSuggestions(): HasMany
    {
        return $this->hasMany(AISuggestion::class);
    }

    public function scopePending($query)
    {
        return $query->where('match_status', 'pending');
    }

    public function scopeMatched($query)
    {
        return $query->where('match_status', 'matched');
    }

    public function scopeUnmatched($query)
    {
        return $query->where('match_status', 'unmatched');
    }

    public function scopeNeedsAttention($query)
    {
        return $query->whereIn('match_status', ['pending', 'unmatched', 'partial']);
    }
}
