<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['chat_session_id', 'sender', 'message', 'flagged'])]
class ChatMessage extends Model
{
    use HasFactory;

    protected $casts = [
        'flagged' => 'boolean',
    ];

    public function chatSession(): BelongsTo
    {
        return $this->belongsTo(ChatSession::class);
    }
}
