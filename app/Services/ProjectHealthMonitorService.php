<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Task;
use App\Models\Budget;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ProjectHealthMonitorService
{
    /**
     * Real-time project health monitoring with predictive alerts
     * Monitors multiple health indicators and provides actionable insights
     */
    public function getProjectHealth(Project $project): array
    {
        $healthIndicators = [
            'schedule_health' => $this->calculateScheduleHealth($project),
            'budget_health' => $this->calculateBudgetHealth($project),
            'team_health' => $this->calculateTeamHealth($project),
            'quality_health' => $this->calculateQualityHealth($project),
            'risk_health' => $this->calculateRiskHealth($project),
        ];

        $overallHealth = $this->calculateOverallHealth($healthIndicators);
        $healthTrend = $this->calculateHealthTrend($project);
        $predictiveAlerts = $this->generatePredictiveAlerts($project, $healthIndicators);

        return [
            'project_id' => $project->id,
            'overall_health' => $overallHealth,
            'health_score' => $overallHealth['score'],
            'health_trend' => $healthTrend,
            'indicators' => $healthIndicators,
            'predictive_alerts' => $predictiveAlerts,
            'actionable_insights' => $this->generateActionableInsights($healthIndicators, $predictiveAlerts),
            'health_history' => $this->getHealthHistory($project),
        ];
    }

    private function calculateScheduleHealth(Project $project): array
    {
        $tasks = $project->tasks()->get();
        $totalTasks = $tasks->count();

        if ($totalTasks === 0) {
            return ['score' => 100, 'status' => 'excellent', 'details' => 'No tasks to evaluate'];
        }

        $completedTasks = $tasks->where('status', 'completed')->count();
        $overdueTasks = $tasks->where('status', '!=', 'completed')
            ->where('due_at', '<', now())
            ->count();
        $dueSoonTasks = $tasks->where('status', '!=', 'completed')
            ->where('due_at', '>', now())
            ->where('due_at', '<', now()->addDays(7))
            ->count();

        $completionRate = ($completedTasks / $totalTasks) * 100;
        $overdueRate = ($overdueTasks / $totalTasks) * 100;

        $score = 100 - ($overdueRate * 2) - ($dueSoonTasks * 0.5);
        $score = max(0, min(100, $score));

        $status = match(true) {
            $score >= 80 => 'excellent',
            $score >= 60 => 'good',
            $score >= 40 => 'fair',
            default => 'poor',
        };

        return [
            'score' => round($score),
            'status' => $status,
            'details' => [
                'completion_rate' => round($completionRate, 1),
                'overdue_tasks' => $overdueTasks,
                'due_soon_tasks' => $dueSoonTasks,
                'total_tasks' => $totalTasks,
            ],
        ];
    }

    private function calculateBudgetHealth(Project $project): array
    {
        $budgets = $project->budgets()->get();

        if ($budgets->isEmpty()) {
            return ['score' => 100, 'status' => 'excellent', 'details' => 'No budgets to evaluate'];
        }

        $totalBudget = $budgets->sum('amount');
        $totalSpent = $budgets->sum('spent');

        if ($totalBudget == 0) {
            return ['score' => 100, 'status' => 'excellent', 'details' => 'No budget set'];
        }

        $spendingRate = ($totalSpent / $totalBudget) * 100;
        $overBudget = $budgets->filter(fn($b) => $b->spent > $b->amount)->count();

        $score = 100;
        
        // Penalize for over-budget items
        $score -= ($overBudget * 15);
        
        // Penalize for spending too fast (more than 80% with less than 80% tasks complete)
        $tasks = $project->tasks()->get();
        $completionRate = $tasks->count() > 0 
            ? ($tasks->where('status', 'completed')->count() / $tasks->count()) * 100 
            : 0;

        if ($spendingRate > 80 && $completionRate < 80) {
            $score -= 20;
        }

        $score = max(0, min(100, $score));

        $status = match(true) {
            $score >= 80 => 'excellent',
            $score >= 60 => 'good',
            $score >= 40 => 'fair',
            default => 'poor',
        };

        return [
            'score' => round($score),
            'status' => $status,
            'details' => [
                'spending_rate' => round($spendingRate, 1),
                'total_budget' => $totalBudget,
                'total_spent' => $totalSpent,
                'over_budget_items' => $overBudget,
            ],
        ];
    }

    private function calculateTeamHealth(Project $project): array
    {
        $team = $project->team;
        
        if (!$team) {
            return ['score' => 100, 'status' => 'excellent', 'details' => 'Individual project'];
        }

        $members = $team->members()->where('is_active', true)->get();
        $memberCount = $members->count();

        if ($memberCount === 0) {
            return ['score' => 0, 'status' => 'poor', 'details' => 'No active team members'];
        }

        // Calculate member engagement
        $activeMembers = 0;
        $overloadedMembers = 0;

        foreach ($members as $member) {
            $activeTasks = $member->assignedTasks()
                ->where('project_id', $project->id)
                ->where('status', '!=', 'completed')
                ->count();

            if ($activeTasks > 0) {
                $activeMembers++;
            }

            if ($activeTasks > 5) {
                $overloadedMembers++;
            }
        }

        $engagementRate = ($activeMembers / $memberCount) * 100;
        $overloadRate = ($overloadedMembers / $memberCount) * 100;

        $score = $engagementRate - ($overloadRate * 20);
        $score = max(0, min(100, $score));

        $status = match(true) {
            $score >= 80 => 'excellent',
            $score >= 60 => 'good',
            $score >= 40 => 'fair',
            default => 'poor',
        };

        return [
            'score' => round($score),
            'status' => $status,
            'details' => [
                'total_members' => $memberCount,
                'active_members' => $activeMembers,
                'engagement_rate' => round($engagementRate, 1),
                'overloaded_members' => $overloadedMembers,
            ],
        ];
    }

    private function calculateQualityHealth(Project $project): array
    {
        $tasks = $project->tasks()->get();
        $totalTasks = $tasks->count();

        if ($totalTasks === 0) {
            return ['score' => 100, 'status' => 'excellent', 'details' => 'No tasks to evaluate'];
        }

        // Quality metrics based on task completion patterns
        $completedTasks = $tasks->where('status', 'completed');
        $reopenedTasks = $tasks->where('status', 'reopened')->count();
        $tasksWithComments = $tasks->filter(fn($t) => $t->comments()->count() > 0)->count();

        if ($completedTasks->isEmpty()) {
            return ['score' => 100, 'status' => 'excellent', 'details' => 'No completed tasks yet'];
        }

        // Calculate quality score based on reopen rate and collaboration
        $reopenRate = ($reopenedTasks / $totalTasks) * 100;
        $collaborationRate = ($tasksWithComments / $totalTasks) * 100;

        $score = 100 - ($reopenRate * 10) + ($collaborationRate * 0.1);
        $score = max(0, min(100, $score));

        $status = match(true) {
            $score >= 80 => 'excellent',
            $score >= 60 => 'good',
            $score >= 40 => 'fair',
            default => 'poor',
        };

        return [
            'score' => round($score),
            'status' => $status,
            'details' => [
                'reopen_rate' => round($reopenRate, 1),
                'collaboration_rate' => round($collaborationRate, 1),
                'reopened_tasks' => $reopenedTasks,
                'tasks_with_comments' => $tasksWithComments,
            ],
        ];
    }

    private function calculateRiskHealth(Project $project): array
    {
        $tasks = $project->tasks()->get();
        $totalTasks = $tasks->count();

        if ($totalTasks === 0) {
            return ['score' => 100, 'status' => 'excellent', 'details' => 'No tasks to evaluate'];
        }

        $highPriorityTasks = $tasks->where('priority', 'high')->count();
        $blockedTasks = $tasks->where('status', 'blocked')->count();
        $criticalTasks = $tasks->where('priority', 'critical')->count();

        $riskScore = 0;
        $riskScore += ($highPriorityTasks * 5);
        $riskScore += ($blockedTasks * 15);
        $riskScore += ($criticalTasks * 10);

        $normalizedRisk = min(100, $riskScore / $totalTasks * 10);
        $score = 100 - $normalizedRisk;

        $status = match(true) {
            $score >= 80 => 'excellent',
            $score >= 60 => 'good',
            $score >= 40 => 'fair',
            default => 'poor',
        };

        return [
            'score' => round($score),
            'status' => $status,
            'details' => [
                'high_priority_tasks' => $highPriorityTasks,
                'blocked_tasks' => $blockedTasks,
                'critical_tasks' => $criticalTasks,
                'risk_score' => round($normalizedRisk, 1),
            ],
        ];
    }

    private function calculateOverallHealth(array $indicators): array
    {
        $scores = array_column($indicators, 'score');
        $averageScore = array_sum($scores) / count($scores);

        $status = match(true) {
            $averageScore >= 80 => 'excellent',
            $averageScore >= 60 => 'good',
            $averageScore >= 40 => 'fair',
            default => 'poor',
        };

        return [
            'score' => round($averageScore),
            'status' => $status,
        ];
    }

    private function calculateHealthTrend(Project $project): string
    {
        // Compare current health with previous week
        // In a real implementation, this would query historical health data
        $previousHealth = $this->getPreviousWeekHealth($project);
        $currentHealth = $this->getProjectHealth($project)['health_score'];

        if ($currentHealth > $previousHealth + 5) {
            return 'improving';
        } elseif ($currentHealth < $previousHealth - 5) {
            return 'declining';
        } else {
            return 'stable';
        }
    }

    private function getPreviousWeekHealth(Project $project): float
    {
        // In a real implementation, this would fetch from historical data
        // For now, return a placeholder
        return 75;
    }

    private function generatePredictiveAlerts(Project $project, array $indicators): array
    {
        $alerts = [];

        // Schedule alerts
        if ($indicators['schedule_health']['score'] < 60) {
            $alerts[] = [
                'type' => 'schedule',
                'severity' => $indicators['schedule_health']['score'] < 40 ? 'critical' : 'warning',
                'message' => 'Schedule health is declining',
                'prediction' => 'Project may miss deadline by 2-3 weeks if current trend continues',
                'suggested_action' => 'Review task dependencies and consider resource reallocation',
            ];
        }

        // Budget alerts
        if ($indicators['budget_health']['score'] < 60) {
            $alerts[] = [
                'type' => 'budget',
                'severity' => $indicators['budget_health']['score'] < 40 ? 'critical' : 'warning',
                'message' => 'Budget health is concerning',
                'prediction' => 'Budget may be exceeded by 15-20% at current spending rate',
                'suggested_action' => 'Review spending patterns and adjust forecasts',
            ];
        }

        // Team alerts
        if ($indicators['team_health']['score'] < 60) {
            $alerts[] = [
                'type' => 'team',
                'severity' => $indicators['team_health']['score'] < 40 ? 'critical' : 'warning',
                'message' => 'Team health needs attention',
                'prediction' => 'Team burnout risk increasing',
                'suggested_action' => 'Consider workload redistribution and team support',
            ];
        }

        return $alerts;
    }

    private function generateActionableInsights(array $indicators, array $alerts): array
    {
        $insights = [];

        // Generate insights based on indicator combinations
        if ($indicators['schedule_health']['score'] < 60 && $indicators['team_health']['score'] < 60) {
            $insights[] = [
                'type' => 'schedule_team',
                'priority' => 'high',
                'insight' => 'Schedule and team health are correlated',
                'recommendation' => 'Improving team engagement will likely improve schedule performance',
            ];
        }

        if ($indicators['quality_health']['score'] < 60 && $indicators['risk_health']['score'] < 60) {
            $insights[] = [
                'type' => 'quality_risk',
                'priority' => 'medium',
                'insight' => 'Quality issues are contributing to project risk',
                'recommendation' => 'Implement quality gates and peer review processes',
            ];
        }

        return $insights;
    }

    private function getHealthHistory(Project $project): array
    {
        // In a real implementation, this would fetch historical health data
        // For now, return placeholder data
        return [
            'dates' => [],
            'scores' => [],
        ];
    }
}
