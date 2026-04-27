<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Cast;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'subscription_id',
    'type',
    'status',
    'payment_gateway',
    'gateway_transaction_id',
    'amount',
    'currency',
    'description',
    'gateway_data',
    'processed_at',
    'refunded_at',
    'refund_reason',
    'invoice_number',
    'receipt_url',
])]
#[Cast('gateway_data', 'array')]
class BillingHistory extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function isPayment(): bool
    {
        return $this->type === 'payment';
    }

    public function isRefund(): bool
    {
        return $this->type === 'refund';
    }

    public function isCredit(): bool
    {
        return $this->type === 'credit';
    }

    public function isAdjustment(): bool
    {
        return $this->type === 'adjustment';
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

    public function getFormattedAmount(): string
    {
        $prefix = $this->isRefund() ? '-' : '+';
        return $prefix . '$' . number_format($this->amount, 2);
    }

    public function getFormattedDate(): string
    {
        return $this->processed_at?->format('M j, Y') ?? $this->created_at->format('M j, Y');
    }

    public function getTypeLabel(): string
    {
        return match($this->type) {
            'payment' => 'Payment',
            'refund' => 'Refund',
            'credit' => 'Credit',
            'adjustment' => 'Adjustment',
            default => ucfirst($this->type),
        };
    }

    public function getStatusLabel(): string
    {
        return match($this->status) {
            'pending' => 'Pending',
            'completed' => 'Completed',
            'failed' => 'Failed',
            'refunded' => 'Refunded',
            default => ucfirst($this->status),
        };
    }

    public function getStatusColor(): string
    {
        return match($this->status) {
            'pending' => 'warning',
            'completed' => 'success',
            'failed' => 'danger',
            'refunded' => 'info',
            default => 'muted',
        };
    }

    public function scopeForUser($query, User $user)
    {
        return $query->where('user_id', $user->id);
    }

    public function scopePayments($query)
    {
        return $query->where('type', 'payment');
    }

    public function scopeRefunds($query)
    {
        return $query->where('type', 'refund');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public static function createPaymentRecord(
        User $user,
        ?Subscription $subscription,
        float $amount,
        string $description,
        string $gateway,
        array $gatewayData = []
    ): self {
        return static::create([
            'user_id' => $user->id,
            'subscription_id' => $subscription?->id,
            'type' => 'payment',
            'status' => 'completed',
            'payment_gateway' => $gateway,
            'gateway_transaction_id' => $gatewayData['transaction_id'] ?? null,
            'amount' => $amount,
            'currency' => 'USD',
            'description' => $description,
            'gateway_data' => $gatewayData,
            'processed_at' => now(),
            'invoice_number' => self::generateInvoiceNumber(),
        ]);
    }

    public static function generateInvoiceNumber(): string
    {
        $prefix = 'INV';
        $year = now()->format('Y');
        $month = now()->format('m');
        $sequence = static::whereYear('created_at', now()->year)
                         ->whereMonth('created_at', now()->month)
                         ->count() + 1;
        
        return sprintf('%s-%s%s-%04d', $prefix, $year, $month, $sequence);
    }
}
