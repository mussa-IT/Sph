<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Task;
use App\Models\Milestone;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class PredictiveMilestoneService
{
    /**
     * AI-powered milestone prediction and automation
     * Automatically suggests optimal milestones and adjusts them based on project progress
     */
    public function generatePredictiveMilestones(Project $project): array
    {
        $tasks = $project->tasks()->get();
        $existingMilestones = $project->milestones()->get();

        // Analyze project characteristics
        $projectComplexity = $this->assessProjectComplexity($project);
        $teamVelocity = $this->calculateTeamVelocity($project);
        $riskFactors = $this->identifyRiskFactors($project);

        // Generate suggested milestones
        $suggestedMilestones = $this->suggestMilestones($project, $tasks, $projectComplexity, $teamVelocity);

        // Compare with existing milestones
        $milestoneAnalysis = $this->analyzeExistingMilestones($existingMilestones, $suggestedMilestones);

        // Generate timeline predictions
        $timelinePredictions = $this->predictTimeline($project, $tasks, $teamVelocity);

        return [
            'project_id' => $project->id,
            'complexity' => $projectComplexity,
            'team_velocity' => $teamVelocity,
            'risk_factors' => $riskFactors,
            'suggested_milestones' => $suggestedMilestones,
            'existing_milestones' => $milestoneAnalysis,
            'timeline_predictions' => $timelinePredictions,
            'recommendations' => $this->generateMilestoneRecommendations($milestoneAnalysis, $riskFactors),
        ];
    }

    private function assessProjectComplexity(Project $project): array
    {
        $taskCount = $project->tasks()->count();
        $budgetCount = $project->budgets()->count();
        $memberCount = $project->team ? $project->team->members()->count() : 1;

        $complexityScore = ($taskCount * 0.4) + ($budgetCount * 0.3) + ($memberCount * 0.2);
        
        $complexityLevel = 'low';
        if ($complexityScore > 30) {
            $complexityLevel = 'medium';
        }
        if ($complexityScore > 60) {
            $complexityLevel = 'high';
        }

        return [
            'score' => $complexityScore,
            'level' => $complexityLevel,
            'factors' => [
                'tasks' => $taskCount,
                'budgets' => $budgetCount,
                'members' => $memberCount,
            ],
        ];
    }

    private function calculateTeamVelocity(Project $project): array
    {
        $completedTasks = $project->tasks()
            ->where('status', 'completed')
            ->get();

        if ($completedTasks->isEmpty()) {
            return [
                'tasks_per_week' => 0,
                'average_duration' => 0,
                'trend' => 'unknown',
            ];
        }

        // Calculate tasks completed per week
        $firstCompletion = $completedTasks->min('completed_at');
        $lastCompletion = $completedTasks->max('completed_at');
        
        if ($firstCompletion && $lastCompletion) {
            $weeks = max(1, $firstCompletion->diffInWeeks($lastCompletion));
            $tasksPerWeek = $completedTasks->count() / $weeks;
        } else {
            $tasksPerWeek = 0;
        }

        // Calculate average task duration
        $durations = $completedTasks->map(function ($task) {
            if ($task->completed_at && $task->created_at) {
                return $task->completed_at->diffInDays($task->created_at);
            }
            return 0;
        })->filter(fn($d) => $d > 0);

        $averageDuration = $durations->avg() ?? 0;

        // Determine trend
        $recentTasks = $completedTasks->filter(fn($t) => $t->completed_at && $t->completed_at->gt(now()->subWeeks(2)));
        $olderTasks = $completedTasks->filter(fn($t) => $t->completed_at && $t->completed_at->lt(now()->subWeeks(2)));

        $trend = 'stable';
        if ($recentTasks->count() > $olderTasks->count() * 1.2) {
            $trend = 'accelerating';
        } elseif ($recentTasks->count() < $olderTasks->count() * 0.8) {
            $trend = 'decelerating';
        }

        return [
            'tasks_per_week' => round($tasksPerWeek, 2),
            'average_duration' => round($averageDuration, 1),
            'trend' => $trend,
        ];
    }

    private function identifyRiskFactors(Project $project): array
    {
        $risks = [];

        // Check for overdue tasks
        $overdueTasks = $project->tasks()
            ->where('status', '!=', 'completed')
            ->where('due_at', '<', now())
            ->count();

        if ($overdueTasks > 0) {
            $risks[] = [
                'type' => 'schedule',
                'severity' => $overdueTasks > 3 ? 'high' : 'medium',
                'description' => "{$overdueTasks} tasks are overdue",
                'impact' => 'milestone delays',
            ];
        }

        // Check for budget overruns
        $budgets = $project->budgets()->get();
        foreach ($budgets as $budget) {
            if ($budget->spent > $budget->amount) {
                $risks[] = [
                    'type' => 'budget',
                    'severity' => 'high',
                    'description' => "Budget exceeded by " . $this->formatCurrency($budget->spent - $budget->amount),
                    'impact' => 'resource constraints',
                ];
            }
        }

        // Check for dependency bottlenecks
        $blockedTasks = $project->tasks()
            ->where('status', '!=', 'completed')
            ->whereHas('dependencies', fn($q) => $q->where('status', '!=', 'completed'))
            ->count();

        if ($blockedTasks > 2) {
            $risks[] = [
                'type' => 'dependency',
                'severity' => 'medium',
                'description' => "{$blockedTasks} tasks are blocked by dependencies",
                'impact' => 'schedule impact',
            ];
        }

        return $risks;
    }

    private function suggestMilestones(Project $project, Collection $tasks, array $complexity, array $velocity): array
    {
        $milestones = [];
        $taskCount = $tasks->count();

        if ($taskCount === 0) {
            return $milestones;
        }

        // Determine number of milestones based on complexity
        $milestoneCount = match($complexity['level']) {
            'low' => 3,
            'medium' => 5,
            'high' => 7,
            default => 4,
        };

        $tasksPerMilestone = ceil($taskCount / $milestoneCount);
        $startDate = $project->created_at ?? now();
        
        // Calculate milestone dates based on team velocity
        $daysPerMilestone = $velocity['tasks_per_week'] > 0 
            ? ceil(($tasksPerMilestone / $velocity['tasks_per_week']) * 7)
            : 14; // Default to 2 weeks if velocity unknown

        for ($i = 1; $i <= $milestoneCount; $i++) {
            $milestoneDate = $startDate->copy()->addDays($i * $daysPerMilestone);
            $taskStart = ($i - 1) * $tasksPerMilestone;
            $taskEnd = min($i * $tasksPerMilestone, $taskCount);

            $milestoneTasks = $tasks->slice($taskStart, $taskEnd - $taskStart);

            $milestones[] = [
                'name' => $this->generateMilestoneName($i, $milestoneCount),
                'description' => $this->generateMilestoneDescription($milestoneTasks, $i),
                'target_date' => $milestoneDate->toDateString(),
                'estimated_tasks' => $milestoneTasks->count(),
                'confidence' => $this->calculateMilestoneConfidence($velocity, $i),
                'suggested_tasks' => $milestoneTasks->pluck('id')->toArray(),
            ];
        }

        return $milestones;
    }

    private function generateMilestoneName(int $index, int $total): string
    {
        $names = [
            'Project Kickoff',
            'Foundation Complete',
            'Core Development',
            'Feature Implementation',
            'Integration & Testing',
            'Quality Assurance',
            'Launch Preparation',
            'Project Completion',
        ];

        return $names[$index - 1] ?? "Milestone {$index}";
    }

    private function generateMilestoneDescription(Collection $tasks, int $index): string
    {
        $taskTypes = $tasks->pluck('category')->unique()->toArray();
        $typeString = implode(', ', array_slice($taskTypes, 0, 3));

        return "Complete {$tasks->count()} tasks related to {$typeString}";
    }

    private function calculateMilestoneConfidence(array $velocity, int $index): string
    {
        // Earlier milestones have higher confidence
        $baseConfidence = 100 - (($index - 1) * 10);
        
        // Adjust based on velocity trend
        if ($velocity['trend'] === 'accelerating') {
            $baseConfidence += 10;
        } elseif ($velocity['trend'] === 'decelerating') {
            $baseConfidence -= 15;
        }

        return match(true) {
            $baseConfidence >= 80 => 'high',
            $baseConfidence >= 60 => 'medium',
            default => 'low',
        };
    }

    private function analyzeExistingMilestones(Collection $existing, array $suggested): array
    {
        $analysis = [];

        foreach ($suggested as $index => $suggestedMilestone) {
            $existingMilestone = $existing->get($index - 1);

            if ($existingMilestone) {
                $suggestedDate = Carbon::parse($suggestedMilestone['target_date']);
                $existingDate = $existingMilestone->target_date;

                $daysDifference = $suggestedDate->diffInDays($existingDate);
                
                $analysis[] = [
                    'suggested' => $suggestedMilestone,
                    'existing' => [
                        'name' => $existingMilestone->name,
                        'target_date' => $existingDate->toDateString(),
                    ],
                    'alignment' => abs($daysDifference) <= 7 ? 'aligned' : 'misaligned',
                    'days_difference' => $daysDifference,
                    'recommendation' => abs($daysDifference) > 14 ? 'Consider adjusting milestone date' : 'Date is reasonable',
                ];
            } else {
                $analysis[] = [
                    'suggested' => $suggestedMilestone,
                    'existing' => null,
                    'alignment' => 'new',
                    'recommendation' => 'Consider adding this milestone',
                ];
            }
        }

        return $analysis;
    }

    private function predictTimeline(Project $project, Collection $tasks, array $velocity): array
    {
        $remainingTasks = $tasks->where('status', '!=', 'completed')->count();
        
        if ($remainingTasks === 0) {
            return [
                'estimated_completion' => now()->toDateString(),
                'confidence' => 'high',
                'factors' => [],
            ];
        }

        $tasksPerWeek = $velocity['tasks_per_week'] > 0 ? $velocity['tasks_per_week'] : 2;
        $weeksRemaining = ceil($remainingTasks / $tasksPerWeek);
        $estimatedCompletion = now()->addWeeks($weeksRemaining);

        $factors = [];
        if ($velocity['trend'] === 'accelerating') {
            $factors[] = 'Team velocity is improving';
            $estimatedCompletion = $estimatedCompletion->subDays(7);
        } elseif ($velocity['trend'] === 'decelerating') {
            $factors[] = 'Team velocity is declining';
            $estimatedCompletion = $estimatedCompletion->addDays(14);
        }

        return [
            'estimated_completion' => $estimatedCompletion->toDateString(),
            'confidence' => $velocity['tasks_per_week'] > 0 ? 'medium' : 'low',
            'factors' => $factors,
            'remaining_tasks' => $remainingTasks,
            'estimated_weeks' => $weeksRemaining,
        ];
    }

    private function generateMilestoneRecommendations(array $milestoneAnalysis, array $riskFactors): array
    {
        $recommendations = [];

        // Check for misaligned milestones
        $misaligned = collect($milestoneAnalysis)->filter(fn($m) => $m['alignment'] === 'misaligned');
        if ($misaligned->count() > 0) {
            $recommendations[] = [
                'type' => 'milestone_adjustment',
                'priority' => 'medium',
                'message' => "{$misaligned->count()} milestones are significantly misaligned with predictions",
                'action' => 'Review and adjust milestone dates based on team velocity',
            ];
        }

        // Check for high-severity risks
        $highRisks = collect($riskFactors)->filter(fn($r) => $r['severity'] === 'high');
        if ($highRisks->count() > 0) {
            $recommendations[] = [
                'type' => 'risk_mitigation',
                'priority' => 'high',
                'message' => "{$highRisks->count()} high-severity risks detected",
                'action' => 'Address risks before they impact milestone delivery',
            ];
        }

        return $recommendations;
    }

    private function formatCurrency(float $amount): string
    {
        return '$' . number_format($amount, 2);
    }
}
