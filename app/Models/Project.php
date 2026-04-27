<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Cast;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'title',
    'category',
    'description',
    'status',
    'progress',
    'estimated_budget',
    'deadline',
    'chat_session_id',
    'project_type',
    'difficulty',
    'estimated_timeline_weeks',
    'confidence_score',
    'feasibility_score',
    'budget_data',
    'tools_data',
    'step_by_step_plan',
    'success_factors',
    'next_actions',
    'project_hash',
    'transaction_hash',
    'wallet_address',
    'blockchain_verified_at',
])]
#[Cast('progress', 'int')]
#[Cast('estimated_budget', 'decimal:2')]
#[Cast('deadline', 'datetime')]
#[Cast('estimated_timeline_weeks', 'int')]
#[Cast('confidence_score', 'int')]
#[Cast('feasibility_score', 'int')]
#[Cast('budget_data', 'json')]
#[Cast('tools_data', 'json')]
#[Cast('step_by_step_plan', 'json')]
#[Cast('success_factors', 'json')]
#[Cast('next_actions', 'json')]
#[Cast('blockchain_verified_at', 'datetime')]
class Project extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function budgets(): HasMany
    {
        return $this->hasMany(Budget::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function topLevelComments(): HasMany
    {
        return $this->comments()->whereNull('parent_id');
    }

    public function chatSession(): BelongsTo
    {
        return $this->belongsTo(ChatSession::class);
    }

    public function userCanView(User $user): bool
    {
        // Owner can always view
        if ($this->user_id === $user->id) {
            return true;
        }

        // Team members can view team projects
        if ($this->team_id) {
            return $this->team->isMember($user);
        }

        // Public projects can be viewed by anyone
        if ($this->visibility === 'public') {
            return true;
        }

        return false;
    }

    public function userCanComment(User $user): bool
    {
        if (!$this->userCanView($user)) {
            return false;
        }

        // Owner can always comment
        if ($this->user_id === $user->id) {
            return true;
        }

        // Team members can comment on team projects
        if ($this->team_id) {
            return $this->team->isMember($user);
        }

        // Public projects allow comments from authenticated users
        if ($this->visibility === 'public') {
            return true;
        }

        return false;
    }

    public function userCanEdit(User $user): bool
    {
        // Only owner can edit
        return $this->user_id === $user->id;
    }

    public function userCanDelete(User $user): bool
    {
        // Only owner can delete
        return $this->user_id === $user->id;
    }
}
