<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Cast;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'template_id',
    'buyer_id',
    'seller_id',
    'price',
    'currency',
    'payment_gateway',
    'transaction_id',
    'status',
    'platform_fee',
    'seller_earnings',
    'refunded_at',
    'refund_reason',
])]
class TemplatePurchase extends Model
{
    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isRefunded(): bool
    {
        return $this->status === 'refunded';
    }

    public function getFormattedPrice(): string
    {
        return '$' . number_format($this->price, 2) . ' ' . $this->currency;
    }

    public function getFormattedPlatformFee(): string
    {
        return '$' . number_format($this->platform_fee, 2) . ' ' . $this->currency;
    }

    public function getFormattedSellerEarnings(): string
    {
        return '$' . number_format($this->seller_earnings, 2) . ' ' . $this->currency;
    }

    public function getPlatformFeePercentage(): float
    {
        return $this->price > 0 ? ($this->platform_fee / $this->price) * 100 : 0;
    }

    public function markAsCompleted(string $transactionId = null): void
    {
        $this->update([
            'status' => 'completed',
            'transaction_id' => $transactionId,
        ]);
    }

    public function markAsFailed(): void
    {
        $this->update([
            'status' => 'failed',
        ]);
    }

    public function refund(string $reason): void
    {
        $this->update([
            'status' => 'refunded',
            'refunded_at' => now(),
            'refund_reason' => $reason,
        ]);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeRefunded($query)
    {
        return $query->where('status', 'refunded');
    }

    public function scopeByBuyer($query, User $buyer)
    {
        return $query->where('buyer_id', $buyer->id);
    }

    public function scopeBySeller($query, User $seller)
    {
        return $query->where('seller_id', $seller->id);
    }

    public function scopeByTemplate($query, Template $template)
    {
        return $query->where('template_id', $template->id);
    }

    public function scopeByGateway($query, string $gateway)
    {
        return $query->where('payment_gateway', $gateway);
    }
}
