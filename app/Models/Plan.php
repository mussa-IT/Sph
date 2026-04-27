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
    'monthly_price',
    'yearly_price',
    'features',
    'limits',
    'sort_order',
    'is_active',
    'is_popular',
    'stripe_price_id_monthly',
    'stripe_price_id_yearly',
    'paypal_plan_id',
    'trial_days',
])]
#[Cast('features', 'array')]
#[Cast('limits', 'array')]
class Plan extends Model
{
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function getPriceForBillingCycle(string $cycle): float
    {
        return $cycle === 'yearly' ? $this->yearly_price : $this->monthly_price;
    }

    public function getStripePriceIdForBillingCycle(string $cycle): ?string
    {
        return $cycle === 'yearly' ? $this->stripe_price_id_yearly : $this->stripe_price_id_monthly;
    }

    public function getYearlyDiscount(): float
    {
        if ($this->monthly_price === 0) return 0;
        
        $yearlyTotal = $this->monthly_price * 12;
        $discount = ($yearlyTotal - $this->yearly_price) / $yearlyTotal * 100;
        
        return round($discount, 1);
    }

    public function getFormattedMonthlyPrice(): string
    {
        return '$' . number_format($this->monthly_price, 2);
    }

    public function getFormattedYearlyPrice(): string
    {
        return '$' . number_format($this->yearly_price, 2);
    }

    public function hasFeature(string $feature): bool
    {
        return in_array($feature, $this->features);
    }

    public function getLimit(string $limit): mixed
    {
        return $this->limits[$limit] ?? null;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function scopePopular($query)
    {
        return $query->where('is_popular', true);
    }

    public static function findBySlug(string $slug): ?self
    {
        return static::where('slug', $slug)->first();
    }

    public static function getActivePlans(): \Illuminate\Database\Eloquent\Collection
    {
        return static::active()->ordered()->get();
    }
}
