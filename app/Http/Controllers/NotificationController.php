<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * @var array<string, list<string>>
     */
    private const FILTER_TYPES = [
        'projects' => ['project_reminders'],
        'tasks' => ['task_deadlines'],
        'system' => ['ai_updates', 'system_alerts'],
    ];

    public function index(Request $request): View
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $activeFilter = $this->resolveFilter($request);

        $notifications = $user->notifications()
            ->tap(fn (Builder $query) => $this->applyFilter($query, $activeFilter))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('notifications.index', [
            'notifications' => $notifications,
            'activeFilter' => $activeFilter,
            'filterCounts' => $this->filterCounts($user->notifications()),
        ]);
    }

    public function feed(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $notifications = $user->notifications()
            ->latest()
            ->limit(20)
            ->get();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $user->notifications()->unread()->count(),
        ]);
    }

    public function markAsRead(Request $request, Notification $notification): JsonResponse
    {
        // Ensure the notification belongs to the authenticated user
        if ($notification->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $user->notifications()->unread()->update([
            'read' => true,
            'read_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    private function resolveFilter(Request $request): string
    {
        $filter = $request->query('filter', 'all');
        $allowedFilters = ['all', 'unread', 'projects', 'tasks', 'system'];

        return in_array($filter, $allowedFilters, true) ? $filter : 'all';
    }

    private function applyFilter(Builder $query, string $activeFilter): void
    {
        if ($activeFilter === 'unread') {
            $query->unread();
            return;
        }

        if (isset(self::FILTER_TYPES[$activeFilter])) {
            $query->whereIn('type', self::FILTER_TYPES[$activeFilter]);
        }
    }

    private function filterCounts(Builder|HasMany $baseQuery): array
    {
        $counts = (clone $baseQuery)
            ->selectRaw('COUNT(*) as all_count')
            ->selectRaw('SUM(CASE WHEN read = 0 THEN 1 ELSE 0 END) as unread_count')
            ->selectRaw("SUM(CASE WHEN type = 'project_reminders' THEN 1 ELSE 0 END) as projects_count")
            ->selectRaw("SUM(CASE WHEN type = 'task_deadlines' THEN 1 ELSE 0 END) as tasks_count")
            ->selectRaw("SUM(CASE WHEN type IN ('ai_updates', 'system_alerts') THEN 1 ELSE 0 END) as system_count")
            ->first();

        return [
            'all' => (int) ($counts?->all_count ?? 0),
            'unread' => (int) ($counts?->unread_count ?? 0),
            'projects' => (int) ($counts?->projects_count ?? 0),
            'tasks' => (int) ($counts?->tasks_count ?? 0),
            'system' => (int) ($counts?->system_count ?? 0),
        ];
    }
}
