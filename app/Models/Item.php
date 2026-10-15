<?php

namespace App\Models;

use App\Enums\ItemStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Item extends Model
{
    use HasFactory;

    protected $with = ['images'];

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'price_per_day',
        'deposit',
        'stock',
        'specs',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'price_per_day' => 'integer',
            'deposit' => 'integer',
            'stock' => 'integer',
            'specs' => 'array',
            'status' => ItemStatus::class,
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ItemImage::class)->orderBy('sort_order')->orderBy('id');
    }

    public function blackouts(): HasMany
    {
        return $this->hasMany(Blackout::class);
    }

    public function bookingItems(): HasMany
    {
        return $this->hasMany(BookingItem::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', ItemStatus::ACTIVE);
    }

    public function getPrimaryImageUrlAttribute(): string
    {
        if ($this->relationLoaded('images')) {
            $primary = $this->images->firstWhere('is_primary', true) ?? $this->images->first();
        } else {
            $primary = $this->images()->where('is_primary', true)->first() ?? $this->images()->first();
        }

        if ($primary && $primary->path) {
            if (str_starts_with($primary->path, 'http')) {
                return $primary->path;
            }
            return Storage::url($primary->path);
        }

        // Clean SVG placeholder for items without image
        return 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=800&auto=format&fit=crop&q=80';
    }
}
