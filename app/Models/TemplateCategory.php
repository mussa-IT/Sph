<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Cast;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'slug',
    'description',
    'icon',
    'color',
    'is_active',
    'sort_order',
])]
class TemplateCategory extends Model
{
    public function templates(): HasMany
    {
        return $this->hasMany(Template::class);
    }

    public function activeTemplates(): HasMany
    {
        return $this->templates()->approved();
    }

    public function getTemplateCount(): int
    {
        return $this->activeTemplates()->count();
    }

    public function getFeaturedTemplateCount(): int
    {
        return $this->activeTemplates()->featured()->count();
    }

    public function getAveragePrice(): float
    {
        return $this->activeTemplates()->avg('price') ?? 0;
    }

    public function getFormattedAveragePrice(): string
    {
        $avgPrice = $this->getAveragePrice();
        return $avgPrice > 0 ? '$' . number_format($avgPrice, 2) : 'Free';
    }

    public function getIconUrl(): string
    {
        return $this->icon ?: asset('images/category-icon.png');
    }

    public function getColor(): string
    {
        return $this->color ?: '#6B7280';
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = \Str::slug($category->name);
            }
        });

        static::updating(function ($category) {
            if ($category->isDirty('name') && empty($category->slug)) {
                $category->slug = \Str::slug($category->name);
            }
        });
    }
}
