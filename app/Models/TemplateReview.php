<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'template_id',
    'user_id',
    'rating',
    'comment',
    'is_verified',
    'is_helpful',
    'helpful_count',
])]
class TemplateReview extends Model
{
    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isVerified(): bool
    {
        return $this->is_verified;
    }

    public function markAsVerified(): void
    {
        $this->update(['is_verified' => true]);
    }

    public function isHelpful(): bool
    {
        return $this->is_helpful;
    }

    public function markAsHelpful(): void
    {
        $this->update([
            'is_helpful' => true,
            'helpful_count' => $this->helpful_count + 1,
        ]);
    }

    public function incrementHelpfulCount(): void
    {
        $this->increment('helpful_count');
    }

    public function getRatingStars(): string
    {
        $stars = '';
        for ($i = 1; $i <= 5; $i++) {
            if ($i <= $this->rating) {
                $stars .= '⭐';
            } else {
                $stars .= '☆';
            }
        }
        return $stars;
    }

    public function getRatingPercentage(): int
    {
        return ($this->rating / 5) * 100;
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeHelpful($query)
    {
        return $query->where('is_helpful', true);
    }

    public function scopeByRating($query, int $rating)
    {
        return $query->where('rating', $rating);
    }

    public function scopeByUser($query, User $user)
    {
        return $query->where('user_id', $user->id);
    }

    public function scopeByTemplate($query, Template $template)
    {
        return $query->where('template_id', $template->id);
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function ($review) {
            $review->template->updateRating();
        });

        static::updated(function ($review) {
            if ($review->isDirty('rating')) {
                $review->template->updateRating();
            }
        });

        static::deleted(function ($review) {
            $review->template->updateRating();
        });
    }
}
