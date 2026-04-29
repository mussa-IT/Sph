<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Blueprint extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'project_id',
        'title',
        'description',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'file_hash',
        'blockchain_hash',
        'transaction_hash',
        'blockchain_anchored_at',
        'is_verified',
    ];

    protected $casts = [
        'blockchain_anchored_at' => 'datetime',
        'is_verified' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function scopeAnchored($query)
    {
        return $query->whereNotNull('blockchain_hash');
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function getIsAnchoredAttribute(): bool
    {
        return !is_null($this->blockchain_hash);
    }
}
