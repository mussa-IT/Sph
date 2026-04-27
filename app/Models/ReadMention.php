<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'mention_id',
    'user_id',
    'read_at',
])]
class ReadMention extends Model
{
    public function mention(): BelongsTo
    {
        return $this->belongsTo(Mention::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($readMention) {
            if (empty($readMention->read_at)) {
                $readMention->read_at = now();
            }
        });
    }
}
