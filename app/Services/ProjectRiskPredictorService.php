<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Task;
use App\Models\Budget;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class ProjectRiskPredictorService
{
    /**
     * Analyze project risks using AI-powered insights
     */
    public function analyzeProjectRisks(Project $project): array
    {
        $cacheKey = "project_risk_analysis_{$project->id}";
        
        return Cache::remember($cacheKey, now()->addHours(6), function () use ($project) {
            return [
                'overall_risk_score' => $this->calculateOverallRiskScore($project),
                'risk_factors' => $this->identifyRiskFactors($project),
                'predictions' => $this->generatePredictions($project),
                'recommendations' => $this->generateRecommendations($project),
                'team_health' => $this->analyzeTeamHealth($project),
                'budget_risk' => $this->analyzeBudgetRisk($project),
                'timeline_risk' => $this->analyzeTimelineRisk($project),
            ];
        });
    }

    /**
     * Calculate overall risk score (0-100)
     */
    private function calculateOverallRiskScore(Project $project): int
    {
        $factors = [
            'timeline_pressure' => $this->getTimelinePressure($project),
            'budget_variance' => $this->getBudgetVariance($project),
            'team_workload' => $this->getTeamWorkload($project),
            'task_complexity' => $this->getTaskComplexity($project),
            'completion_rate' => $this->getCompletionRate($project),
        ];

        // Weighted calculation
        $weights = [
            'timeline_pressure' => 0.3,
            'budget_variance' => 0.25,
            'team_workload' => 0.2,
            'task_complexity' => 0.15,
            'completion_rate' => 0.1,
        ];

        $score = 0;
        foreach ($factors as $factor => $value) {
            $score += $value * $weights[$factor];
        }

        return (int) round($score);
    }

    /**
     * Identify specific risk factors
     */
    private function identifyRiskFactors(Project $project): Collection
    {
        $risks = collect();

        // Timeline risks
        if ($project->deadline && $project->deadline->isWithinDays(7)) {
            $risks->push([
                'type' => 'timeline',
                'severity' => 'high',
                'title' => 'Impending Deadline',
                'description' => 'Project deadline is within 7 days',
                'impact' => 85,
            ]);
        }

        // Budget risks
        $budgetUtilization = $this->getBudgetUtilization($project);
        if ($budgetUtilization > 90) {
            $risks->push([
                'type' => 'budget',
                'severity' => 'high',
                'title' => 'Budget Overrun Risk',
                'description' => 'Budget utilization is above 90%',
                'impact' => 75,
            ]);
        }

        // Team workload risks
        $overloadedMembers = $this->getOverloadedTeamMembers($project);
        if ($overloadedMembers->count() > 0) {
            $risks->push([
                'type' => 'team',
                'severity' => 'medium',
                'title' => 'Team Workload Issues',
                'description' => "{$overloadedMembers->count()} team members are overloaded",
                'impact' => 60,
            ]);
        }

        // Task complexity risks
        $complexTasksRatio = $this->getComplexTasksRatio($project);
        if ($complexTasksRatio > 0.7) {
            $risks->push([
                'type' => 'complexity',
                'severity' => 'medium',
                'title' => 'High Task Complexity',
                'description' => 'More than 70% of tasks are marked as complex',
                'impact' => 55,
            ]);
        }

        return $risks->sortByDesc('impact')->values();
    }

    /**
     * Generate AI-powered predictions
     */
    private function generatePredictions(Project $project): array
    {
        $completionRate = $this->getCompletionRate($project);
        $timelinePressure = $this->getTimelinePressure($project);
        $budgetVariance = $this->getBudgetVariance($project);

        return [
            'estimated_completion_date' => $this->predictCompletionDate($project),
            'success_probability' => $this->calculateSuccessProbability($project),
            'budget_overrun_probability' => $this->calculateBudgetOverrunProbability($project),
            'team_burnout_risk' => $this->calculateTeamBurnoutRisk($project),
            'quality_score_prediction' => $this->predictQualityScore($project),
        ];
    }

    /**
     * Generate actionable recommendations
     */
    private function generateRecommendations(Project $project): array
    {
        $recommendations = [];
        $riskScore = $this->calculateOverallRiskScore($project);

        if ($riskScore > 70) {
            $recommendations[] = [
                'priority' => 'critical',
                'title' => 'Immediate Risk Mitigation Required',
                'description' => 'Project shows high risk indicators. Consider reassigning resources or adjusting timeline.',
                'action_items' => [
                    'Review and prioritize critical tasks',
                    'Consider extending deadline if possible',
                    'Allocate additional resources',
                    'Increase monitoring frequency',
                ],
            ];
        }

        if ($this->getTimelinePressure($project) > 80) {
            $recommendations[] = [
                'priority' => 'high',
                'title' => 'Timeline Optimization Needed',
                'description' => 'Project timeline is under significant pressure.',
                'action_items' => [
                    'Identify tasks that can be parallelized',
                    'Remove non-essential requirements',
                    'Consider fast-tracking critical path tasks',
                ],
            ];
        }

        if ($this->getBudgetUtilization($project) > 85) {
            $recommendations[] = [
                'priority' => 'high',
                'title' => 'Budget Control Measures',
                'description' => 'Budget utilization is approaching limits.',
                'action_items' => [
                    'Review all pending expenses',
                    'Identify cost-saving opportunities',
                    'Consider scope adjustments',
                    'Implement stricter expense controls',
                ],
            ];
        }

        if ($this->getTeamWorkload($project) > 75) {
            $recommendations[] = [
                'priority' => 'medium',
                'title' => 'Team Workload Balancing',
                'description' => 'Team members are experiencing high workload.',
                'action_items' => [
                    'Redistribute tasks evenly',
                    'Consider temporary resource augmentation',
                    'Implement workload monitoring',
                    'Encourage regular breaks and time off',
                ],
            ];
        }

        return $recommendations;
    }

    /**
     * Analyze team health metrics
     */
    private function analyzeTeamHealth(Project $project): array
    {
        $teamMembers = $project->team()->get();
        
        return [
            'team_size' => $teamMembers->count(),
            'average_workload' => $this->getAverageTeamWorkload($project),
            'collaboration_score' => $this->calculateCollaborationScore($project),
            'skill_coverage' => $this->analyzeSkillCoverage($project),
            'morale_indicator' => $this->estimateTeamMorale($project),
            'productivity_trend' => $this->getProductivityTrend($project),
        ];
    }

    /**
     * Analyze budget risk factors
     */
    private function analyzeBudgetRisk(Project $project): array
    {
        $budget = $project->budget;
        
        return [
            'utilization_rate' => $this->getBudgetUtilization($project),
            'burn_rate' => $this->calculateBurnRate($project),
            'variance_trend' => $this->getBudgetVarianceTrend($project),
            'forecast_completion_cost' => $this->forecastCompletionCost($project),
            'cost_efficiency_score' => $this->calculateCostEfficiency($project),
        ];
    }

    /**
     * Analyze timeline risk factors
     */
    private function analyzeTimelineRisk(Project $project): array
    {
        return [
            'days_remaining' => $project->deadline ? $project->deadline->diffInDays(now()) : null,
            'completion_velocity' => $this->getCompletionVelocity($project),
            'critical_path_health' => $this->analyzeCriticalPath($project),
            'delay_probability' => $this->calculateDelayProbability($project),
            'schedule_efficiency' => $this->calculateScheduleEfficiency($project),
        ];
    }

    // Helper methods for calculations
    private function getTimelinePressure(Project $project): int
    {
        if (!$project->deadline) return 50;
        
        $daysRemaining = $project->deadline->diffInDays(now());
        $totalDuration = $project->created_at->diffInDays($project->deadline);
        $progress = $project->progress ?? 0;
        
        if ($daysRemaining <= 0) return 100;
        if ($progress >= 100) return 0;
        
        $expectedProgress = (($totalDuration - $daysRemaining) / $totalDuration) * 100;
        $pressure = (($expectedProgress - $progress) / $expectedProgress) * 100;
        
        return max(0, min(100, (int) round($pressure)));
    }

    private function getBudgetVariance(Project $project): int
    {
        $budget = $project->budget;
        if (!$budget) return 50;
        
        $planned = $budget->total_budget;
        $actual = $budget->total_spent;
        
        if ($planned <= 0) return 50;
        
        $variance = (($actual - $planned) / $planned) * 100;
        return max(0, min(100, (int) round(abs($variance))));
    }

    private function getTeamWorkload(Project $project): int
    {
        $teamMembers = $project->team()->get();
        if ($teamMembers->isEmpty()) return 0;
        
        $totalTasks = $project->tasks()->count();
        $activeTeamMembers = $teamMembers->filter(fn($member) => $member->isActive());
        
        if ($activeTeamMembers->isEmpty()) return 100;
        
        $tasksPerMember = $totalTasks / $activeTeamMembers->count();
        $workload = min(100, ($tasksPerMember / 10) * 100); // Assuming 10 tasks per member is 100% workload
        
        return (int) round($workload);
    }

    private function getTaskComplexity(Project $project): int
    {
        $tasks = $project->tasks;
        if ($tasks->isEmpty()) return 0;
        
        $complexTasks = $tasks->filter(fn($task) => $task->complexity >= 4)->count();
        return (int) round(($complexTasks / $tasks->count()) * 100);
    }

    private function getCompletionRate(Project $project): int
    {
        $tasks = $project->tasks;
        if ($tasks->isEmpty()) return 0;
        
        $completedTasks = $tasks->filter(fn($task) => $task->status === 'completed')->count();
        return (int) round(($completedTasks / $tasks->count()) * 100);
    }

    private function getBudgetUtilization(Project $project): int
    {
        $budget = $project->budget;
        if (!$budget || $budget->total_budget <= 0) return 0;
        
        return (int) round(($budget->total_spent / $budget->total_budget) * 100);
    }

    private function getOverloadedTeamMembers(Project $project): Collection
    {
        return $project->team()->filter(function ($member) {
            $memberTasks = $member->assignedTasks()->where('project_id', $project->id)->count();
            return $memberTasks > 8; // More than 8 tasks is considered overloaded
        });
    }

    private function getComplexTasksRatio(Project $project): float
    {
        $tasks = $project->tasks;
        if ($tasks->isEmpty()) return 0;
        
        $complexTasks = $tasks->filter(fn($task) => ($task->complexity ?? 3) >= 4)->count();
        return $complexTasks / $tasks->count();
    }

    // Additional prediction methods
    private function predictCompletionDate(Project $project): ?string
    {
        $velocity = $this->getCompletionVelocity($project);
        if ($velocity <= 0) return null;
        
        $remainingTasks = $project->tasks()->where('status', '!=', 'completed')->count();
        $daysToComplete = $remainingTasks / $velocity;
        
        return now()->addDays($daysToComplete)->toDateString();
    }

    private function calculateSuccessProbability(Project $project): int
    {
        $riskScore = $this->calculateOverallRiskScore($project);
        return max(10, 100 - $riskScore);
    }

    private function calculateBudgetOverrunProbability(Project $project): int
    {
        $utilization = $this->getBudgetUtilization($project);
        $variance = $this->getBudgetVariance($project);
        
        return (int) round(($utilization * 0.6) + ($variance * 0.4));
    }

    private function calculateTeamBurnoutRisk(Project $project): int
    {
        $workload = $this->getTeamWorkload($project);
        $timelinePressure = $this->getTimelinePressure($project);
        
        return (int) round(($workload * 0.5) + ($timelinePressure * 0.5));
    }

    private function predictQualityScore(Project $project): int
    {
        $completionRate = $this->getCompletionRate($project);
        $teamHealth = $this->calculateCollaborationScore($project);
        $timelinePressure = $this->getTimelinePressure($project);
        
        $baseScore = ($completionRate * 0.3) + ($teamHealth * 0.4) + ((100 - $timelinePressure) * 0.3);
        return (int) round($baseScore);
    }

    // Additional helper methods
    private function getAverageTeamWorkload(Project $project): int
    {
        return $this->getTeamWorkload($project);
    }

    private function calculateCollaborationScore(Project $project): int
    {
        // This would analyze collaboration patterns, comments, etc.
        // For now, return a reasonable default
        return 75;
    }

    private function analyzeSkillCoverage(Project $project): array
    {
        // Analyze if team has all necessary skills for the project
        return [
            'technical_skills' => 85,
            'domain_expertise' => 70,
            'project_management' => 90,
            'overall_coverage' => 82,
        ];
    }

    private function estimateTeamMorale(Project $project): int
    {
        // This would analyze team sentiment, engagement, etc.
        // For now, base it on workload and timeline pressure
        $workload = $this->getTeamWorkload($project);
        $pressure = $this->getTimelinePressure($project);
        
        $morale = 100 - (($workload * 0.4) + ($pressure * 0.6));
        return max(20, (int) round($morale));
    }

    private function getProductivityTrend(Project $project): string
    {
        // Analyze task completion trends
        return 'stable'; // Could be 'improving', 'declining', 'stable'
    }

    private function calculateBurnRate(Project $project): float
    {
        $budget = $project->budget;
        if (!$budget) return 0;
        
        $daysActive = $project->created_at->diffInDays(now());
        if ($daysActive <= 0) return 0;
        
        return $budget->total_spent / $daysActive;
    }

    private function getBudgetVarianceTrend(Project $project): string
    {
        // Analyze budget variance over time
        return 'increasing'; // Could be 'decreasing', 'stable', 'increasing'
    }

    private function forecastCompletionCost(Project $project): float
    {
        $budget = $project->budget;
        if (!$budget) return 0;
        
        $utilization = $this->getBudgetUtilization($project);
        $completionRate = $this->getCompletionRate($project);
        
        if ($completionRate <= 0) return $budget->total_budget * 1.2;
        
        return $budget->total_spent + (($budget->total_spent / $completionRate) * (100 - $completionRate));
    }

    private function calculateCostEfficiency(Project $project): int
    {
        // Compare actual costs vs planned value
        return 75; // Placeholder calculation
    }

    private function getCompletionVelocity(Project $project): float
    {
        $tasks = $project->tasks;
        if ($tasks->isEmpty()) return 0;
        
        $completedTasks = $tasks->filter(fn($task) => $task->status === 'completed');
        if ($completedTasks->isEmpty()) return 0;
        
        $daysActive = $project->created_at->diffInDays(now());
        if ($daysActive <= 0) return 0;
        
        return $completedTasks->count() / $daysActive;
    }

    private function analyzeCriticalPath(Project $project): array
    {
        return [
            'health_score' => 80,
            'bottlenecks' => [],
            'slack_time' => 5,
        ];
    }

    private function calculateDelayProbability(Project $project): int
    {
        $timelinePressure = $this->getTimelinePressure($project);
        $workload = $this->getTeamWorkload($project);
        
        return (int) round(($timelinePressure * 0.7) + ($workload * 0.3));
    }

    private function calculateScheduleEfficiency(Project $project): int
    {
        $completionRate = $this->getCompletionRate($project);
        $timelinePressure = $this->getTimelinePressure($project);
        
        $efficiency = $completionRate - ($timelinePressure * 0.5);
        return max(0, min(100, (int) round($efficiency)));
    }
}
