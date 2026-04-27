<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Task;
use App\Models\Budget;
use App\Models\User;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class ProjectAnalyticsService
{
    public function generateProjectInsights(Project $project): array
    {
        return [
            'health_score' => $this->calculateProjectHealth($project),
            'risk_factors' => $this->identifyRiskFactors($project),
            'performance_metrics' => $this->calculatePerformanceMetrics($project),
            'team_productivity' => $this->analyzeTeamProductivity($project),
            'budget_analysis' => $this->analyzeBudgetPerformance($project),
            'timeline_predictions' => $this->predictTimeline($project),
            'recommendations' => $this->generateRecommendations($project),
            'comparative_analysis' => $this->compareToSimilarProjects($project),
        ];
    }

    public function calculateProjectHealth(Project $project): array
    {
        $tasks = $project->tasks;
        $totalTasks = $tasks->count();
        $completedTasks = $tasks->where('status', 'completed')->count();
        $overdueTasks = $tasks->where('due_date', '<', now())->where('status', '!=', 'completed')->count();
        
        $budgets = $project->budgets;
        $totalBudget = $budgets->sum('amount');
        $spentBudget = $budgets->sum('spent');
        
        $progressScore = $totalTasks > 0 ? ($completedTasks / $totalTasks) * 100 : 0;
        $budgetScore = $totalBudget > 0 ? (($totalBudget - $spentBudget) / $totalBudget) * 100 : 100;
        $timelineScore = $totalTasks > 0 ? (($totalTasks - $overdueTasks) / $totalTasks) * 100 : 100;
        
        $overallHealth = ($progressScore + $budgetScore + $timelineScore) / 3;
        
        return [
            'overall_score' => round($overallHealth, 1),
            'progress_score' => round($progressScore, 1),
            'budget_score' => round($budgetScore, 1),
            'timeline_score' => round($timelineScore, 1),
            'status' => $this->getHealthStatus($overallHealth),
            'trend' => $this->calculateHealthTrend($project),
        ];
    }

    public function identifyRiskFactors(Project $project): array
    {
        $risks = [];
        $tasks = $project->tasks;
        
        // Timeline risks
        $overdueTasks = $tasks->where('due_date', '<', now())->where('status', '!=', 'completed');
        if ($overdueTasks->count() > 0) {
            $risks[] = [
                'type' => 'timeline',
                'severity' => $overdueTasks->count() > 3 ? 'high' : 'medium',
                'description' => "{$overdueTasks->count()} tasks are overdue",
                'impact' => 'Project timeline at risk',
                'mitigation' => 'Reassess task priorities and deadlines',
            ];
        }
        
        // Budget risks
        $budgets = $project->budgets;
        $overBudgetBudgets = $budgets->where('spent', '>', 'amount');
        if ($overBudgetBudgets->count() > 0) {
            $risks[] = [
                'type' => 'budget',
                'severity' => 'high',
                'description' => 'Budget overruns detected',
                'impact' => 'Financial constraints may impact project completion',
                'mitigation' => 'Review spending and adjust budget allocations',
            ];
        }
        
        // Resource risks
        $unassignedTasks = $tasks->where('assigned_to', null);
        if ($unassignedTasks->count() > 0) {
            $risks[] = [
                'type' => 'resource',
                'severity' => 'medium',
                'description' => "{$unassignedTasks->count()} tasks unassigned",
                'impact' => 'Resource allocation issues',
                'mitigation' => 'Assign tasks to available team members',
            ];
        }
        
        // Dependency risks
        $blockedTasks = $tasks->filter(function ($task) {
            return $task->dependencies && count($task->dependencies) > 0;
        });
        
        if ($blockedTasks->count() > 0) {
            $risks[] = [
                'type' => 'dependency',
                'severity' => 'medium',
                'description' => "{$blockedTasks->count()} tasks have dependencies",
                'impact' => 'Task dependencies may cause delays',
                'mitigation' => 'Review and optimize task dependencies',
            ];
        }
        
        return $risks;
    }

    public function calculatePerformanceMetrics(Project $project): array
    {
        $tasks = $project->tasks;
        $completedTasks = $tasks->where('status', 'completed');
        
        // Velocity metrics
        $velocity = $this->calculateVelocity($completedTasks);
        
        // Efficiency metrics
        $efficiency = $this->calculateEfficiency($tasks);
        
        // Quality metrics
        $quality = $this->calculateQuality($completedTasks);
        
        return [
            'velocity' => $velocity,
            'efficiency' => $efficiency,
            'quality' => $quality,
            'productivity_index' => $this->calculateProductivityIndex($project),
            'completion_rate' => $tasks->count() > 0 ? ($completedTasks->count() / $tasks->count()) * 100 : 0,
        ];
    }

    public function analyzeTeamProductivity(Project $project): array
    {
        $teamMembers = $project->teamMembers;
        $analysis = [];
        
        foreach ($teamMembers as $member) {
            $memberTasks = $project->tasks->where('assigned_to', $member->id);
            $completedTasks = $memberTasks->where('status', 'completed');
            
            $analysis[$member->id] = [
                'name' => $member->name,
                'total_tasks' => $memberTasks->count(),
                'completed_tasks' => $completedTasks->count(),
                'completion_rate' => $memberTasks->count() > 0 ? ($completedTasks->count() / $memberTasks->count()) * 100 : 0,
                'average_completion_time' => $this->calculateAverageCompletionTime($completedTasks),
                'productivity_score' => $this->calculateProductivityScore($memberTasks, $completedTasks),
                'workload_balance' => $this->calculateWorkloadBalance($memberTasks),
            ];
        }
        
        return $analysis;
    }

    public function analyzeBudgetPerformance(Project $project): array
    {
        $budgets = $project->budgets;
        $totalBudget = $budgets->sum('amount');
        $totalSpent = $budgets->sum('spent');
        
        return [
            'total_budget' => $totalBudget,
            'total_spent' => $totalSpent,
            'remaining_budget' => $totalBudget - $totalSpent,
            'budget_utilization' => $totalBudget > 0 ? ($totalSpent / $totalBudget) * 100 : 0,
            'burn_rate' => $this->calculateBurnRate($project),
            'projected_completion_cost' => $this->projectCompletionCost($project),
            'budget_variance' => $this->calculateBudgetVariance($project),
            'cost_efficiency' => $this->calculateCostEfficiency($project),
        ];
    }

    public function predictTimeline(Project $project): array
    {
        $tasks = $project->tasks;
        $remainingTasks = $tasks->where('status', '!=', 'completed');
        
        $currentVelocity = $this->calculateVelocity($tasks->where('status', 'completed'));
        $estimatedDays = $remainingTasks->count() > 0 && $currentVelocity > 0 ? 
            ceil($remainingTasks->count() / $currentVelocity) : null;
        
        return [
            'estimated_completion_date' => $estimatedDays ? now()->addDays($estimatedDays) : null,
            'confidence_level' => $this->calculatePredictionConfidence($project),
            'risk_factors' => $this->identifyTimelineRisks($project),
            'optimistic_estimate' => $estimatedDays ? now()->addDays(ceil($estimatedDays * 0.8)) : null,
            'pessimistic_estimate' => $estimatedDays ? now()->addDays(ceil($estimatedDays * 1.5)) : null,
        ];
    }

    public function generateRecommendations(Project $project): array
    {
        $recommendations = [];
        $health = $this->calculateProjectHealth($project);
        $risks = $this->identifyRiskFactors($project);
        
        // Health-based recommendations
        if ($health['overall_score'] < 70) {
            $recommendations[] = [
                'priority' => 'high',
                'category' => 'project_health',
                'action' => 'Conduct comprehensive project review',
                'reason' => 'Project health score is below optimal',
                'expected_impact' => 'Improve overall project performance',
            ];
        }
        
        // Risk-based recommendations
        foreach ($risks as $risk) {
            if ($risk['severity'] === 'high') {
                $recommendations[] = [
                    'priority' => 'high',
                    'category' => 'risk_mitigation',
                    'action' => $risk['mitigation'],
                    'reason' => $risk['description'],
                    'expected_impact' => $risk['impact'],
                ];
            }
        }
        
        // Performance-based recommendations
        $performance = $this->calculatePerformanceMetrics($project);
        if ($performance['velocity'] < 2) {
            $recommendations[] = [
                'priority' => 'medium',
                'category' => 'productivity',
                'action' => 'Optimize task allocation and remove bottlenecks',
                'reason' => 'Team velocity is below optimal',
                'expected_impact' => 'Increase development speed',
            ];
        }
        
        return $recommendations;
    }

    public function compareToSimilarProjects(Project $project): array
    {
        $similarProjects = $this->findSimilarProjects($project);
        $comparison = [];
        
        foreach ($similarProjects as $similar) {
            $comparison[] = [
                'project_name' => $similar->title,
                'similarity_score' => $this->calculateSimilarity($project, $similar),
                'health_comparison' => $this->compareHealth($project, $similar),
                'performance_comparison' => $this->comparePerformance($project, $similar),
                'lessons_learned' => $this->extractLessons($similar),
            ];
        }
        
        return $comparison;
    }

    // Helper methods
    private function getHealthStatus(float $score): string
    {
        if ($score >= 80) return 'excellent';
        if ($score >= 60) return 'good';
        if ($score >= 40) return 'fair';
        return 'poor';
    }

    private function calculateHealthTrend(Project $project): string
    {
        // Compare current health with previous period
        $previousHealth = $this->getPreviousHealthScore($project);
        $currentHealth = $this->calculateProjectHealth($project)['overall_score'];
        
        if ($previousHealth === null) return 'stable';
        
        $difference = $currentHealth - $previousHealth;
        
        if ($difference > 5) return 'improving';
        if ($difference < -5) return 'declining';
        return 'stable';
    }

    private function calculateVelocity(Collection $completedTasks): float
    {
        if ($completedTasks->isEmpty()) return 0;
        
        $lastWeek = $completedTasks->filter(function ($task) {
            return $task->completed_at && $task->completed_at->greaterThan(now()->subWeek());
        });
        
        return $lastWeek->count();
    }

    private function calculateEfficiency(Collection $tasks): array
    {
        $completedTasks = $tasks->where('status', 'completed');
        
        if ($completedTasks->isEmpty()) {
            return ['on_time_completion' => 0, 'within_budget' => 100];
        }
        
        $onTimeTasks = $completedTasks->filter(function ($task) {
            return $task->completed_at && (!$task->due_date || $task->completed_at->lessThanOrEqualTo($task->due_date));
        });
        
        return [
            'on_time_completion' => ($onTimeTasks->count() / $completedTasks->count()) * 100,
            'within_budget' => 100, // TODO: Implement budget efficiency calculation
        ];
    }

    private function calculateQuality(Collection $completedTasks): array
    {
        // Placeholder for quality metrics
        return [
            'defect_rate' => 0,
            'rework_rate' => 0,
            'customer_satisfaction' => 0,
        ];
    }

    private function calculateProductivityIndex(Project $project): float
    {
        $tasks = $project->tasks;
        $completedTasks = $tasks->where('status', 'completed');
        
        if ($tasks->isEmpty()) return 0;
        
        $completionRate = $completedTasks->count() / $tasks->count();
        $velocity = $this->calculateVelocity($completedTasks);
        $efficiency = $this->calculateEfficiency($tasks)['on_time_completion'] / 100;
        
        return ($completionRate + $velocity + $efficiency) / 3 * 100;
    }

    private function calculateAverageCompletionTime(Collection $completedTasks): float
    {
        if ($completedTasks->isEmpty()) return 0;
        
        $totalDays = $completedTasks->sum(function ($task) {
            if (!$task->completed_at || !$task->created_at) return 0;
            return $task->completed_at->diffInDays($task->created_at);
        });
        
        return $totalDays / $completedTasks->count();
    }

    private function calculateProductivityScore(Collection $allTasks, Collection $completedTasks): float
    {
        if ($allTasks->isEmpty()) return 0;
        
        $completionRate = $completedTasks->count() / $allTasks->count();
        $avgCompletionTime = $this->calculateAverageCompletionTime($completedTasks);
        
        // Score based on completion rate and speed
        $score = ($completionRate * 60) + (min(100, (7 - $avgCompletionTime) * 5.7));
        
        return max(0, min(100, $score));
    }

    private function calculateWorkloadBalance(Collection $tasks): string
    {
        $taskCount = $tasks->count();
        
        if ($taskCount === 0) return 'balanced';
        if ($taskCount > 10) return 'overloaded';
        if ($taskCount < 3) return 'underutilized';
        return 'balanced';
    }

    private function calculateBurnRate(Project $project): float
    {
        $budgets = $project->budgets;
        $totalSpent = $budgets->sum('spent');
        $projectDuration = $project->created_at->diffInDays(now());
        
        return $projectDuration > 0 ? $totalSpent / $projectDuration : 0;
    }

    private function projectCompletionCost(Project $project): float
    {
        $budgets = $project->budgets;
        $totalBudget = $budgets->sum('amount');
        $totalSpent = $budgets->sum('spent');
        $progress = $project->progress;
        
        if ($progress === 0) return $totalBudget;
        
        return ($totalSpent / $progress) * 100;
    }

    private function calculateBudgetVariance(Project $project): array
    {
        $budgets = $project->budgets;
        $totalBudget = $budgets->sum('amount');
        $totalSpent = $budgets->sum('spent');
        
        $variance = $totalBudget - $totalSpent;
        $variancePercentage = $totalBudget > 0 ? ($variance / $totalBudget) * 100 : 0;
        
        return [
            'amount' => $variance,
            'percentage' => $variancePercentage,
            'status' => $variance >= 0 ? 'under_budget' : 'over_budget',
        ];
    }

    private function calculateCostEfficiency(Project $project): float
    {
        $budgets = $project->budgets;
        $totalBudget = $budgets->sum('amount');
        $totalSpent = $budgets->sum('spent');
        $progress = $project->progress;
        
        if ($totalBudget === 0 || $progress === 0) return 100;
        
        $expectedSpent = ($totalBudget * $progress) / 100;
        return ($expectedSpent / $totalSpent) * 100;
    }

    private function calculatePredictionConfidence(Project $project): float
    {
        $tasks = $project->tasks;
        $completedTasks = $tasks->where('status', 'completed');
        
        if ($completedTasks->count() < 5) return 30; // Low confidence with little data
        if ($completedTasks->count() < 10) return 60; // Medium confidence
        return 85; // High confidence with sufficient data
    }

    private function identifyTimelineRisks(Project $project): array
    {
        $risks = [];
        $tasks = $project->tasks;
        
        $overdueTasks = $tasks->where('due_date', '<', now())->where('status', '!=', 'completed');
        if ($overdueTasks->count() > 0) {
            $risks[] = 'Overdue tasks may delay project completion';
        }
        
        $unassignedTasks = $tasks->where('assigned_to', null);
        if ($unassignedTasks->count() > $tasks->count() * 0.3) {
            $risks[] = 'High percentage of unassigned tasks';
        }
        
        return $risks;
    }

    private function findSimilarProjects(Project $project): Collection
    {
        return Project::where('id', '!=', $project->id)
            ->where('category', $project->category)
            ->where('status', 'completed')
            ->take(5)
            ->get();
    }

    private function calculateSimilarity(Project $project1, Project $project2): float
    {
        $similarity = 0;
        
        // Category similarity
        if ($project1->category === $project2->category) $similarity += 30;
        
        // Budget similarity
        $budgetDiff = abs($project1->budget - $project2->budget);
        $budgetSimilarity = max(0, 100 - ($budgetDiff / max($project1->budget, $project2->budget) * 100));
        $similarity += $budgetSimilarity * 0.4;
        
        // Team size similarity
        $teamSizeDiff = abs($project1->teamMembers->count() - $project2->teamMembers->count());
        $teamSimilarity = max(0, 100 - ($teamSizeDiff * 20));
        $similarity += $teamSimilarity * 0.3;
        
        return min(100, $similarity);
    }

    private function compareHealth(Project $project1, Project $project2): array
    {
        $health1 = $this->calculateProjectHealth($project1);
        $health2 = $this->calculateProjectHealth($project2);
        
        return [
            'current_score' => $health1['overall_score'],
            'comparison_score' => $health2['overall_score'],
            'difference' => $health1['overall_score'] - $health2['overall_score'],
            'status' => $health1['overall_score'] > $health2['overall_score'] ? 'better' : 'worse',
        ];
    }

    private function comparePerformance(Project $project1, Project $project2): array
    {
        $perf1 = $this->calculatePerformanceMetrics($project1);
        $perf2 = $this->calculatePerformanceMetrics($project2);
        
        return [
            'current_velocity' => $perf1['velocity'],
            'comparison_velocity' => $perf2['velocity'],
            'current_efficiency' => $perf1['efficiency'],
            'comparison_efficiency' => $perf2['efficiency'],
        ];
    }

    private function extractLessons(Project $project): array
    {
        // Placeholder for lessons learned extraction
        return [
            'What went well' => [],
            'What could be improved' => [],
            'Key takeaways' => [],
        ];
    }

    private function getPreviousHealthScore(Project $project): ?float
    {
        // Placeholder for historical health data
        return null;
    }
}
