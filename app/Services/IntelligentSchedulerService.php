<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use App\Models\Project;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class IntelligentSchedulerService
{
    public function optimizeTaskSchedule(Project $project, array $constraints = []): array
    {
        $tasks = $project->tasks->where('status', '!=', 'completed');
        $teamMembers = $project->teamMembers;
        
        $schedule = [
            'optimized_assignments' => $this->optimizeAssignments($tasks, $teamMembers, $constraints),
            'timeline_optimization' => $this->optimizeTimeline($tasks, $constraints),
            'resource_allocation' => $this->optimizeResources($tasks, $teamMembers),
            'dependency_resolution' => $this->resolveDependencies($tasks),
            'workload_balancing' => $this->balanceWorkload($teamMembers, $tasks),
            'critical_path_analysis' => $this->analyzeCriticalPath($tasks),
            'recommendations' => $this->generateScheduleRecommendations($project, $tasks),
        ];
        
        return $schedule;
    }

    public function optimizeAssignments(Collection $tasks, Collection $teamMembers, array $constraints): array
    {
        $assignments = [];
        
        foreach ($tasks as $task) {
            $bestCandidate = $this->findBestCandidate($task, $teamMembers, $constraints);
            
            if ($bestCandidate) {
                $assignments[] = [
                    'task_id' => $task->id,
                    'task_title' => $task->title,
                    'assigned_to' => $bestCandidate['user_id'],
                    'assigned_to_name' => $bestCandidate['name'],
                    'confidence_score' => $bestCandidate['confidence'],
                    'reasoning' => $bestCandidate['reasoning'],
                    'estimated_completion' => $bestCandidate['estimated_completion'],
                ];
            }
        }
        
        return $assignments;
    }

    public function optimizeTimeline(Collection $tasks, array $constraints): array
    {
        $timeline = [];
        $currentDate = now();
        
        // Sort tasks by priority and dependencies
        $sortedTasks = $this->sortTasksByPriority($tasks);
        
        foreach ($sortedTasks as $task) {
            $startDate = $this->calculateOptimalStartDate($task, $currentDate, $constraints);
            $duration = $this->estimateTaskDuration($task);
            $endDate = $startDate->copy()->addDays($duration);
            
            $timeline[] = [
                'task_id' => $task->id,
                'task_title' => $task->title,
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'duration' => $duration,
                'buffer_days' => $this->calculateBufferDays($task, $constraints),
                'confidence_level' => $this->calculateTimelineConfidence($task),
            ];
            
            $currentDate = $endDate;
        }
        
        return $timeline;
    }

    public function optimizeResources(Collection $tasks, Collection $teamMembers): array
    {
        $resourcePlan = [];
        
        // Calculate resource requirements
        $resourceRequirements = $this->calculateResourceRequirements($tasks);
        
        // Allocate resources based on availability and skills
        foreach ($teamMembers as $member) {
            $memberCapacity = $this->calculateMemberCapacity($member);
            $assignedTasks = $this->getAssignedTasks($member, $tasks);
            
            $resourcePlan[] = [
                'member_id' => $member->id,
                'member_name' => $member->name,
                'capacity' => $memberCapacity,
                'current_workload' => $assignedTasks->count(),
                'utilization_rate' => $this->calculateUtilizationRate($member, $tasks),
                'skill_match_score' => $this->calculateSkillMatch($member, $assignedTasks),
                'recommendations' => $this->generateResourceRecommendations($member, $tasks),
            ];
        }
        
        return $resourcePlan;
    }

    public function resolveDependencies(Collection $tasks): array
    {
        $dependencyGraph = $this->buildDependencyGraph($tasks);
        $resolutionPlan = [];
        
        foreach ($dependencyGraph as $taskId => $dependencies) {
            $resolutionPlan[] = [
                'task_id' => $taskId,
                'task_title' => $tasks->find($taskId)?->title,
                'dependencies' => $dependencies,
                'blocking_tasks' => $this->findBlockingTasks($taskId, $dependencyGraph),
                'parallel_tasks' => $this->findParallelTasks($taskId, $dependencyGraph),
                'optimization_suggestions' => $this->optimizeDependencies($taskId, $dependencies, $dependencyGraph),
            ];
        }
        
        return $resolutionPlan;
    }

    public function balanceWorkload(Collection $teamMembers, Collection $tasks): array
    {
        $currentWorkloads = [];
        $optimizedWorkloads = [];
        
        // Calculate current workloads
        foreach ($teamMembers as $member) {
            $memberTasks = $tasks->where('assigned_to', $member->id);
            $currentWorkloads[$member->id] = [
                'name' => $member->name,
                'task_count' => $memberTasks->count(),
                'estimated_hours' => $this->estimateTotalHours($memberTasks),
                'complexity_score' => $this->calculateComplexityScore($memberTasks),
                'stress_level' => $this->calculateStressLevel($member, $memberTasks),
            ];
        }
        
        // Optimize workloads
        $optimizedWorkloads = $this->redistributeTasks($teamMembers, $tasks, $currentWorkloads);
        
        return [
            'current_workloads' => $currentWorkloads,
            'optimized_workloads' => $optimizedWorkloads,
            'balance_score' => $this->calculateWorkloadBalance($optimizedWorkloads),
            'recommendations' => $this->generateWorkloadRecommendations($currentWorkloads, $optimizedWorkloads),
        ];
    }

    public function analyzeCriticalPath(Collection $tasks): array
    {
        $criticalPath = $this->calculateCriticalPath($tasks);
        
        return [
            'critical_tasks' => $criticalPath['tasks'],
            'total_duration' => $criticalPath['duration'],
            'slack_time' => $criticalPath['slack'],
            'risk_points' => $criticalPath['risks'],
            'optimization_opportunities' => $criticalPath['optimizations'],
            'contingency_planning' => $this->generateContingencyPlan($criticalPath),
        ];
    }

    public function generateScheduleRecommendations(Project $project, Collection $tasks): array
    {
        $recommendations = [];
        
        // Timeline recommendations
        $timelineIssues = $this->identifyTimelineIssues($tasks);
        foreach ($timelineIssues as $issue) {
            $recommendations[] = [
                'type' => 'timeline',
                'priority' => $issue['severity'],
                'action' => $issue['recommendation'],
                'reason' => $issue['description'],
                'impact' => $issue['impact'],
            ];
        }
        
        // Resource recommendations
        $resourceIssues = $this->identifyResourceIssues($project, $tasks);
        foreach ($resourceIssues as $issue) {
            $recommendations[] = [
                'type' => 'resource',
                'priority' => $issue['severity'],
                'action' => $issue['recommendation'],
                'reason' => $issue['description'],
                'impact' => $issue['impact'],
            ];
        }
        
        // Dependency recommendations
        $dependencyIssues = $this->identifyDependencyIssues($tasks);
        foreach ($dependencyIssues as $issue) {
            $recommendations[] = [
                'type' => 'dependency',
                'priority' => $issue['severity'],
                'action' => $issue['recommendation'],
                'reason' => $issue['description'],
                'impact' => $issue['impact'],
            ];
        }
        
        return $recommendations;
    }

    // Helper methods
    private function findBestCandidate(Task $task, Collection $teamMembers, array $constraints): ?array
    {
        $candidates = [];
        
        foreach ($teamMembers as $member) {
            $score = $this->calculateCandidateScore($task, $member, $constraints);
            
            if ($score > 0) {
                $candidates[] = [
                    'user_id' => $member->id,
                    'name' => $member->name,
                    'confidence' => $score,
                    'reasoning' => $this->generateCandidateReasoning($task, $member, $score),
                    'estimated_completion' => $this->estimateTaskCompletionForUser($task, $member),
                ];
            }
        }
        
        // Sort by confidence score and return the best candidate
        usort($candidates, function ($a, $b) {
            return $b['confidence'] <=> $a['confidence'];
        });
        
        return $candidates[0] ?? null;
    }

    private function calculateCandidateScore(Task $task, User $member, array $constraints): float
    {
        $score = 0;
        
        // Skill matching
        $skillScore = $this->calculateSkillMatch($member, collect([$task]));
        $score += $skillScore * 0.4;
        
        // Workload consideration
        $currentWorkload = $this->getCurrentWorkload($member);
        $workloadScore = max(0, 100 - ($currentWorkload * 10));
        $score += $workloadScore * 0.3;
        
        // Availability
        $availabilityScore = $this->calculateAvailability($member, $constraints);
        $score += $availabilityScore * 0.2;
        
        // Past performance
        $performanceScore = $this->calculatePastPerformance($member, $task);
        $score += $performanceScore * 0.1;
        
        return $score;
    }

    private function calculateSkillMatch(User $user, Collection $tasks): float
    {
        // Placeholder for skill matching algorithm
        // In a real implementation, this would analyze user skills vs task requirements
        return rand(60, 95);
    }

    private function getCurrentWorkload(User $user): int
    {
        // Calculate current number of active tasks for the user
        return Task::where('assigned_to', $user->id)
            ->where('status', '!=', 'completed')
            ->count();
    }

    private function calculateAvailability(User $user, array $constraints): float
    {
        // Calculate availability based on constraints (working hours, timezone, etc.)
        return isset($constraints['working_hours']) ? 80 : 100;
    }

    private function calculatePastPerformance(User $user, Task $task): float
    {
        // Calculate past performance on similar tasks
        $similarTasks = Task::where('assigned_to', $user->id)
            ->where('status', 'completed')
            ->where('category', $task->category)
            ->take(10)
            ->get();
        
        if ($similarTasks->isEmpty()) return 75; // Default score
        
        $onTimeCompletion = $similarTasks->filter(function ($task) {
            return $task->completed_at && (!$task->due_date || $task->completed_at->lessThanOrEqualTo($task->due_date));
        })->count();
        
        return ($onTimeCompletion / $similarTasks->count()) * 100;
    }

    private function generateCandidateReasoning(Task $task, User $member, float $score): string
    {
        $reasons = [];
        
        if ($score > 80) {
            $reasons[] = "Excellent skill match and availability";
        } elseif ($score > 60) {
            $reasons[] = "Good fit for the task requirements";
        } else {
            $reasons[] = "Available despite some skill gaps";
        }
        
        return implode(', ', $reasons);
    }

    private function estimateTaskCompletionForUser(Task $task, User $user): string
    {
        $baseDuration = $this->estimateTaskDuration($task);
        $skillMultiplier = $this->calculateSkillMatch($user, collect([$task])) / 100;
        
        $adjustedDuration = $baseDuration * (2 - $skillMultiplier); // Better skills = faster completion
        
        return $adjustedDuration . ' days';
    }

    private function sortTasksByPriority(Collection $tasks): Collection
    {
        return $tasks->sortByDesc(function ($task) {
            $priorityScore = match($task->priority) {
                'high' => 3,
                'medium' => 2,
                'low' => 1,
                default => 0,
            };
            
            // Add dependency weight
            $dependencyWeight = $task->dependencies ? count($task->dependencies) * 0.5 : 0;
            
            return $priorityScore + $dependencyWeight;
        });
    }

    private function calculateOptimalStartDate(Task $task, Carbon $currentDate, array $constraints): Carbon
    {
        // Check dependencies
        if ($task->dependencies) {
            $latestDependency = now();
            foreach ($task->dependencies as $depId) {
                $depTask = Task::find($depId);
                if ($depTask && $depTask->due_date) {
                    $latestDependency = $latestDependency->max($depTask->due_date);
                }
            }
            $currentDate = $latestDependency->addDay();
        }
        
        // Consider working days constraint
        if (isset($constraints['working_days_only']) && $constraints['working_days_only']) {
            while ($currentDate->isWeekend()) {
                $currentDate->addDay();
            }
        }
        
        return $currentDate;
    }

    private function estimateTaskDuration(Task $task): int
    {
        // Base duration estimation based on task complexity and priority
        $baseDays = match($task->priority) {
            'high' => 3,
            'medium' => 5,
            'low' => 7,
            default => 5,
        };
        
        // Adjust for task description length (complexity indicator)
        $complexityAdjustment = min(2, strlen($task->description) / 100);
        
        return (int)($baseDays + $complexityAdjustment);
    }

    private function calculateBufferDays(Task $task, array $constraints): int
    {
        $baseBuffer = match($task->priority) {
            'high' => 1,
            'medium' => 2,
            'low' => 3,
            default => 2,
        };
        
        // Add buffer for complex tasks
        if (strlen($task->description) > 200) {
            $baseBuffer += 1;
        }
        
        return $baseBuffer;
    }

    private function calculateTimelineConfidence(Task $task): float
    {
        // Confidence based on task clarity and historical data
        $confidence = 75; // Base confidence
        
        // Higher confidence for well-defined tasks
        if (strlen($task->description) > 50) {
            $confidence += 10;
        }
        
        // Lower confidence for high-priority tasks (more uncertainty)
        if ($task->priority === 'high') {
            $confidence -= 10;
        }
        
        return max(30, min(95, $confidence));
    }

    private function calculateResourceRequirements(Collection $tasks): array
    {
        $requirements = [
            'total_hours' => 0,
            'skill_requirements' => [],
            'peak_demand' => 0,
        ];
        
        foreach ($tasks as $task) {
            $requirements['total_hours'] += $this->estimateTaskHours($task);
            
            // Add skill requirements (placeholder)
            $requirements['skill_requirements'][] = $task->category;
        }
        
        return $requirements;
    }

    private function estimateTaskHours(Task $task): int
    {
        $duration = $this->estimateTaskDuration($task);
        return $duration * 8; // 8 hours per day
    }

    private function calculateMemberCapacity(User $member): int
    {
        // Standard capacity: 40 hours per week
        return 40;
    }

    private function getAssignedTasks(User $member, Collection $tasks): Collection
    {
        return $tasks->where('assigned_to', $member->id);
    }

    private function calculateUtilizationRate(User $member, Collection $tasks): float
    {
        $assignedTasks = $this->getAssignedTasks($member, $tasks);
        $totalHours = $this->estimateTotalHours($assignedTasks);
        $capacity = $this->calculateMemberCapacity($member);
        
        return $capacity > 0 ? ($totalHours / $capacity) * 100 : 0;
    }

    private function estimateTotalHours(Collection $tasks): int
    {
        return $tasks->sum(function ($task) {
            return $this->estimateTaskHours($task);
        });
    }

    private function generateResourceRecommendations(User $member, Collection $tasks): array
    {
        $recommendations = [];
        $utilization = $this->calculateUtilizationRate($member, $tasks);
        
        if ($utilization > 90) {
            $recommendations[] = "Consider redistributing some tasks to reduce workload";
        } elseif ($utilization < 50) {
            $recommendations[] = "Team member has capacity for additional tasks";
        }
        
        return $recommendations;
    }

    private function buildDependencyGraph(Collection $tasks): array
    {
        $graph = [];
        
        foreach ($tasks as $task) {
            $graph[$task->id] = $task->dependencies ?? [];
        }
        
        return $graph;
    }

    private function findBlockingTasks(int $taskId, array $dependencyGraph): array
    {
        $blockingTasks = [];
        
        foreach ($dependencyGraph as $taskId => $dependencies) {
            if (in_array($taskId, $dependencies)) {
                $blockingTasks[] = $taskId;
            }
        }
        
        return $blockingTasks;
    }

    private function findParallelTasks(int $taskId, array $dependencyGraph): array
    {
        $parallelTasks = [];
        $taskDependencies = $dependencyGraph[$taskId] ?? [];
        
        foreach ($dependencyGraph as $otherTaskId => $dependencies) {
            if ($otherTaskId !== $taskId && !array_intersect($taskDependencies, $dependencies)) {
                $parallelTasks[] = $otherTaskId;
            }
        }
        
        return $parallelTasks;
    }

    private function optimizeDependencies(int $taskId, array $dependencies, array $dependencyGraph): array
    {
        $suggestions = [];
        
        // Check for circular dependencies
        if ($this->hasCircularDependency($taskId, $dependencyGraph)) {
            $suggestions[] = "Resolve circular dependency to prevent deadlock";
        }
        
        // Check for unnecessary dependencies
        if (count($dependencies) > 3) {
            $suggestions[] = "Consider breaking down task or reducing dependencies";
        }
        
        return $suggestions;
    }

    private function hasCircularDependency(int $taskId, array $dependencyGraph): bool
    {
        $visited = [];
        $recursionStack = [];
        
        return $this->hasCircularDependencyHelper($taskId, $dependencyGraph, $visited, $recursionStack);
    }

    private function hasCircularDependencyHelper(int $taskId, array $dependencyGraph, array &$visited, array &$recursionStack): bool
    {
        if (isset($recursionStack[$taskId])) {
            return true;
        }
        
        if (isset($visited[$taskId])) {
            return false;
        }
        
        $visited[$taskId] = true;
        $recursionStack[$taskId] = true;
        
        foreach ($dependencyGraph[$taskId] ?? [] as $dependency) {
            if ($this->hasCircularDependencyHelper($dependency, $dependencyGraph, $visited, $recursionStack)) {
                return true;
            }
        }
        
        unset($recursionStack[$taskId]);
        return false;
    }

    private function redistributeTasks(Collection $teamMembers, Collection $tasks, array $currentWorkloads): array
    {
        // Simplified redistribution logic
        $optimized = $currentWorkloads;
        
        // Find overworked members
        $overworked = array_filter($optimized, function ($workload) {
            return $workload['estimated_hours'] > 40;
        });
        
        // Find underworked members
        $underworked = array_filter($optimized, function ($workload) {
            return $workload['estimated_hours'] < 20;
        });
        
        // Redistribute some tasks (simplified)
        if (!empty($overworked) && !empty($underworked)) {
            // In a real implementation, this would involve complex task reassignment logic
            $optimized = $this->balanceWorkloadsSimple($optimized);
        }
        
        return $optimized;
    }

    private function balanceWorkloadsSimple(array $workloads): array
    {
        // Simple balancing: redistribute hours evenly
        $totalHours = array_sum(array_column($workloads, 'estimated_hours'));
        $memberCount = count($workloads);
        $targetHours = $totalHours / $memberCount;
        
        foreach ($workloads as &$workload) {
            $workload['estimated_hours'] = $targetHours;
            $workload['task_count'] = (int)($targetHours / 8); // 8 hours per task
        }
        
        return $workloads;
    }

    private function calculateWorkloadBalance(array $workloads): float
    {
        if (empty($workloads)) return 100;
        
        $hours = array_column($workloads, 'estimated_hours');
        $mean = array_sum($hours) / count($hours);
        
        $variance = array_sum(array_map(function ($hour) use ($mean) {
            return pow($hour - $mean, 2);
        }, $hours)) / count($hours);
        
        $standardDeviation = sqrt($variance);
        
        // Lower standard deviation = better balance
        return max(0, 100 - ($standardDeviation * 2));
    }

    private function calculateComplexityScore(Collection $tasks): float
    {
        // Simple complexity calculation based on task descriptions
        $totalLength = $tasks->sum(function ($task) {
            return strlen($task->description);
        });
        
        return min(100, ($totalLength / count($tasks)) / 2);
    }

    private function calculateStressLevel(User $member, Collection $tasks): string
    {
        $workload = $this->getCurrentWorkload($member);
        
        if ($workload > 8) return 'high';
        if ($workload > 5) return 'medium';
        return 'low';
    }

    private function generateWorkloadRecommendations(array $current, array $optimized): array
    {
        $recommendations = [];
        
        foreach ($current as $memberId => $currentWorkload) {
            $optimizedWorkload = $optimized[$memberId] ?? $currentWorkload;
            
            if ($currentWorkload['estimated_hours'] > $optimizedWorkload['estimated_hours']) {
                $recommendations[] = "Reduce workload for {$currentWorkload['name']} by " . 
                    ($currentWorkload['estimated_hours'] - $optimizedWorkload['estimated_hours']) . " hours";
            } elseif ($currentWorkload['estimated_hours'] < $optimizedWorkload['estimated_hours']) {
                $recommendations[] = "Increase workload for {$currentWorkload['name']} by " . 
                    ($optimizedWorkload['estimated_hours'] - $currentWorkload['estimated_hours']) . " hours";
            }
        }
        
        return $recommendations;
    }

    private function calculateCriticalPath(Collection $tasks): array
    {
        // Simplified critical path calculation
        $criticalTasks = $tasks->where('priority', 'high')->pluck('id')->toArray();
        $totalDuration = $criticalTasks ? count($criticalTasks) * 3 : 0; // 3 days per high-priority task
        
        return [
            'tasks' => $criticalTasks,
            'duration' => $totalDuration,
            'slack' => 0,
            'risks' => [],
            'optimizations' => [],
        ];
    }

    private function generateContingencyPlan(array $criticalPath): array
    {
        return [
            'backup_resources' => 'Identify alternative team members for critical tasks',
            'time_buffers' => 'Add buffer time between critical path tasks',
            'risk_mitigation' => 'Monitor high-risk tasks closely',
            'escalation_plan' => 'Define escalation procedures for delays',
        ];
    }

    private function identifyTimelineIssues(Collection $tasks): array
    {
        $issues = [];
        
        $overdueTasks = $tasks->where('due_date', '<', now())->where('status', '!=', 'completed');
        if ($overdueTasks->count() > 0) {
            $issues[] = [
                'severity' => 'high',
                'description' => "{$overdueTasks->count()} tasks are overdue",
                'recommendation' => 'Reassess priorities and deadlines',
                'impact' => 'Project timeline at risk',
            ];
        }
        
        return $issues;
    }

    private function identifyResourceIssues(Project $project, Collection $tasks): array
    {
        $issues = [];
        
        $unassignedTasks = $tasks->where('assigned_to', null);
        if ($unassignedTasks->count() > $tasks->count() * 0.3) {
            $issues[] = [
                'severity' => 'medium',
                'description' => 'High percentage of unassigned tasks',
                'recommendation' => 'Assign tasks to available team members',
                'impact' => 'Resource utilization inefficiency',
            ];
        }
        
        return $issues;
    }

    private function identifyDependencyIssues(Collection $tasks): array
    {
        $issues = [];
        
        $tasksWithDependencies = $tasks->filter(function ($task) {
            return $task->dependencies && count($task->dependencies) > 0;
        });
        
        if ($tasksWithDependencies->count() > $tasks->count() * 0.5) {
            $issues[] = [
                'severity' => 'medium',
                'description' => 'High number of task dependencies',
                'recommendation' => 'Review and optimize task dependencies',
                'impact' => 'Potential for cascading delays',
            ];
        }
        
        return $issues;
    }
}
