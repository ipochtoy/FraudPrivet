<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'order_id',
        'platform',
        'store',
        'order_date',
        'amount',
        'original_amount',
        'discount',
        'client_id',
        'client_email',
        'match_status',
    ];

    protected $casts = [
        'order_date' => 'date',
        'amount' => 'decimal:2',
        'original_amount' => 'decimal:2',
        'discount' => 'decimal:2',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

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

    public function scopeByPlatform($query, $platform)
    {
        return $query->where('platform', $platform);
    }

    public function hasDiscount(): bool
    {
        return $this->discount > 0;
    }
}
