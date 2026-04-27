<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Cast;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable([
    'title',
    'slug',
    'description',
    'content',
    'metadata',
    'thumbnail_url',
    'preview_url',
    'price',
    'currency',
    'status',
    'seller_id',
    'category_id',
    'tags',
    'downloads',
    'purchases',
    'rating',
    'reviews_count',
    'is_featured',
    'featured_at',
    'approved_at',
    'rejection_reason',
])]
#[Cast('metadata', 'array')]
#[Cast('tags', 'array')]
class Template extends Model
{
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TemplateCategory::class, 'category_id');
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(TemplatePurchase::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(TemplateReview::class);
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isArchived(): bool
    {
        return $this->status === 'archived';
    }

    public function isFree(): bool
    {
        return $this->price == 0;
    }

    public function isPaid(): bool
    {
        return $this->price > 0;
    }

    public function isFeatured(): bool
    {
        return $this->is_featured;
    }

    public function getFormattedPrice(): string
    {
        if ($this->isFree()) {
            return 'Free';
        }

        return '$' . number_format($this->price, 2) . ' ' . $this->currency;
    }

    public function getAverageRating(): float
    {
        return (float) $this->rating;
    }

    public function getRatingStars(): string
    {
        $rating = $this->getAverageRating();
        $stars = '';

        for ($i = 1; $i <= 5; $i++) {
            if ($i <= $rating) {
                $stars .= '⭐';
            } elseif ($i - 0.5 <= $rating) {
                $stars .= '⭐';
            } else {
                $stars .= '☆';
            }
        }

        return $stars;
    }

    public function getTagList(): array
    {
        return $this->tags ?? [];
    }

    public function hasTag(string $tag): bool
    {
        return in_array($tag, $this->getTagList());
    }

    public function incrementDownloads(): void
    {
        $this->increment('downloads');
    }

    public function incrementPurchases(): void
    {
        $this->increment('purchases');
    }

    public function updateRating(): void
    {
        $avgRating = $this->reviews()->avg('rating') ?? 0;
        $reviewsCount = $this->reviews()->count();

        $this->update([
            'rating' => $avgRating,
            'reviews_count' => $reviewsCount,
        ]);
    }

    public function approve(): void
    {
        $this->update([
            'status' => 'approved',
            'approved_at' => now(),
            'rejection_reason' => null,
        ]);
    }

    public function reject(string $reason): void
    {
        $this->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
        ]);
    }

    public function archive(): void
    {
        $this->update([
            'status' => 'archived',
            'is_featured' => false,
        ]);
    }

    public function feature(): void
    {
        $this->update([
            'is_featured' => true,
            'featured_at' => now(),
        ]);
    }

    public function unfeature(): void
    {
        $this->update([
            'is_featured' => false,
            'featured_at' => null,
        ]);
    }

    public function canBePurchasedBy(User $user): bool
    {
        // Can't purchase own template
        if ($this->seller_id === $user->id) {
            return false;
        }

        // Can't purchase if not approved
        if (!$this->isApproved()) {
            return false;
        }

        // Check if already purchased
        return !$this->purchases()->where('buyer_id', $user->id)->exists();
    }

    public function getPurchaseUrl(): string
    {
        return route('templates.purchase', $this->slug);
    }

    public function getPreviewUrl(): string
    {
        return $this->preview_url ?: route('templates.preview', $this->slug);
    }

    public function getThumbnailUrl(): string
    {
        return $this->thumbnail_url ?: asset('images/template-placeholder.jpg');
    }

    public function getMetadataValue(string $key, $default = null)
    {
        $metadata = $this->metadata ?? [];
        return $metadata[$key] ?? $default;
    }

    public function setMetadataValue(string $key, $value): void
    {
        $metadata = $this->metadata ?? [];
        $metadata[$key] = $value;
        $this->metadata = $metadata;
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeFree($query)
    {
        return $query->where('price', 0);
    }

    public function scopePaid($query)
    {
        return $query->where('price', '>', 0);
    }

    public function scopeByCategory($query, string $categorySlug)
    {
        return $query->whereHas('category', function ($query) use ($categorySlug) {
            $query->where('slug', $categorySlug);
        });
    }

    public function scopeBySeller($query, User $seller)
    {
        return $query->where('seller_id', $seller->id);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($query) use ($search) {
            $query->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('tags', 'like', "%{$search}%");
        });
    }

    public function scopeSortBy($query, string $sortBy)
    {
        return match($sortBy) {
            'newest' => $query->latest(),
            'oldest' => $query->oldest(),
            'price_low' => $query->orderBy('price', 'asc'),
            'price_high' => $query->orderBy('price', 'desc'),
            'rating' => $query->orderBy('rating', 'desc'),
            'downloads' => $query->orderBy('downloads', 'desc'),
            'purchases' => $query->orderBy('purchases', 'desc'),
            default => $query->latest(),
        };
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($template) {
            if (empty($template->slug)) {
                $template->slug = Str::slug($template->title) . '-' . time();
            }
        });

        static::updating(function ($template) {
            if ($template->isDirty('title') && empty($template->slug)) {
                $template->slug = Str::slug($template->title) . '-' . time();
            }
        });
    }
}
