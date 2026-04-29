<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class CrossProjectLearningService
{
    /**
     * Cross-project learning system that analyzes historical project data
     * to provide intelligent recommendations and insights for new projects
     */
    public function analyzeAndRecommend(Project $project): array
    {
        // Find similar historical projects
        $similarProjects = $this->findSimilarProjects($project);
        
        // Extract patterns from similar projects
        $patterns = $this->extractPatterns($similarProjects);
        
        // Generate recommendations based on patterns
        $recommendations = $this->generateRecommendations($project, $patterns);
        
        // Predict project outcomes
        $predictions = $this->predictOutcomes($project, $patterns);
        
        // Identify success factors
        $successFactors = $this->identifySuccessFactors($similarProjects);
        
        // Learn from failures
        $failurePatterns = $this->analyzeFailurePatterns($similarProjects);

        return [
            'project_id' => $project->id,
            'similar_projects_count' => count($similarProjects),
            'similar_projects' => $similarProjects->take(5)->map(fn($p) => [
                'id' => $p->id,
                'title' => $p->title,
                'status' => $p->status,
                'similarity_score' => $this->calculateSimilarity($project, $p),
            ])->toArray(),
            'patterns' => $patterns,
            'recommendations' => $recommendations,
            'predictions' => $predictions,
            'success_factors' => $successFactors,
            'failure_patterns' => $failurePatterns,
            'actionable_insights' => $this->generateActionableInsights($recommendations, $predictions),
        ];
    }

    private function findSimilarProjects(Project $project): Collection
    {
        $cacheKey = "similar_projects_{$project->id}";
        
        return Cache::remember($cacheKey, 3600, function () use ($project) {
            $allProjects = Project::where('id', '!=', $project->id)
                ->where('status', 'completed')
                ->with(['tasks', 'budgets'])
                ->get();

            $similarProjects = $allProjects->map(function ($p) use ($project) {
                $similarity = $this->calculateSimilarity($project, $p);
                return [
                    'project' => $p,
                    'similarity' => $similarity,
                ];
            })->filter(fn($item) => $item['similarity'] > 0.3)
              ->sortByDesc('similarity')
              ->take(10)
              ->pluck('project');

            return $similarProjects;
        });
    }

    private function calculateSimilarity(Project $project1, Project $project2): float
    {
        $score = 0;

        // Title similarity
        $titleSimilarity = similar_text(
            strtolower($project1->title),
            strtolower($project2->title),
            $percent
        ) ? $percent / 100 : 0;
        $score += $titleSimilarity * 0.2;

        // Task count similarity
        $taskCount1 = $project1->tasks()->count();
        $taskCount2 = $project2->tasks()->count();
        $taskSimilarity = 1 - min(1, abs($taskCount1 - $taskCount2) / max($taskCount1, $taskCount2, 1));
        $score += $taskSimilarity * 0.3;

        // Budget similarity
        $budget1 = $project1->budgets()->sum('amount');
        $budget2 = $project2->budgets()->sum('amount');
        if ($budget1 > 0 && $budget2 > 0) {
            $budgetSimilarity = 1 - min(1, abs($budget1 - $budget2) / max($budget1, $budget2));
            $score += $budgetSimilarity * 0.2;
        }

        // Team size similarity
        $teamSize1 = $project1->team ? $project1->team->members()->count() : 1;
        $teamSize2 = $project2->team ? $project2->team->members()->count() : 1;
        $teamSimilarity = 1 - min(1, abs($teamSize1 - $teamSize2) / max($teamSize1, $teamSize2, 1));
        $score += $teamSimilarity * 0.15;

        // Category similarity (if available)
        if ($project1->category && $project2->category) {
            $categoryMatch = $project1->category === $project2->category ? 1 : 0;
            $score += $categoryMatch * 0.15;
        }

        return min(1, $score);
    }

    private function extractPatterns(Collection $similarProjects): array
    {
        if ($similarProjects->isEmpty()) {
            return [];
        }

        $patterns = [];

        // Task duration patterns
        $taskDurations = [];
        foreach ($similarProjects as $project) {
            foreach ($project->tasks as $task) {
                if ($task->completed_at && $task->created_at) {
                    $duration = $task->completed_at->diffInDays($task->created_at);
                    $taskDurations[] = $duration;
                }
            }
        }

        if (!empty($taskDurations)) {
            $patterns['average_task_duration'] = array_sum($taskDurations) / count($taskDurations);
            $patterns['task_duration_range'] = [
                'min' => min($taskDurations),
                'max' => max($taskDurations),
            ];
        }

        // Budget utilization patterns
        $budgetUtilizations = [];
        foreach ($similarProjects as $project) {
            $totalBudget = $project->budgets()->sum('amount');
            $totalSpent = $project->budgets()->sum('spent');
            if ($totalBudget > 0) {
                $budgetUtilizations[] = ($totalSpent / $totalBudget) * 100;
            }
        }

        if (!empty($budgetUtilizations)) {
            $patterns['average_budget_utilization'] = array_sum($budgetUtilizations) / count($budgetUtilizations);
        }

        // Success rate patterns
        $successfulProjects = $similarProjects->where('status', 'completed')->count();
        $patterns['success_rate'] = ($successfulProjects / $similarProjects->count()) * 100;

        // Common task categories
        $taskCategories = [];
        foreach ($similarProjects as $project) {
            foreach ($project->tasks as $task) {
                if ($task->category) {
                    $taskCategories[] = $task->category;
                }
            }
        }

        if (!empty($taskCategories)) {
            $categoryCounts = array_count_values($taskCategories);
            arsort($categoryCounts);
            $patterns['common_task_categories'] = array_slice($categoryCounts, 0, 5, true);
        }

        // Typical team composition
        $teamSizes = [];
        foreach ($similarProjects as $project) {
            if ($project->team) {
                $teamSizes[] = $project->team->members()->count();
            }
        }

        if (!empty($teamSizes)) {
            $patterns['average_team_size'] = array_sum($teamSizes) / count($teamSizes);
        }

        return $patterns;
    }

    private function generateRecommendations(Project $project, array $patterns): array
    {
        $recommendations = [];

        if (empty($patterns)) {
            return $recommendations;
        }

        // Task duration recommendations
        if (isset($patterns['average_task_duration'])) {
            $currentTasks = $project->tasks()->get();
            $currentAvgDuration = 0;
            
            $completedTasks = $currentTasks->where('status', 'completed');
            if (!$completedTasks->isEmpty()) {
                $durations = $completedTasks->map(fn($t) => 
                    $t->completed_at && $t->created_at 
                    ? $t->completed_at->diffInDays($t->created_at) 
                    : 0
                )->filter(fn($d) => $d > 0);
                
                if (!$durations->isEmpty()) {
                    $currentAvgDuration = $durations->avg();
                }
            }

            if ($currentAvgDuration > $patterns['average_task_duration'] * 1.5) {
                $recommendations[] = [
                    'type' => 'task_duration',
                    'priority' => 'high',
                    'message' => 'Tasks are taking longer than similar projects',
                    'recommendation' => 'Review task complexity and consider breaking down large tasks',
                    'benchmark' => round($patterns['average_task_duration'], 1) . ' days',
                    'current' => round($currentAvgDuration, 1) . ' days',
                ];
            }
        }

        // Budget recommendations
        if (isset($patterns['average_budget_utilization'])) {
            $currentBudget = $project->budgets()->sum('amount');
            $currentSpent = $project->budgets()->sum('spent');
            
            if ($currentBudget > 0) {
                $currentUtilization = ($currentSpent / $currentBudget) * 100;
                
                if ($currentUtilization > $patterns['average_budget_utilization'] * 1.2) {
                    $recommendations[] = [
                        'type' => 'budget',
                        'priority' => 'medium',
                        'message' => 'Budget utilization is higher than similar projects',
                        'recommendation' => 'Review spending patterns and consider cost optimization',
                        'benchmark' => round($patterns['average_budget_utilization'], 1) . '%',
                        'current' => round($currentUtilization, 1) . '%',
                    ];
                }
            }
        }

        // Team size recommendations
        if (isset($patterns['average_team_size'])) {
            $currentTeamSize = $project->team ? $project->team->members()->count() : 1;
            
            if ($currentTeamSize < $patterns['average_team_size'] * 0.7) {
                $recommendations[] = [
                    'type' => 'team',
                    'priority' => 'low',
                    'message' => 'Team size is smaller than similar projects',
                    'recommendation' => 'Consider if additional team members could accelerate progress',
                    'benchmark' => round($patterns['average_team_size'], 1),
                    'current' => $currentTeamSize,
                ];
            }
        }

        return $recommendations;
    }

    private function predictOutcomes(Project $project, array $patterns): array
    {
        $predictions = [];

        if (empty($patterns)) {
            return $predictions;
        }

        // Predict completion time
        if (isset($patterns['average_task_duration'])) {
            $remainingTasks = $project->tasks()->where('status', '!=', 'completed')->count();
            $estimatedDays = $remainingTasks * $patterns['average_task_duration'];
            $estimatedCompletion = now()->addDays($estimatedDays);
            
            $predictions[] = [
                'type' => 'completion_date',
                'prediction' => $estimatedCompletion->toDateString(),
                'confidence' => 'medium',
                'based_on' => 'Historical task duration patterns',
            ];
        }

        // Predict budget needs
        if (isset($patterns['average_budget_utilization'])) {
            $currentBudget = $project->budgets()->sum('amount');
            $projectedSpending = $currentBudget * ($patterns['average_budget_utilization'] / 100);
            
            $predictions[] = [
                'type' => 'final_budget',
                'prediction' => round($projectedSpending, 2),
                'confidence' => 'medium',
                'based_on' => 'Historical budget utilization patterns',
            ];
        }

        // Predict success probability
        if (isset($patterns['success_rate'])) {
            $predictions[] = [
                'type' => 'success_probability',
                'prediction' => round($patterns['success_rate'], 1) . '%',
                'confidence' => 'low',
                'based_on' => 'Historical success rate of similar projects',
            ];
        }

        return $predictions;
    }

    private function identifySuccessFactors(Collection $similarProjects): array
    {
        $successfulProjects = $similarProjects->where('status', 'completed');
        
        if ($successfulProjects->isEmpty()) {
            return [];
        }

        $factors = [];

        // Analyze successful project characteristics
        $avgTeamSize = 0;
        $avgTaskDuration = 0;
        $avgBudgetUtilization = 0;

        foreach ($successfulProjects as $project) {
            $avgTeamSize += $project->team ? $project->team->members()->count() : 1;
            
            $tasks = $project->tasks()->where('status', 'completed')->get();
            if (!$tasks->isEmpty()) {
                $durations = $tasks->map(fn($t) => 
                    $t->completed_at && $t->created_at 
                    ? $t->completed_at->diffInDays($t->created_at) 
                    : 0
                )->filter(fn($d) => $d > 0);
                if (!$durations->isEmpty()) {
                    $avgTaskDuration += $durations->avg();
                }
            }

            $totalBudget = $project->budgets()->sum('amount');
            $totalSpent = $project->budgets()->sum('spent');
            if ($totalBudget > 0) {
                $avgBudgetUtilization += ($totalSpent / $totalBudget) * 100;
            }
        }

        $count = $successfulProjects->count();
        $avgTeamSize /= $count;
        $avgTaskDuration /= $count;
        $avgBudgetUtilization /= $count;

        $factors[] = [
            'factor' => 'optimal_team_size',
            'value' => round($avgTeamSize, 1),
            'description' => 'Successful projects typically have this team size',
        ];

        $factors[] = [
            'factor' => 'efficient_task_duration',
            'value' => round($avgTaskDuration, 1) . ' days',
            'description' => 'Average task duration in successful projects',
        ];

        $factors[] = [
            'factor' => 'budget_efficiency',
            'value' => round($avgBudgetUtilization, 1) . '%',
            'description' => 'Budget utilization rate in successful projects',
        ];

        return $factors;
    }

    private function analyzeFailurePatterns(Collection $similarProjects): array
    {
        $failedProjects = $similarProjects->where('status', '!=', 'completed');
        
        if ($failedProjects->isEmpty()) {
            return [];
        }

        $patterns = [];

        // Analyze common failure indicators
        $overdueTasks = 0;
        $overBudgetProjects = 0;
        $smallTeams = 0;

        foreach ($failedProjects as $project) {
            $overdueCount = $project->tasks()
                ->where('status', '!=', 'completed')
                ->where('due_at', '<', now())
                ->count();
            
            if ($overdueCount > 0) {
                $overdueTasks++;
            }

            $totalBudget = $project->budgets()->sum('amount');
            $totalSpent = $project->budgets()->sum('spent');
            if ($totalBudget > 0 && $totalSpent > $totalBudget) {
                $overBudgetProjects++;
            }

            $teamSize = $project->team ? $project->team->members()->count() : 1;
            if ($teamSize < 3) {
                $smallTeams++;
            }
        }

        $total = $failedProjects->count();

        if ($overdueTasks > 0) {
            $patterns[] = [
                'pattern' => 'schedule_issues',
                'frequency' => round(($overdueTasks / $total) * 100, 1) . '%',
                'description' => 'Projects with overdue tasks tend to struggle',
                'mitigation' => 'Implement proactive schedule monitoring',
            ];
        }

        if ($overBudgetProjects > 0) {
            $patterns[] = [
                'pattern' => 'budget_overruns',
                'frequency' => round(($overBudgetProjects / $total) * 100, 1) . '%',
                'description' => 'Budget overruns are common in struggling projects',
                'mitigation' => 'Implement regular budget reviews',
            ];
        }

        if ($smallTeams > 0) {
            $patterns[] = [
                'pattern' => 'insufficient_team',
                'frequency' => round(($smallTeams / $total) * 100, 1) . '%',
                'description' => 'Small teams may lack capacity for complex projects',
                'mitigation' => 'Ensure adequate team size for project complexity',
            ];
        }

        return $patterns;
    }

    private function generateActionableInsights(array $recommendations, array $predictions): array
    {
        $insights = [];

        // Combine recommendations and predictions into actionable insights
        foreach ($recommendations as $rec) {
            $insights[] = [
                'type' => 'recommendation',
                'priority' => $rec['priority'],
                'action' => $rec['recommendation'],
                'expected_impact' => $this->estimateImpact($rec['type']),
            ];
        }

        foreach ($predictions as $pred) {
            $insights[] = [
                'type' => 'prediction',
                'confidence' => $pred['confidence'],
                'insight' => "Based on historical data, project is predicted to {$pred['type']}: {$pred['prediction']}",
                'monitoring_required' => true,
            ];
        }

        return $insights;
    }

    private function estimateImpact(string $type): string
    {
        $impacts = [
            'task_duration' => 'high',
            'budget' => 'medium',
            'team' => 'medium',
        ];

        return $impacts[$type] ?? 'medium';
    }
}
