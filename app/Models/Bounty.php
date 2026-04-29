<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bounty extends Model
{
    use HasFactory;

    protected $fillable = [
        'creator_id',
        'winner_id',
        'title',
        'description',
        'reward',
        'status',
        'deadline',
    ];

    protected $casts = [
        'reward' => 'decimal:2',
        'deadline' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'winner_id');
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeAssigned($query)
    {
        return $query->where('status', 'assigned');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
