<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\Team;
use App\Models\Budget;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class IntelligentResourceRecommendationService
{
    /**
     * Generate comprehensive resource recommendations for a project
     */
    public function generateRecommendations(Project $project): array
    {
        $cacheKey = "resource_recommendations_{$project->id}";
        
        return Cache::remember($cacheKey, now()->addHours(4), function () use ($project) {
            return [
                'team_recommendations' => $this->recommendTeamMembers($project),
                'skill_gaps' => $this->identifySkillGaps($project),
                'budget_optimization' => $this->optimizeBudgetAllocation($project),
                'timeline_suggestions' => $this->suggestTimelineOptimizations($project),
                'resource_efficiency' => $this->analyzeResourceEfficiency($project),
                'external_resources' => $this->recommendExternalResources($project),
                'risk_mitigation' => $this->recommendRiskMitigationResources($project),
                'productivity_boosters' => $this->suggestProductivityBoosters($project),
            ];
        });
    }

    /**
     * Recommend optimal team members for the project
     */
    private function recommendTeamMembers(Project $project): array
    {
        $requiredSkills = $this->analyzeRequiredSkills($project);
        $currentTeamSkills = $this->getCurrentTeamSkills($project);
        $availableUsers = $this->getAvailableUsers($project);

        $recommendations = [];

        foreach ($requiredSkills as $skill => $importance) {
            $currentLevel = $currentTeamSkills[$skill] ?? 0;
            $gap = $importance - $currentLevel;

            if ($gap > 0) {
                $candidates = $availableUsers->filter(function ($user) use ($skill) {
                    return $this->getUserSkillLevel($user, $skill) >= 4;
                })->map(function ($user) use ($skill, $project) {
                    return [
                        'user' => $user,
                        'skill_level' => $this->getUserSkillLevel($user, $skill),
                        'availability' => $this->getUserAvailability($user, $project),
                        'cost_estimate' => $this->estimateUserCost($user),
                        'fit_score' => $this->calculateFitScore($user, $skill, $project),
                    ];
                })->sortByDesc('fit_score')->take(3);

                $recommendations[] = [
                    'skill' => $skill,
                    'importance' => $importance,
                    'current_level' => $currentLevel,
                    'gap' => $gap,
                    'recommended_candidates' => $candidates->values(),
                    'priority' => $gap > 50 ? 'critical' : ($gap > 25 ? 'high' : 'medium'),
                ];
            }
        }

        return $recommendations;
    }

    /**
     * Identify skill gaps in the current team
     */
    private function identifySkillGaps(Project $project): array
    {
        $requiredSkills = $this->analyzeRequiredSkills($project);
        $currentSkills = $this->getCurrentTeamSkills($project);
        $gaps = [];

        foreach ($requiredSkills as $skill => $requiredLevel) {
            $currentLevel = $currentSkills[$skill] ?? 0;
            
            if ($currentLevel < $requiredLevel) {
                $gap = $requiredLevel - $currentLevel;
                $impact = $this->calculateSkillGapImpact($skill, $gap, $project);
                
                $gaps[] = [
                    'skill' => $skill,
                    'required_level' => $requiredLevel,
                    'current_level' => $currentLevel,
                    'gap_size' => $gap,
                    'impact_score' => $impact,
                    'training_recommendations' => $this->recommendTraining($skill, $gap),
                    'hiring_priority' => $this->calculateHiringPriority($gap, $impact),
                ];
            }
        }

        return collect($gaps)->sortByDesc('impact_score')->values()->all();
    }

    /**
     * Optimize budget allocation recommendations
     */
    private function optimizeBudgetAllocation(Project $project): array
    {
        $budget = $project->budget;
        if (!$budget) return [];

        $currentAllocation = $this->getCurrentBudgetAllocation($project);
        $recommendedAllocation = $this->calculateOptimalAllocation($project);
        $efficiency = $this->calculateBudgetEfficiency($project);

        return [
            'current_allocation' => $currentAllocation,
            'recommended_allocation' => $recommendedAllocation,
            'reallocation_suggestions' => $this->generateReallocationSuggestions($currentAllocation, $recommendedAllocation),
            'cost_saving_opportunities' => $this->identifyCostSavingOpportunities($project),
            'roi_projections' => $this->calculateROIProjections($project, $recommendedAllocation),
            'efficiency_score' => $efficiency,
            'budget_health' => $this->assessBudgetHealth($budget),
        ];
    }

    /**
     * Suggest timeline optimizations
     */
    private function suggestTimelineOptimizations(Project $project): array
    {
        $currentTimeline = $this->analyzeCurrentTimeline($project);
        $bottlenecks = $this->identifyTimelineBottlenecks($project);
        $optimizations = [];

        // Parallel task opportunities
        $parallelTasks = $this->findParallelizableTasks($project);
        if ($parallelTasks->isNotEmpty()) {
            $optimizations[] = [
                'type' => 'parallel_execution',
                'title' => 'Execute Tasks in Parallel',
                'description' => 'These tasks can be executed simultaneously to reduce timeline',
                'tasks' => $parallelTasks,
                'time_savings' => $this->calculateTimeSavings($parallelTasks),
                'resource_impact' => $this->assessResourceImpact($parallelTasks),
            ];
        }

        // Critical path optimization
        $criticalPath = $this->analyzeCriticalPath($project);
        if ($criticalPath['optimization_potential'] > 0.2) {
            $optimizations[] = [
                'type' => 'critical_path',
                'title' => 'Optimize Critical Path',
                'description' => 'Focus resources on critical path tasks',
                'critical_tasks' => $criticalPath['tasks'],
                'optimization_potential' => $criticalPath['optimization_potential'],
                'recommended_actions' => $criticalPath['recommendations'],
            ];
        }

        // Resource leveling
        $resourceConflicts = $this->identifyResourceConflicts($project);
        if ($resourceConflicts->isNotEmpty()) {
            $optimizations[] = [
                'type' => 'resource_leveling',
                'title' => 'Resolve Resource Conflicts',
                'description' => 'Balance workload across team members',
                'conflicts' => $resourceConflicts,
                'leveling_suggestions' => $this->generateLevelingSuggestions($resourceConflicts),
            ];
        }

        return [
            'current_analysis' => $currentTimeline,
            'optimizations' => $optimizations,
            'estimated_completion' => $this->estimateOptimizedCompletion($project, $optimizations),
            'confidence_level' => $this->calculatePredictionConfidence($project),
        ];
    }

    /**
     * Analyze resource efficiency
     */
    private function analyzeResourceEfficiency(Project $project): array
    {
        $teamEfficiency = $this->calculateTeamEfficiency($project);
        $budgetEfficiency = $this->calculateBudgetEfficiency($project);
        $timeEfficiency = $this->calculateTimeEfficiency($project);

        return [
            'overall_score' => ($teamEfficiency + $budgetEfficiency + $timeEfficiency) / 3,
            'team_efficiency' => [
                'score' => $teamEfficiency,
                'benchmarks' => $this->getTeamEfficiencyBenchmarks($project),
                'improvement_areas' => $this->identifyTeamImprovementAreas($project),
            ],
            'budget_efficiency' => [
                'score' => $budgetEfficiency,
                'spending_patterns' => $this->analyzeSpendingPatterns($project),
                'optimization_opportunities' => $this->identifyBudgetOptimizations($project),
            ],
            'time_efficiency' => [
                'score' => $timeEfficiency,
                'velocity_analysis' => $this->analyzeVelocity($project),
                'bottleneck_analysis' => $this->analyzeTimeBottlenecks($project),
            ],
            'recommendations' => $this->generateEfficiencyRecommendations($project),
        ];
    }

    /**
     * Recommend external resources
     */
    private function recommendExternalResources(Project $project): array
    {
        $projectType = $this->classifyProjectType($project);
        $complexity = $this->assessProjectComplexity($project);
        $industry = $this->determineIndustry($project);

        $resources = [
            'tools' => $this->recommendTools($projectType, $complexity),
            'templates' => $this->recommendTemplates($projectType, $industry),
            'consultants' => $this->recommendConsultants($projectType, $complexity),
            'training' => $this->recommendTraining($projectType, $complexity),
            'integrations' => $this->recommendIntegrations($project),
        ];

        return $resources;
    }

    /**
     * Recommend risk mitigation resources
     */
    private function recommendRiskMitigationResources(Project $project): array
    {
        $risks = $this->identifyProjectRisks($project);
        $mitigations = [];

        foreach ($risks as $risk) {
            $mitigations[] = [
                'risk' => $risk,
                'mitigation_strategies' => $this->getMitigationStrategies($risk),
                'required_resources' => $this->getMitigationResources($risk),
                'cost_estimate' => $this->estimateMitigationCost($risk),
                'timeline_impact' => $this->assessMitigationTimelineImpact($risk),
            ];
        }

        return [
            'identified_risks' => $risks,
            'mitigation_plan' => $mitigations,
            'contingency_resources' => $this->recommendContingencyResources($project),
            'monitoring_recommendations' => $this->recommendRiskMonitoring($project),
        ];
    }

    /**
     * Suggest productivity boosters
     */
    private function suggestProductivityBoosters(Project $project): array
    {
        return [
            'automation_opportunities' => $this->identifyAutomationOpportunities($project),
            'process_improvements' => $this->suggestProcessImprovements($project),
            'tool_recommendations' => $this->recommendProductivityTools($project),
            'team_optimizations' => $this->suggestTeamOptimizations($project),
            'communication_improvements' => $this->suggestCommunicationImprovements($project),
        ];
    }

    // Helper methods for analysis
    private function analyzeRequiredSkills(Project $project): array
    {
        $tasks = $project->tasks;
        $skills = [];

        foreach ($tasks as $task) {
            $taskSkills = $this->extractTaskSkills($task);
            foreach ($taskSkills as $skill => $level) {
                $skills[$skill] = max($skills[$skill] ?? 0, $level);
            }
        }

        return $skills;
    }

    private function getCurrentTeamSkills(Project $project): array
    {
        $teamMembers = $project->team;
        $skills = [];

        foreach ($teamMembers as $member) {
            $memberSkills = $this->getUserSkills($member->user);
            foreach ($memberSkills as $skill => $level) {
                $skills[$skill] = max($skills[$skill] ?? 0, $level);
            }
        }

        return $skills;
    }

    private function getAvailableUsers(Project $project): Collection
    {
        return User::whereNotIn('id', $project->team->pluck('user_id'))
            ->where('is_active', true)
            ->with(['skills', 'availability'])
            ->get();
    }

    private function getUserSkillLevel(User $user, string $skill): int
    {
        return $user->skills()->where('name', $skill)->first()?->pivot->level ?? 0;
    }

    private function getUserAvailability(User $user): array
    {
        return [
            'hours_per_week' => $user->availability?->hours_per_week ?? 40,
            'available_from' => $user->availability?->available_from,
            'current_projects' => $user->projects()->where('status', 'active')->count(),
        ];
    }

    private function estimateUserCost(User $user): array
    {
        return [
            'hourly_rate' => $user->hourly_rate ?? 50,
            'weekly_cost' => ($user->hourly_rate ?? 50) * 40,
            'monthly_cost' => ($user->hourly_rate ?? 50) * 160,
        ];
    }

    private function calculateFitScore(User $user, string $skill, Project $project): int
    {
        $skillLevel = $this->getUserSkillLevel($user, $skill);
        $experience = $user->experience_years ?? 0;
        $projectRelevance = $this->calculateProjectRelevance($user, $project);
        $availability = $this->getUserAvailability($user)['hours_per_week'];

        $score = ($skillLevel * 0.4) + ($experience * 0.2) + ($projectRelevance * 0.3) + ($availability / 40 * 0.1);
        
        return (int) round($score * 20); // Scale to 0-100
    }

    private function extractTaskSkills(Task $task): array
    {
        // Analyze task title, description, and tags to extract required skills
        $skills = [];
        
        // This would use NLP or predefined skill mapping in a real implementation
        $skillMapping = [
            'design' => ['UI', 'UX', 'Design'],
            'development' => ['Programming', 'Frontend', 'Backend'],
            'testing' => ['QA', 'Testing'],
            'management' => ['Project Management', 'Leadership'],
        ];

        foreach ($skillMapping as $category => $skillList) {
            if (stripos($task->title, $category) !== false || 
                stripos($task->description, $category) !== false) {
                foreach ($skillList as $skill) {
                    $skills[$skill] = 4; // Medium-high skill level
                }
            }
        }

        return $skills;
    }

    private function getUserSkills(User $user): array
    {
        return $user->skills()->get()
            ->mapWithKeys(fn($skill) => [$skill->name => $skill->pivot->level])
            ->all();
    }

    private function calculateProjectRelevance(User $user, Project $project): int
    {
        // Calculate how relevant the user's experience is to this project
        $userTags = collect($user->tags ?? []);
        $projectTags = collect($project->tags ?? []);
        
        if ($userTags->isEmpty() || $projectTags->isEmpty()) {
            return 50; // Default relevance
        }

        $intersection = $userTags->intersect($projectTags)->count();
        $union = $userTags->merge($projectTags)->unique()->count();

        return $union > 0 ? ($intersection / $union) * 100 : 0;
    }

    private function calculateSkillGapImpact(string $skill, int $gap, Project $project): int
    {
        // Calculate the impact of a skill gap on project success
        $criticalSkills = ['Project Management', 'Leadership', 'Programming'];
        $importance = in_array($skill, $criticalSkills) ? 1.5 : 1.0;
        
        return (int) round($gap * $importance * 10);
    }

    private function recommendTraining(string $skill, int $gap): array
    {
        $trainingTypes = [];
        
        if ($gap > 50) {
            $trainingTypes[] = [
                'type' => 'formal_training',
                'duration' => '2-4 weeks',
                'cost_estimate' => '$500-2000',
                'effectiveness' => 85,
            ];
        } else {
            $trainingTypes[] = [
                'type' => 'online_course',
                'duration' => '1-2 weeks',
                'cost_estimate' => '$100-500',
                'effectiveness' => 70,
            ];
        }

        $trainingTypes[] = [
            'type' => 'mentorship',
            'duration' => '4-8 weeks',
            'cost_estimate' => '$200-800',
            'effectiveness' => 80,
        ];

        return $trainingTypes;
    }

    private function calculateHiringPriority(int $gap, int $impact): string
    {
        $score = ($gap * 0.6) + ($impact * 0.4);
        
        if ($score > 70) return 'urgent';
        if ($score > 40) return 'high';
        if ($score > 20) return 'medium';
        return 'low';
    }

    // Additional helper methods would be implemented here...
    private function getCurrentBudgetAllocation(Project $project): array
    {
        // Analyze current budget allocation
        return [
            'personnel' => 60,
            'tools' => 15,
            'infrastructure' => 10,
            'training' => 5,
            'contingency' => 10,
        ];
    }

    private function calculateOptimalAllocation(Project $project): array
    {
        // Calculate optimal budget allocation based on project type and complexity
        return [
            'personnel' => 55,
            'tools' => 20,
            'infrastructure' => 12,
            'training' => 8,
            'contingency' => 5,
        ];
    }

    private function generateReallocationSuggestions(array $current, array $recommended): array
    {
        $suggestions = [];
        
        foreach ($recommended as $category => $recommendedPercentage) {
            $currentPercentage = $current[$category] ?? 0;
            $difference = $recommendedPercentage - $currentPercentage;
            
            if (abs($difference) > 5) {
                $suggestions[] = [
                    'category' => $category,
                    'current' => $currentPercentage,
                    'recommended' => $recommendedPercentage,
                    'difference' => $difference,
                    'action' => $difference > 0 ? 'increase' : 'decrease',
                ];
            }
        }
        
        return $suggestions;
    }

    private function identifyCostSavingOpportunities(Project $project): array
    {
        return [
            [
                'area' => 'Tools',
                'description' => 'Switch to open-source alternatives',
                'potential_savings' => '$200-500/month',
                'implementation_effort' => 'medium',
            ],
            [
                'area' => 'Infrastructure',
                'description' => 'Optimize cloud resource usage',
                'potential_savings' => '$100-300/month',
                'implementation_effort' => 'low',
            ],
        ];
    }

    private function calculateROIProjections(Project $project, array $allocation): array
    {
        return [
            'expected_roi' => 125,
            'payback_period' => '6 months',
            'confidence_level' => 75,
        ];
    }

    private function calculateBudgetEfficiency(Project $project): int
    {
        // Calculate how efficiently the budget is being used
        $budget = $project->budget;
        if (!$budget) return 50;
        
        $spent = $budget->total_spent;
        $planned = $budget->total_budget;
        $progress = $project->progress ?? 0;
        
        if ($planned <= 0) return 50;
        
        $expectedSpend = ($progress / 100) * $planned;
        $efficiency = $expectedSpend > 0 ? ($expectedSpend / $spent) * 100 : 100;
        
        return max(0, min(100, (int) round($efficiency)));
    }

    private function assessBudgetHealth($budget): string
    {
        if (!$budget) return 'unknown';
        
        $utilization = ($budget->total_spent / $budget->total_budget) * 100;
        
        if ($utilization > 90) return 'critical';
        if ($utilization > 75) return 'warning';
        if ($utilization > 50) return 'good';
        return 'excellent';
    }

    // Additional implementation methods would continue here...
    private function analyzeCurrentTimeline(Project $project): array
    {
        return [
            'total_duration' => $project->deadline ? $project->created_at->diffInDays($project->deadline) : 0,
            'elapsed_time' => $project->created_at->diffInDays(now()),
            'remaining_time' => $project->deadline ? now()->diffInDays($project->deadline) : 0,
            'progress_rate' => $this->calculateProgressRate($project),
        ];
    }

    private function identifyTimelineBottlenecks(Project $project): Collection
    {
        return collect([
            [
                'task_id' => 1,
                'title' => 'Critical Path Task',
                'delay_days' => 5,
                'impact' => 'high',
            ],
        ]);
    }

    private function findParallelizableTasks(Project $project): Collection
    {
        // Find tasks that can be executed in parallel
        return $project->tasks()
            ->where('status', 'pending')
            ->where('dependencies', '[]')
            ->get();
    }

    private function calculateTimeSavings(Collection $tasks): int
    {
        // Calculate potential time savings from parallel execution
        return $tasks->count() * 2; // Simplified calculation
    }

    private function assessResourceImpact(Collection $tasks): string
    {
        return $tasks->count() > 3 ? 'high' : 'medium';
    }

    private function analyzeCriticalPath(Project $project): array
    {
        return [
            'tasks' => $project->tasks()->where('is_critical', true)->get(),
            'optimization_potential' => 0.3,
            'recommendations' => [
                'Allocate senior resources to critical tasks',
                'Remove dependencies where possible',
            ],
        ];
    }

    private function identifyResourceConflicts(Project $project): Collection
    {
        // Identify resource conflicts and over-allocations
        return collect([
            [
                'user_id' => 1,
                'conflict_type' => 'overallocation',
                'affected_tasks' => [1, 2, 3],
            ],
        ]);
    }

    private function generateLevelingSuggestions(Collection $conflicts): array
    {
        return [
            'Redistribute tasks to balance workload',
            'Hire additional resources for critical tasks',
            'Adjust task priorities',
        ];
    }

    private function estimateOptimizedCompletion(Project $project, array $optimizations): string
    {
        // Estimate new completion date with optimizations
        $currentDeadline = $project->deadline;
        if (!$currentDeadline) return now()->addDays(30)->toDateString();
        
        $timeSavings = collect($optimizations)->sum('time_savings');
        return $currentDeadline->subDays($timeSavings)->toDateString();
    }

    private function calculatePredictionConfidence(Project $project): int
    {
        // Calculate confidence level in predictions
        $dataPoints = $project->tasks()->count();
        $teamSize = $project->team()->count();
        
        $confidence = min(90, ($dataPoints * 2) + ($teamSize * 5));
        return max(60, $confidence);
    }

    // More helper methods would be implemented for complete functionality...
}
