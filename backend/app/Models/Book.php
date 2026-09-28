<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Book extends Model
{
    protected $fillable = [
        'category_id', 'title', 'slug', 'author', 'description',
        'pages', 'language', 'published_at', 'isbn',
        'has_digital', 'has_physical', 'digital_price', 'physical_price',
        'stock', 'weight_grams', 'cover_path', 'pdf_path',
        'is_featured', 'is_published', 'tags', 'rating', 'reviews_count',
    ];

    protected $casts = [
        'published_at' => 'date',
        'has_digital' => 'boolean',
        'has_physical' => 'boolean',
        'is_featured' => 'boolean',
        'is_published' => 'boolean',
        'digital_price' => 'decimal:2',
        'physical_price' => 'decimal:2',
        'rating' => 'decimal:2',
        'tags' => 'array',
    ];

    protected $appends = ['cover_url', 'pdf_url'];

    protected static function booted(): void
    {
        static::creating(function (Book $b) {
            if (empty($b->slug)) $b->slug = Str::slug($b->title);
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getCoverUrlAttribute(): ?string
    {
        return $this->cover_path ? Storage::disk('public')->url($this->cover_path) : null;
    }

    public function getPdfUrlAttribute(): ?string
    {
        // Internal URL only — the actual download is gated by entitlement.
        return $this->pdf_path ? route('books.pdf.download', $this->slug) : null;
    }
}
