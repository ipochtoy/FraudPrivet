<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AISuggestion extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'type',
        'transaction_id',
        'order_id',
        'suggestion',
        'explanation',
        'confidence',
        'status',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
        'feedback_applied',
    ];

    protected $casts = [
        'suggestion' => 'array',
        'confidence' => 'decimal:2',
        'reviewed_at' => 'datetime',
        'created_at' => 'datetime',
        'feedback_applied' => 'boolean',
    ];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeHighConfidence($query, $threshold = 0.8)
    {
        return $query->where('confidence', '>=', $threshold);
    }

    public function accept(string $reviewedBy, ?string $notes = null): void
    {
        $this->update([
            'status' => 'accepted',
            'reviewed_by' => $reviewedBy,
            'reviewed_at' => now(),
            'review_notes' => $notes,
        ]);
    }

    public function reject(string $reviewedBy, ?string $notes = null): void
    {
        $this->update([
            'status' => 'rejected',
            'reviewed_by' => $reviewedBy,
            'reviewed_at' => now(),
            'review_notes' => $notes,
        ]);
    }
}
