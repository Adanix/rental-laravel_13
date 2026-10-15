<?php

namespace App\Models;

use App\Enums\BookingStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'user_id',
        'start_at',
        'end_at',
        'picked_up_at',
        'returned_at',
        'status',
        'subtotal_rental',
        'deposit_total',
        'discount_amount',
        'late_fee',
        'damage_fee',
        'grand_total',
        'handover_checklist',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'picked_up_at' => 'datetime',
            'returned_at' => 'datetime',
            'expires_at' => 'datetime',
            'status' => BookingStatus::class,
            'subtotal_rental' => 'integer',
            'deposit_total' => 'integer',
            'discount_amount' => 'integer',
            'late_fee' => 'integer',
            'damage_fee' => 'integer',
            'grand_total' => 'integer',
            'handover_checklist' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(BookingItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(BookingStatusLog::class);
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->status === BookingStatus::PICKED_UP && now()->gt($this->end_at);
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('status', BookingStatus::PICKED_UP->value)
            ->where('end_at', '<', now());
    }

    public function getDurationDaysAttribute(): int
    {
        if (! $this->start_at || ! $this->end_at) {
            return 1;
        }

        $hours = $this->start_at->diffInHours($this->end_at);
        return (int) max(1, ceil($hours / 24));
    }
}
