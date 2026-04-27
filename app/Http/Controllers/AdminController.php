<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Models\Project;
use App\Models\User;
use App\Services\AnalyticsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminController extends Controller
{
    public function __construct(private readonly AnalyticsService $analyticsService)
    {
    }

    public function index(): View
    {
        $hasFlaggedColumn = Schema::hasColumn('chat_messages', 'flagged');

        $metrics = Cache::remember('admin:metrics', now()->addSeconds(60), function () use ($hasFlaggedColumn): array {
            $activeUsers = DB::table('sessions')
                ->whereNotNull('user_id')
                ->where('last_activity', '>=', now()->subDays(7)->getTimestamp())
                ->distinct()
                ->count('user_id');

            return [
                'users' => User::count(),
                'active_users' => $activeUsers,
                'projects' => Project::count(),
                'ai_messages' => ChatMessage::where('sender', 'ai')->count(),
                'ai_sessions' => ChatSession::count(),
                'ai_messages_last_7_days' => ChatMessage::where('sender', 'ai')
                    ->where('created_at', '>=', now()->subDays(7))
                    ->count(),
                'flagged_content' => $hasFlaggedColumn ? ChatMessage::where('flagged', true)->count() : 0,
            ];
        });

        $recentUsers = User::query()
            ->latest()
            ->limit(8)
            ->get(['id', 'name', 'email', 'created_at', 'email_verified_at']);

        $recentFlagged = [];
        if (Schema::hasColumn('chat_messages', 'flagged')) {
            $recentFlagged = ChatMessage::query()
                ->where('flagged', true)
                ->with(['chatSession' => fn ($query) => $query->select(['id', 'title', 'user_id'])])
                ->latest()
                ->limit(8)
                ->get(['id', 'chat_session_id', 'sender', 'message', 'created_at']);
        }

        $analytics = Cache::remember('admin:analytics', now()->addMinutes(5), function (): array {
            return $this->analyticsService->summary();
        });

        return view('pages.admin', compact('metrics', 'recentUsers', 'recentFlagged', 'analytics'));
    }

    public function users(Request $request): View
    {
        $query = User::query();

        if ($search = $request->string('q')->trim()) {
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($role = $request->string('role')->lower()->value()) {
            $query->where('role', $role);
        }

        if ($status = $request->string('status')->lower()->value()) {
            if ($status === 'active') {
                $query->where('suspended', false);
            }
            if ($status === 'suspended') {
                $query->where('suspended', true);
            }
        }

        $users = $query->latest('created_at')
            ->paginate(15)
            ->withQueryString();

        $roles = [
            'user' => 'User',
            'admin' => 'Admin',
            'premium' => 'Premium',
        ];

        return view('pages.admin-users', compact('users', 'roles'));
    }

    public function suspendUser(User $user)
    {
        $currentUser = auth()->user();

        if (! $currentUser || $currentUser->id === $user->id) {
            abort(403);
        }

        if ($user->isSuperAdmin()) {
            abort(403);
        }

        if ($currentUser->isAdmin() && ! $currentUser->isSuperAdmin() && $user->isAdmin()) {
            abort(403);
        }

        $user->update(['suspended' => ! $user->suspended]);

        return back()->with('success', $user->suspended ? __('User suspended.') : __('User unsuspended.'));
    }
}
