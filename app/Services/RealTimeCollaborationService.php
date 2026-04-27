<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\ChatMessage;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Cache;

class RealTimeCollaborationService
{
    public function initializeCollaborationSession(Project $project, User $user): array
    {
        $sessionId = $this->generateSessionId($project, $user);
        
        $session = [
            'session_id' => $sessionId,
            'project_id' => $project->id,
            'user_id' => $user->id,
            'active_users' => $this->getActiveUsers($project),
            'presence_data' => $this->initializePresence($user, $project),
            'collaboration_features' => $this->getAvailableFeatures($project),
            'permissions' => $this->getUserPermissions($user, $project),
        ];
        
        // Store session data
        Cache::put("collaboration_session_{$sessionId}", $session, 3600);
        
        return $session;
    }

    public function broadcastUserPresence(Project $project, User $user, array $presenceData): void
    {
        $presence = [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_avatar' => $user->avatar_url,
            'status' => $presenceData['status'] ?? 'online',
            'current_task' => $presenceData['current_task'] ?? null,
            'mouse_position' => $presenceData['mouse_position'] ?? null,
            'selection' => $presenceData['selection'] ?? null,
            'last_activity' => now()->toISOString(),
        ];
        
        // Update presence cache
        Cache::put("presence_{$project->id}_{$user->id}", $presence, 300);
        
        // Broadcast to other users
        $this->broadcastToProject($project, 'user.presence', $presence, $user->id);
    }

    public function broadcastTaskUpdate(Project $project, Task $task, User $user, array $changes): void
    {
        $update = [
            'task_id' => $task->id,
            'task_title' => $task->title,
            'changes' => $changes,
            'updated_by' => [
                'id' => $user->id,
                'name' => $user->name,
            ],
            'timestamp' => now()->toISOString(),
            'version' => $this->getTaskVersion($task),
        ];
        
        // Update task version
        Cache::increment("task_version_{$task->id}");
        
        // Broadcast update
        $this->broadcastToProject($project, 'task.updated', $update);
        
        // Log activity
        $this->logCollaborationActivity($project, $user, 'task_updated', [
            'task_id' => $task->id,
            'changes' => $changes,
        ]);
    }

    public function broadcastComment(Project $project, array $commentData, User $user): void
    {
        $comment = [
            'id' => uniqid(),
            'content' => $commentData['content'],
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'avatar' => $user->avatar_url,
            ],
            'mentions' => $commentData['mentions'] ?? [],
            'timestamp' => now()->toISOString(),
            'reactions' => [],
        ];
        
        // Broadcast comment
        $this->broadcastToProject($project, 'comment.added', $comment);
        
        // Send notifications to mentioned users
        $this->notifyMentionedUsers($project, $comment, $user);
        
