<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Collection;

class SmartResourceMatcherService
{
    /**
     * AI-powered resource matching that assigns optimal team members to tasks
     * based on skills, availability, workload, and historical performance
     */
    public function matchResourcesForProject(Project $project): array
    {
        $tasks = $project->tasks()->where('status', '!=', 'completed')->get();
        $teamMembers = $project->team ? $project->team->members()->where('is_active', true)->get() : collect([$project->user]);

        $matches = [];

        foreach ($tasks as $task) {
            $taskSkills = $this->extractTaskSkills($task);
            $taskPriority = $task->priority;
            $taskComplexity = $this->assessTaskComplexity($task);

            $memberScores = [];

            foreach ($teamMembers as $member) {
                $score = $this->calculateMatchScore($member, $task, $taskSkills, $taskPriority, $taskComplexity);
                $memberScores[] = [
                    'member' => $member,
                    'score' => $score,
                    'reasoning' => $this->explainScore($score, $member, $task),
                ];
            }

            // Sort by score descending
            usort($memberScores, function ($a, $b) {
                return $b['score'] <=> $a['score'];
            });

            $matches[] = [
                'task' => $task,
                'required_skills' => $taskSkills,
                'recommended_assignments' => array_slice($memberScores, 0, 3),
                'confidence' => $memberScores[0]['score'] ?? 0,
            ];
        }

        return [
            'project_id' => $project->id,
            'total_tasks' => count($matches),
            'matches' => $matches,
            'team_utilization' => $this->calculateTeamUtilization($teamMembers),
            'recommendations' => $this->generateTeamRecommendations($matches, $teamMembers),
        ];
    }

