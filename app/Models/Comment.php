<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Cast;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable([
    'user_id',
    'project_id',
    'parent_id',
    'content',
    'mentions',
    'is_edited',
    'edited_at',
])]
#[Cast('mentions', 'array')]
class Comment extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    public function mentionedUsers(): HasMany
    {
        return $this->hasMany(Mention::class);
    }

    public function isReply(): bool
    {
        return !is_null($this->parent_id);
    }

    public function isEdited(): bool
    {
        return $this->is_edited;
    }

    public function getFormattedContent(): string
    {
        $content = $this->content;
        
        // Process mentions
        if ($this->mentions) {
            foreach ($this->mentions as $userId) {
                $user = User::find($userId);
                if ($user) {
                    $content = str_replace("@{$user->name}", "<span class='text-primary font-medium'>@{$user->name}</span>", $content);
                }
            }
        }

        // Convert URLs to links
        $content = preg_replace(
            '/(https?:\/\/[^\s]+)/',
            '<a href="$1" target="_blank" rel="noopener noreferrer" class="text-primary hover:underline">$1</a>',
            $content
        );

        // Convert line breaks to <br>
        $content = nl2br($content);

        return $content;
    }

    public function getMentionedUsers(): \Illuminate\Database\Eloquent\Collection
    {
        if (!$this->mentions) {
            return collect([]);
        }

        return User::whereIn('id', $this->mentions)->get();
    }

    public function extractMentions(): array
    {
        preg_match_all('/@(\w+)/', $this->content, $matches);
        
        $usernames = $matches[1] ?? [];
        $userIds = [];

        foreach ($usernames as $username) {
            $user = User::where('name', $username)->first();
            if ($user) {
                $userIds[] = $user->id;
            }
        }

        return $userIds;
    }

    public function processMentions(): void
    {
        $mentionedUserIds = $this->extractMentions();
        
        if (!empty($mentionedUserIds)) {
            $this->mentions = $mentionedUserIds;
            $this->save();

            // Create mention records for notifications
            foreach ($mentionedUserIds as $userId) {
                Mention::create([
                    'comment_id' => $this->id,
                    'user_id' => $userId,
                ]);
            }
        }
    }

    public function edit(string $content): void
    {
        $this->content = $content;
        $this->is_edited = true;
        $this->edited_at = now();
        $this->save();

        // Re-process mentions
        Mention::where('comment_id', $this->id)->delete();
        $this->processMentions();
    }

    public function canEdit(User $user): bool
    {
        return $this->user_id === $user->id;
    }

    public function canDelete(User $user): bool
    {
        return $this->user_id === $user->id || 
               ($this->project && $this->project->user_id === $user->id);
    }

    public function getRepliesCount(): int
    {
        return $this->replies()->count();
    }

    public function getLatestReplies(int $limit = 3): \Illuminate\Database\Eloquent\Collection
    {
        return $this->replies()
            ->with('user')
            ->latest()
            ->take($limit)
            ->get();
    }

    public function getAllReplies(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->replies()
            ->with('user', 'replies')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function scopeForProject($query, Project $project)
    {
        return $query->where('project_id', $project->id);
    }

    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeReplies($query)
    {
        return $query->whereNotNull('parent_id');
    }

    public function scopeRecent($query, int $hours = 24)
    {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }

    public function scopeByUser($query, User $user)
    {
        return $query->where('user_id', $user->id);
    }

    public function scopeWithReplies($query)
    {
        return $query->with(['replies' => function ($query) {
            $query->with('user')->orderBy('created_at', 'asc');
        }]);
    }

    public function scopeWithUser($query)
    {
        return $query->with('user:id,name,email');
    }

    public function getTruncatedContent(int $maxLength = 100): string
    {
        $content = strip_tags($this->getFormattedContent());
        
        if (strlen($content) <= $maxLength) {
            return $content;
        }

        return substr($content, 0, $maxLength) . '...';
    }

    public function getTimeAgo(): string
    {
        return $this->created_at->diffForHumans();
    }

    public function getEditedTimeAgo(): string
    {
        return $this->edited_at?->diffForHumans() ?? '';
    }

    public static function createComment(array $data): self
    {
        $comment = static::create($data);
        $comment->processMentions();
        
        return $comment;
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function ($comment) {
            // Update project's updated_at timestamp
            if ($comment->project) {
                $comment->project->touch();
            }
        });

        static::updated(function ($comment) {
            // Update project's updated_at timestamp
            if ($comment->project) {
                $comment->project->touch();
            }
        });
    }
}
