<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Project;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function index(Project $project): JsonResponse
    {
        if (!$project->userCanView(Auth::user())) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to view comments on this project.',
            ]);
        }

        $comments = $project->topLevelComments()
            ->with(['user:id,name', 'replies.user:id,name'])
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'comments' => $comments,
        ]);
    }

    public function store(Request $request, Project $project): JsonResponse
    {
        if (!$project->userCanComment(Auth::user())) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to comment on this project.',
            ]);
        }

        $request->validate([
            'content' => ['required', 'string', 'min:1', 'max:2000'],
            'parent_id' => ['nullable', 'exists:comments,id'],
        ]);

        try {
            $comment = Comment::createComment([
                'user_id' => Auth::id(),
                'project_id' => $project->id,
                'parent_id' => $request->input('parent_id'),
                'content' => $request->input('content'),
            ]);

            // Load the comment with relationships
            $comment->load(['user:id,name', 'replies.user:id,name']);

            return response()->json([
                'success' => true,
                'comment' => $comment,
                'message' => 'Comment posted successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to post comment. Please try again.',
            ]);
        }
    }

    public function update(Request $request, Comment $comment): JsonResponse
    {
        if (!$comment->canEdit(Auth::user())) {
            return response()->json([
                'success' => false,
                'message' => 'You can only edit your own comments.',
            ]);
        }

        $request->validate([
            'content' => ['required', 'string', 'min:1', 'max:2000'],
        ]);

        try {
            $comment->edit($request->input('content'));

            // Load the updated comment with relationships
            $comment->load(['user:id,name', 'replies.user:id,name']);

            return response()->json([
                'success' => true,
                'comment' => $comment,
                'message' => 'Comment updated successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update comment. Please try again.',
            ]);
        }
    }

    public function destroy(Comment $comment): JsonResponse
    {
        if (!$comment->canDelete(Auth::user())) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to delete this comment.',
            ]);
        }

        try {
            $comment->delete();

            return response()->json([
                'success' => true,
                'message' => 'Comment deleted successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete comment. Please try again.',
            ]);
        }
    }

    public function getReplies(Comment $comment): JsonResponse
    {
        $project = $comment->project;
        
        if (!$project->userCanView(Auth::user())) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to view comments on this project.',
            ]);
        }

        $replies = $comment->replies()
            ->with('user:id,name')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'replies' => $replies,
        ]);
    }

    public function searchUsers(Request $request): JsonResponse
    {
        $request->validate([
            'query' => ['required', 'string', 'min:2', 'max:50'],
        ]);

        $query = $request->input('query');
        
        // Remove @ symbol if present
        $query = ltrim($query, '@');

        $users = User::where('name', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->limit(10)
            ->get(['id', 'name', 'email']);

        return response()->json([
            'success' => true,
            'users' => $users,
        ]);
    }

    public function getMentions(User $user = null): JsonResponse
    {
        $targetUser = $user ?: Auth::user();

        $mentions = $targetUser->mentions()
            ->with(['comment.user:id,name', 'comment.project:id,title'])
            ->whereHas('comment.project', function ($query) use ($targetUser) {
                $query->where(function ($q) use ($targetUser) {
                    $q->where('user_id', $targetUser->id)
                      ->orWhereHas('project.teamMembers', function ($q) use ($targetUser) {
                          $q->where('user_id', $targetUser->id)->where('is_active', true);
                      });
                });
            })
            ->latest()
            ->take(50)
            ->get();

        return response()->json([
            'success' => true,
            'mentions' => $mentions,
        ]);
    }

    public function markAsRead(): JsonResponse
    {
        $user = Auth::user();

        try {
            // Mark all unread mentions as read
            $user->mentions()
                ->whereDoesntHave('readMentions')
                ->get()
                ->each(function ($mention) {
                    $mention->readMentions()->create([
                        'user_id' => $user->id,
                    ]);
                });

            return response()->json([
                'success' => true,
                'message' => 'All mentions marked as read!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark mentions as read.',
            ]);
        }
    }

    public function getUnreadCount(): JsonResponse
    {
        $user = Auth::user();

        $unreadCount = $user->mentions()
            ->whereDoesntHave('readMentions')
            ->count();

        return response()->json([
            'success' => true,
            'unread_count' => $unreadCount,
        ]);
    }

    public function getActivityFeed(): JsonResponse
    {
        $user = Auth::user();

        // Get comments from projects the user can access
        $comments = Comment::whereHas('project', function ($query) use ($user) {
            $query->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhereHas('project.teamMembers', function ($q) use ($user) {
                      $q->where('user_id', $user->id)->where('is_active', true);
                  });
            });
        })
        ->with(['user:id,name', 'project:id,title'])
        ->latest()
        ->take(20)
        ->get();

        return response()->json([
            'success' => true,
            'comments' => $comments,
        ]);
    }

    public function getStats(Project $project): JsonResponse
    {
        if (!$project->userCanView(Auth::user())) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to view this project.',
            ]);
        }

        $totalComments = $project->comments()->count();
        $topLevelComments = $project->topLevelComments()->count();
        $replies = $totalComments - $topLevelComments;
        
        $mostActiveCommenters = $project->comments()
            ->selectRaw('user_id, COUNT(*) as comment_count')
            ->groupBy('user_id')
            ->orderByDesc('comment_count')
            ->limit(5)
            ->with('user:id,name')
            ->get();

        return response()->json([
            'success' => true,
            'stats' => [
                'total_comments' => $totalComments,
                'top_level_comments' => $topLevelComments,
                'replies' => $replies,
                'most_active_commenters' => $mostActiveCommenters,
            ],
        ]);
    }
}