        // Log activity
        $this->logCollaborationActivity($project, $user, 'comment_added', [
            'comment_id' => $comment['id'],
            'mentions' => $comment['mentions'],
        ]);
    }

    public function broadcastCursorTracking(Project $project, User $user, array $cursorData): void
    {
        $cursor = [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'position' => $cursorData['position'],
            'selection' => $cursorData['selection'] ?? null,
            'timestamp' => now()->toISOString(),
        ];
        
        // Update cursor cache (short TTL)
        Cache::put("cursor_{$project->id}_{$user->id}", $cursor, 30);
        
        // Broadcast to other users (high frequency, so use efficient channel)
        $this->broadcastToProject($project, 'cursor.moved', $cursor, $user->id);
    }

    public function broadcastTypingIndicator(Project $project, User $user, string $context, bool $isTyping): void
    {
        $indicator = [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'context' => $context, // 'chat', 'task_description', etc.
            'is_typing' => $isTyping,
            'timestamp' => now()->toISOString(),
        ];
        
        // Update typing cache (very short TTL)
        $cacheKey = "typing_{$project->id}_{$context}_{$user->id}";
        
        if ($isTyping) {
            Cache::put($cacheKey, $indicator, 5);
        } else {
            Cache::forget($cacheKey);
        }
        
        // Broadcast typing status
        $this->broadcastToProject($project, 'typing.indicator', $indicator, $user->id);
    }

    public function handleDocumentEdit(Project $project, User $user, array $editData): array
    {
        $operation = $editData['operation']; // 'insert', 'delete', 'replace'
        $position = $editData['position'];
        $content = $editData['content'] ?? '';
        $length = $editData['length'] ?? 0;
        
        // Generate unique operation ID
        $operationId = uniqid('op_');
        
        $edit = [
            'operation_id' => $operationId,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'operation' => $operation,
            'position' => $position,
            'content' => $content,
            'length' => $length,
            'timestamp' => now()->toISOString(),
        ];
        
        // Apply operational transformation
        $transformedEdit = $this->applyOperationalTransformation($project, $edit);
        
        // Broadcast transformed edit
        $this->broadcastToProject($project, 'document.edited', $transformedEdit, $user->id);
        
        // Store operation for conflict resolution
        Cache::put("operation_{$operationId}", $transformedEdit, 300);
        
        return $transformedEdit;
    }

    public function broadcastFileShare(Project $project, User $user, array $fileData): void
    {
        $fileShare = [
            'file_id' => $fileData['file_id'],
            'file_name' => $fileData['file_name'],
            'file_size' => $fileData['file_size'],
            'file_type' => $fileData['file_type'],
            'file_url' => $fileData['file_url'],
            'shared_by' => [
                'id' => $user->id,
                'name' => $user->name,
            ],
            'timestamp' => now()->toISOString(),
            'permissions' => $fileData['permissions'] ?? 'view',
        ];
        
        // Broadcast file share
        $this->broadcastToProject($project, 'file.shared', $fileShare);
        
        // Log activity
        $this->logCollaborationActivity($project, $user, 'file_shared', [
            'file_id' => $fileData['file_id'],
            'file_name' => $fileData['file_name'],
        ]);
    }

    public function broadcastScreenShare(Project $project, User $user, array $shareData): void
    {
        $screenShare = [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'session_id' => $shareData['session_id'],
            'is_sharing' => $shareData['is_sharing'],
            'quality' => $shareData['quality'] ?? 'medium',
            'participants' => $shareData['participants'] ?? [],
            'timestamp' => now()->toISOString(),
        ];
        
        // Broadcast screen share status
        $this->broadcastToProject($project, 'screen.share', $screenShare);
        
        // Update screen share cache
        $cacheKey = "screen_share_{$project->id}_{$user->id}";
        
        if ($shareData['is_sharing']) {
            Cache::put($cacheKey, $screenShare, 3600);
        } else {
            Cache::forget($cacheKey);
        }
    }

    public function getActiveUsers(Project $project): Collection
    {
        $presenceKeys = Cache::getRedis()->keys("presence_{$project->id}_*");
        $activeUsers = collect();
        
        foreach ($presenceKeys as $key) {
            $presence = Cache::get($key);
            if ($presence && now()->diffInMinutes(Carbon::parse($presence['last_activity'])) < 5) {
                $activeUsers->push($presence);
            }
        }
        
        return $activeUsers;
    }

    public function getTypingUsers(Project $project, string $context): Collection
    {
        $typingKeys = Cache::getRedis()->keys("typing_{$project->id}_{$context}_*");
        $typingUsers = collect();
        
        foreach ($typingKeys as $key) {
            $typing = Cache::get($key);
            if ($typing && $typing['is_typing']) {
                $typingUsers->push($typing);
            }
        }
        
        return $typingUsers;
    }

    public function getDocumentState(Project $project, string $documentId): array
    {
        $state = Cache::get("document_state_{$project->id}_{$documentId}", [
            'content' => '',
            'version' => 0,
            'operations' => [],
            'last_modified' => now()->toISOString(),
        ]);
        
        return $state;
    }

    public function handleConflictResolution(Project $project, array $conflictData): array
    {
        $conflictId = $conflictData['conflict_id'];
        $operations = $conflictData['operations'];
        
        $resolution = [
            'conflict_id' => $conflictId,
            'resolution_strategy' => $this->determineResolutionStrategy($operations),
            'resolved_operations' => [],
            'rejected_operations' => [],
            'timestamp' => now()->toISOString(),
        ];
        
        // Apply conflict resolution
        foreach ($operations as $operation) {
            if ($this->canApplyOperation($operation, $resolution['resolution_strategy'])) {
                $resolution['resolved_operations'][] = $operation;
            } else {
                $resolution['rejected_operations'][] = $operation;
            }
        }
        
        // Broadcast resolution
        $this->broadcastToProject($project, 'conflict.resolved', $resolution);
        
        return $resolution;
    }

    public function generateCollaborationInsights(Project $project): array
    {
        $activeUsers = $this->getActiveUsers($project);
        $recentActivity = $this->getRecentActivity($project);
        $engagementMetrics = $this->calculateEngagementMetrics($project);
        
        return [
            'active_participants' => $activeUsers->count(),
            'collaboration_intensity' => $this->calculateCollaborationIntensity($activeUsers),
            'peak_activity_hours' => $this->getPeakActivityHours($project),
            'most_active_users' => $this->getMostActiveUsers($project),
            'engagement_score' => $engagementMetrics['score'],
            'communication_patterns' => $this->analyzeCommunicationPatterns($project),
            'productivity_impact' => $this->measureProductivityImpact($project),
        ];
    }

    // Helper methods
    private function generateSessionId(Project $project, User $user): string
    {
        return md5($project->id . '_' . $user->id . '_' . time());
    }

    private function initializePresence(User $user, Project $project): array
    {
        return [
            'user_id' => $user->id,
            'status' => 'online',
            'joined_at' => now()->toISOString(),
            'current_view' => 'dashboard',
            'permissions' => $this->getUserPermissions($user, $project),
        ];
    }

    private function getAvailableFeatures(Project $project): array
    {
        return [
            'real_time_editing' => true,
            'cursor_tracking' => true,
            'typing_indicators' => true,
            'voice_chat' => false, // TODO: Implement voice chat
            'video_chat' => false, // TODO: Implement video chat
            'screen_sharing' => true,
            'file_sharing' => true,
            'whiteboard' => false, // TODO: Implement whiteboard
        ];
    }

    private function getUserPermissions(User $user, Project $project): array
    {
        $member = $project->teamMembers()->where('user_id', $user->id)->first();
        
        if (!$member) {
            return ['view' => true];
        }
        
        return [
            'view' => true,
            'edit' => in_array($member->role, ['owner', 'admin', 'member']),
            'delete' => in_array($member->role, ['owner', 'admin']),
            'invite' => in_array($member->role, ['owner', 'admin']),
            'manage' => in_array($member->role, ['owner']),
        ];
    }

    private function broadcastToProject(Project $project, string $event, array $data, ?int $excludeUserId = null): void
    {
        $channel = "project.{$project->id}";
        
        // In a real implementation, this would use WebSockets or Pusher
        // For now, we'll store in cache for polling
        $broadcastData = [
            'event' => $event,
            'data' => $data,
            'timestamp' => now()->toISOString(),
            'exclude_user_id' => $excludeUserId,
        ];
        
        Cache::put("broadcast_{$channel}_{$event}", $broadcastData, 60);
    }

    private function logCollaborationActivity(Project $project, User $user, string $action, array $metadata): void
    {
        $activity = [
            'project_id' => $project->id,
            'user_id' => $user->id,
            'action' => $action,
            'metadata' => $metadata,
            'timestamp' => now()->toISOString(),
        ];
        
        Cache::push("collaboration_activity_{$project->id}", $activity);
        
        // Keep only last 100 activities
        $activities = Cache::get("collaboration_activity_{$project->id}", []);
        if (count($activities) > 100) {
            Cache::put("collaboration_activity_{$project->id}", array_slice($activities, -100));
        }
    }

    private function notifyMentionedUsers(Project $project, array $comment, User $author): void
    {
        foreach ($comment['mentions'] as $mention) {
            $notification = [
                'type' => 'mention',
                'project_id' => $project->id,
                'comment_id' => $comment['id'],
                'mentioned_by' => $author->id,
                'timestamp' => now()->toISOString(),
            ];
            
            Cache::push("notifications_{$mention}", $notification);
        }
    }

    private function getTaskVersion(Task $task): int
    {
        return Cache::get("task_version_{$task->id}", 1);
    }

    private function applyOperationalTransformation(Project $project, array $edit): array
    {
        // Simplified operational transformation
        // In a real implementation, this would handle complex transformation logic
        
        $documentState = $this->getDocumentState($project, 'main');
        
        // Apply transformation based on current state
        $transformedEdit = $edit;
        
        // Update document state
        $documentState['version']++;
        $documentState['operations'][] = $edit;
        $documentState['last_modified'] = now()->toISOString();
        
        Cache::put("document_state_{$project->id}_main", $documentState, 3600);
        
        return $transformedEdit;
    }

    private function determineResolutionStrategy(array $operations): string
    {
        // Determine conflict resolution strategy based on operations
        $operationCount = count($operations);
        
        if ($operationCount === 1) {
            return 'accept_single';
        } elseif ($operationCount === 2) {
            return 'merge_operations';
        } else {
            return 'majority_vote';
        }
    }

    private function canApplyOperation(array $operation, string $strategy): bool
    {
        // Simplified logic for determining if operation can be applied
        return match($strategy) {
            'accept_single' => true,
            'merge_operations' => true,
            'majority_vote' => rand(0, 1) === 1, // Simplified voting
            default => true,
        };
    }

    private function getRecentActivity(Project $project): Collection
    {
        return collect(Cache::get("collaboration_activity_{$project->id}", []))
            ->sortByDesc('timestamp')
            ->take(20);
    }

    private function calculateEngagementMetrics(Project $project): array
    {
        $activeUsers = $this->getActiveUsers($project);
        $recentActivity = $this->getRecentActivity($project);
        
        $score = 0;
        
        // Score based on active users
        $score += min(40, $activeUsers->count() * 10);
        
        // Score based on activity frequency
        $score += min(60, $recentActivity->count() * 3);
        
        return [
            'score' => min(100, $score),
            'active_users' => $activeUsers->count(),
            'activity_count' => $recentActivity->count(),
        ];
    }

    private function calculateCollaborationIntensity(Collection $activeUsers): string
    {
        $count = $activeUsers->count();
        
        if ($count >= 5) return 'high';
        if ($count >= 3) return 'medium';
        return 'low';
    }

    private function getPeakActivityHours(Project $project): array
    {
        // Simplified peak hours calculation
        $activity = $this->getRecentActivity($project);
        $hourlyCounts = [];
        
        foreach ($activity as $item) {
            $hour = Carbon::parse($item['timestamp'])->hour;
            $hourlyCounts[$hour] = ($hourlyCounts[$hour] ?? 0) + 1;
        }
        
        arsort($hourlyCounts);
        
        return array_slice($hourlyCounts, 0, 3, true);
    }

    private function getMostActiveUsers(Project $project): array
    {
        $activity = $this->getRecentActivity($project);
        $userCounts = [];
        
        foreach ($activity as $item) {
            $userId = $item['user_id'];
            $userCounts[$userId] = ($userCounts[$userId] ?? 0) + 1;
        }
        
        arsort($userCounts);
        
        return array_slice($userCounts, 0, 5, true);
    }

    private function analyzeCommunicationPatterns(Project $project): array
    {
        $activity = $this->getRecentActivity($project);
        $patterns = [
            'comments_per_user' => [],
            'edits_per_user' => [],
            'file_shares' => 0,
        ];
        
        foreach ($activity as $item) {
            $userId = $item['user_id'];
            
            switch ($item['action']) {
                case 'comment_added':
                    $patterns['comments_per_user'][$userId] = ($patterns['comments_per_user'][$userId] ?? 0) + 1;
                    break;
                case 'task_updated':
                    $patterns['edits_per_user'][$userId] = ($patterns['edits_per_user'][$userId] ?? 0) + 1;
                    break;
                case 'file_shared':
                    $patterns['file_shares']++;
                    break;
            }
        }
        
        return $patterns;
    }

    private function measureProductivityImpact(Project $project): array
    {
        // Simplified productivity impact measurement
        $activeUsers = $this->getActiveUsers($project);
        $recentActivity = $this->getRecentActivity($project);
        
        $productivityActions = $recent->filter(function ($item) {
            return in_array($item['action'], ['task_updated', 'task_completed', 'file_shared']);
        });
        
        return [
            'productivity_actions' => $productivityActions->count(),
            'actions_per_user' => $activeUsers->count() > 0 ? $productivityActions->count() / $activeUsers->count() : 0,
            'collaboration_efficiency' => $this->calculateCollaborationEfficiency($project),
        ];
    }

    private function calculateCollaborationEfficiency(Project $project): float
    {
        // Simplified efficiency calculation
        $activeUsers = $this->getActiveUsers($project);
        $recentActivity = $this->getRecentActivity($project);
        
        if ($activeUsers->count() === 0) return 0;
        
        $productiveActions = $recent->filter(function ($item) {
            return in_array($item['action'], ['task_updated', 'task_completed', 'comment_added']);
        })->count();
        
        $totalActions = $recent->count();
        
        return $totalActions > 0 ? ($productiveActions / $totalActions) * 100 : 0;
    }
}