    private function extractTaskSkills(Task $task): array
    {
        // Extract skills from task description, title, and tags
        $text = strtolower($task->title . ' ' . $task->description);
        
        $skillKeywords = [
            'design' => ['design', 'ui', 'ux', 'figma', 'sketch', 'wireframe', 'mockup'],
            'development' => ['code', 'develop', 'programming', 'api', 'backend', 'frontend', 'database'],
            'testing' => ['test', 'qa', 'quality', 'bug', 'debug', 'verify'],
            'documentation' => ['document', 'write', 'guide', 'manual', 'readme'],
            'management' => ['manage', 'coordinate', 'lead', 'plan', 'schedule'],
            'marketing' => ['market', 'promote', 'seo', 'content', 'social'],
            'analytics' => ['analyze', 'data', 'metrics', 'report', 'insight'],
            'security' => ['security', 'secure', 'encrypt', 'protect', 'vulnerability'],
        ];

        $detectedSkills = [];

        foreach ($skillKeywords as $skill => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($text, $keyword)) {
                    $detectedSkills[] = $skill;
                    break;
                }
            }
        }

        return array_unique($detectedSkills);
    }

    private function assessTaskComplexity(Task $task): string
    {
        $descriptionLength = strlen($task->description);
        $subtaskCount = $task->subtasks()->count();

        if ($descriptionLength > 500 || $subtaskCount > 5) {
            return 'high';
        } elseif ($descriptionLength > 200 || $subtaskCount > 2) {
            return 'medium';
        } else {
            return 'low';
        }
    }

    private function calculateMatchScore(User $member, Task $task, array $taskSkills, string $priority, string $complexity): float
    {
        $score = 0;

        // Skill match (40% weight)
        $memberSkills = $this->getMemberSkills($member);
        $skillMatch = $this->calculateSkillMatch($taskSkills, $memberSkills);
        $score += $skillMatch * 40;

        // Availability (25% weight)
        $availability = $this->calculateAvailability($member);
        $score += $availability * 25;

        // Workload balance (20% weight)
        $workload = $this->calculateWorkloadScore($member);
        $score += $workload * 20;

        // Historical performance (15% weight)
        $performance = $this->getHistoricalPerformance($member, $taskSkills);
        $score += $performance * 15;

        // Priority boost
        if ($priority === 'high') {
            $score *= 1.1;
        }

        // Complexity adjustment
        if ($complexity === 'high' && $performance > 0.7) {
            $score *= 1.05;
        }

        return min(100, $score);
    }

    private function getMemberSkills(User $member): array
    {
        // In a real implementation, this would come from user profile, completed tasks, etc.
        // For now, return based on user's role and past projects
        $skills = [];

        if ($member->role === 'developer' || str_contains(strtolower($member->bio ?? ''), 'develop')) {
            $skills[] = 'development';
        }
        if ($member->role === 'designer' || str_contains(strtolower($member->bio ?? ''), 'design')) {
            $skills[] = 'design';
        }
        if ($member->role === 'manager' || str_contains(strtolower($member->bio ?? ''), 'manage')) {
            $skills[] = 'management';
        }

        return $skills;
    }

    private function calculateSkillMatch(array $taskSkills, array $memberSkills): float
    {
        if (empty($taskSkills)) {
            return 0.5; // Neutral if no skills detected
        }

        $matches = array_intersect($taskSkills, $memberSkills);
        $matchRatio = count($matches) / count($taskSkills);

        return $matchRatio;
    }

    private function calculateAvailability(User $member): float
    {
        // Check if member is currently working on too many high-priority tasks
        $activeTasks = $member->assignedTasks()
            ->where('status', '!=', 'completed')
            ->where('priority', 'high')
            ->count();

        if ($activeTasks >= 5) {
            return 0.2; // Very busy
        } elseif ($activeTasks >= 3) {
            return 0.5; // Moderately busy
        } else {
            return 1.0; // Available
        }
    }

    private function calculateWorkloadScore(User $member): float
    {
        $totalTasks = $member->assignedTasks()
            ->where('status', '!=', 'completed')
            ->count();

        // Optimal workload is 3-5 tasks
        if ($totalTasks >= 3 && $totalTasks <= 5) {
            return 1.0;
        } elseif ($totalTasks < 3) {
            return 0.7; // Underutilized
        } else {
            return max(0, 1.0 - (($totalTasks - 5) * 0.15)); // Overloaded
        }
    }

    private function getHistoricalPerformance(User $member, array $taskSkills): float
    {
        // Calculate member's historical performance on similar tasks
        $completedTasks = $member->assignedTasks()
            ->where('status', 'completed')
            ->get();

        if ($completedTasks->isEmpty()) {
            return 0.5; // Neutral for new members
        }

        // Calculate on-time completion rate
        $onTimeCount = 0;
        foreach ($completedTasks as $task) {
            if ($task->completed_at && $task->due_at) {
                if ($task->completed_at->lte($task->due_at)) {
                    $onTimeCount++;
                }
            }
        }

        $onTimeRate = $onTimeCount / $completedTasks->count();

        return $onTimeRate;
    }

    private function explainScore(float $score, User $member, Task $task): string
    {
        $reasons = [];

        if ($score > 80) {
            $reasons[] = 'Excellent match based on skills and availability';
        } elseif ($score > 60) {
            $reasons[] = 'Good match with some considerations';
        } elseif ($score > 40) {
            $reasons[] = 'Moderate match, may need support';
        } else {
            $reasons[] = 'Low match, consider alternative assignment';
        }

        return implode('. ', $reasons);
    }

    private function calculateTeamUtilization(Collection $teamMembers): array
    {
        $utilization = [];

        foreach ($teamMembers as $member) {
            $activeTasks = $member->assignedTasks()
                ->where('status', '!=', 'completed')
                ->count();

            $utilization[] = [
                'member_id' => $member->id,
                'member_name' => $member->name,
                'active_tasks' => $activeTasks,
                'utilization_percentage' => min(100, ($activeTasks / 5) * 100),
            ];
        }

        return $utilization;
    }

    private function generateTeamRecommendations(array $matches, Collection $teamMembers): array
    {
        $recommendations = [];

        // Check for overloaded members
        foreach ($teamMembers as $member) {
            $activeTasks = $member->assignedTasks()
                ->where('status', '!=', 'completed')
                ->count();

            if ($activeTasks > 5) {
                $recommendations[] = [
                    'type' => 'workload',
                    'severity' => 'high',
                    'message' => "{$member->name} is overloaded with {$activeTasks} active tasks",
                    'suggestion' => 'Consider redistributing some tasks to available team members',
                ];
            }
        }

        // Check for unassigned high-priority tasks
        $unassignedHighPriority = collect($matches)
            ->filter(fn($match) => $match['task']->priority === 'high' && !$match['task']->assigned_to)
            ->count();

        if ($unassignedHighPriority > 0) {
            $recommendations[] = [
                'type' => 'assignment',
                'severity' => 'high',
                'message' => "{$unassignedHighPriority} high-priority tasks are unassigned",
                'suggestion' => 'Assign these tasks to top-recommended team members immediately',
            ];
        }

        return $recommendations;
    }
}
