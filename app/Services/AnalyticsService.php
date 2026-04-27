<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    public function summary(): array
    {
        return [
            'daily_signups' => $this->dailySignups(),
            'most_used_features' => $this->mostUsedFeatures(),
            'retention' => $this->retention(),
            'project_completion_rate' => $this->projectCompletionRate(),
        ];
    }

    public function dailySignups(int $days = 14): array
    {
        $startDate = now()->startOfDay()->subDays($days - 1);
        $rows = User::query()
            ->where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as signup_date, COUNT(*) as total')
            ->groupBy('signup_date')
            ->orderBy('signup_date')
            ->get()
            ->keyBy('signup_date');

        $series = collect(range(0, $days - 1))
            ->map(function (int $offset) use ($startDate, $rows): array {
                $date = $startDate->copy()->addDays($offset)->toDateString();
                $count = (int) ($rows[$date]->total ?? 0);

                return [
                    'date' => $date,
                    'count' => $count,
                ];
            })
            ->all();

        return [
            'today' => (int) User::query()->whereDate('created_at', now()->toDateString())->count(),
            'last_7_days' => (int) User::query()->where('created_at', '>=', now()->subDays(7))->count(),
            'series' => $series,
        ];
    }

    public function mostUsedFeatures(): array
    {
        $featureCounts = [
            ['name' => 'Projects', 'count' => Project::count()],
            ['name' => 'Tasks', 'count' => Task::count()],
            ['name' => 'AI Chat Sessions', 'count' => ChatSession::count()],
            ['name' => 'AI Chat Messages', 'count' => ChatMessage::count()],
            ['name' => 'Budgets', 'count' => Budget::count()],
        ];

        usort($featureCounts, fn (array $a, array $b): int => $b['count'] <=> $a['count']);

        return $featureCounts;
    }

    public function retention(): array
    {
        $activeUserIds30 = $this->activeUserIdsWithinDays(30);
        $activeUserIds7 = $this->activeUserIdsWithinDays(7);

        $cohort30 = User::query()
            ->whereBetween('created_at', [now()->subDays(60), now()->subDays(30)])
            ->pluck('id');

        $cohort7 = User::query()
            ->whereBetween('created_at', [now()->subDays(14), now()->subDays(7)])
            ->pluck('id');

        return [
            'rolling_30_day' => $this->percentage($activeUserIds30->count(), User::count()),
            'rolling_7_day' => $this->percentage($activeUserIds7->count(), User::count()),
            'cohort_30_day' => $this->cohortRetention($cohort30, $activeUserIds30),
            'cohort_7_day' => $this->cohortRetention($cohort7, $activeUserIds7),
        ];
    }

    public function projectCompletionRate(): array
    {
        $totalProjects = Project::count();
        $completedProjects = Project::query()
            ->where(function ($query) {
                $query->where('status', 'completed')
                    ->orWhere('progress', '>=', 100);
            })
            ->count();

        return [
            'completed' => $completedProjects,
            'total' => $totalProjects,
            'rate' => $this->percentage($completedProjects, $totalProjects),
        ];
    }

    private function activeUserIdsWithinDays(int $days): Collection
    {
        $windowStart = now()->subDays($days);
        $sessionCutoff = $windowStart->getTimestamp();

        $sessionUserIds = DB::table('sessions')
            ->whereNotNull('user_id')
            ->where('last_activity', '>=', $sessionCutoff)
            ->pluck('user_id');

        $projectUserIds = Project::query()
            ->where('created_at', '>=', $windowStart)
            ->pluck('user_id');

        $taskUserIds = Task::query()
            ->where('created_at', '>=', $windowStart)
            ->pluck('user_id');

        $chatUserIds = ChatSession::query()
            ->where('created_at', '>=', $windowStart)
            ->pluck('user_id');

        return $sessionUserIds
            ->merge($projectUserIds)
            ->merge($taskUserIds)
            ->merge($chatUserIds)
            ->filter()
            ->unique()
            ->values();
    }

    private function cohortRetention(Collection $cohortUserIds, Collection $activeUserIds): float
    {
        if ($cohortUserIds->isEmpty()) {
            return 0.0;
        }

        $retained = $cohortUserIds->intersect($activeUserIds)->count();

        return $this->percentage($retained, $cohortUserIds->count());
    }

    private function percentage(int $numerator, int $denominator): float
    {
        if ($denominator === 0) {
            return 0.0;
        }

        return round(($numerator / $denominator) * 100, 1);
    }
}
