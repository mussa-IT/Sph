<?php

namespace App\Services;

use App\Models\Project;
use Illuminate\Support\Collection;

class ProjectDNAGeneratorService
{
    /**
     * Generate a unique DNA fingerprint for a project
     * This fingerprint captures the project's characteristics and can be used
     * to find similar projects and suggest best practices
     */
    public function generateDNA(Project $project): array
    {
        $characteristics = [
            'complexity_score' => $this->calculateComplexityScore($project),
            'team_size_factor' => $this->calculateTeamSizeFactor($project),
            'timeline_pattern' => $this->analyzeTimelinePattern($project),
            'budget_efficiency' => $this->calculateBudgetEfficiency($project),
            'task_diversity' => $this->calculateTaskDiversity($project),
            'collaboration_density' => $this->calculateCollaborationDensity($project),
            'risk_profile' => $this->assessRiskProfile($project),
            'success_probability' => $this->predictSuccessProbability($project),
        ];

        // Generate DNA hash
        $dnaString = $this->encodeDNA($characteristics);
        $dnaHash = hash('sha256', $dnaString);

        return [
            'dna_hash' => $dnaHash,
            'dna_string' => $dnaString,
            'characteristics' => $characteristics,
            'similar_projects' => $this->findSimilarProjects($dnaHash, $project),
            'recommended_practices' => $this->getRecommendedPractices($characteristics),
        ];
    }

    private function calculateComplexityScore(Project $project): float
    {
        $taskCount = $project->tasks()->count();
        $budgetCount = $project->budgets()->count();
        $memberCount = $project->team ? $project->team->members()->count() : 1;

        // Complexity increases with tasks, budgets, and team size
        $complexity = ($taskCount * 0.4) + ($budgetCount * 0.3) + ($memberCount * 0.3);
        
        return min(100, $complexity);
    }

    private function calculateTeamSizeFactor(Project $project): float
    {
        $memberCount = $project->team ? $project->team->members()->count() : 1;
        
        // Optimal team size is typically 3-7
        if ($memberCount >= 3 && $memberCount <= 7) {
            return 1.0; // Optimal
        } elseif ($memberCount < 3) {
            return 0.6; // Too small
        } else {
            return 0.8 - (($memberCount - 7) * 0.05); // Diminishing returns
        }
    }

    private function analyzeTimelinePattern(Project $project): string
    {
        $tasks = $project->tasks()->get();
        
        if ($tasks->isEmpty()) {
            return 'unknown';
        }

        $completedTasks = $tasks->where('status', 'completed');
        $avgDuration = $completedTasks->avg(function ($task) {
            if ($task->completed_at && $task->created_at) {
                return $task->completed_at->diffInDays($task->created_at);
            }
            return 0;
        });

        if ($avgDuration < 3) {
            return 'rapid';
        } elseif ($avgDuration < 7) {
            return 'normal';
        } elseif ($avgDuration < 14) {
            return 'extended';
        } else {
            return 'prolonged';
        }
    }

    private function calculateBudgetEfficiency(Project $project): float
    {
        $budgets = $project->budgets()->get();
        
        if ($budgets->isEmpty()) {
            return 0;
        }

        $totalBudget = $budgets->sum('amount');
        $totalSpent = $budgets->sum('spent');

        if ($totalBudget == 0) {
            return 0;
        }

        $efficiency = ($totalSpent / $totalBudget) * 100;
        
        // Efficiency is good if spending is within 80-100% of budget
        if ($efficiency >= 80 && $efficiency <= 100) {
            return 1.0;
        } elseif ($efficiency > 100) {
            return max(0, 1.0 - (($efficiency - 100) * 0.02));
        } else {
            return $efficiency / 100;
        }
    }

    private function calculateTaskDiversity(Project $project): float
    {
        $tasks = $project->tasks()->get();
        
        if ($tasks->isEmpty()) {
            return 0;
        }

        $categories = $tasks->pluck('category')->unique()->count();
        $priorities = $tasks->pluck('priority')->unique()->count();
        
        // Diversity based on categories and priorities
        $diversity = (($categories / 5) * 0.5) + (($priorities / 3) * 0.5);
        
        return min(1.0, $diversity);
    }

    private function calculateCollaborationDensity(Project $project): float
    {
        $comments = $project->comments()->count();
        $tasks = $project->tasks()->count();
        
        if ($tasks == 0) {
            return 0;
        }

        // Comments per task as collaboration metric
        $density = $comments / $tasks;
        
        return min(1.0, $density / 5); // Normalize to 0-1
    }

    private function assessRiskProfile(Project $project): string
    {
        $characteristics = [
            'complexity' => $this->calculateComplexityScore($project),
            'timeline' => $this->analyzeTimelinePattern($project),
            'budget' => $this->calculateBudgetEfficiency($project),
        ];

        $riskScore = 0;

        // High complexity increases risk
        if ($characteristics['complexity'] > 70) {
            $riskScore += 30;
        }

        // Prolonged timeline increases risk
        if ($characteristics['timeline'] === 'prolonged') {
            $riskScore += 25;
        }

        // Poor budget efficiency increases risk
        if ($characteristics['budget'] < 0.7) {
            $riskScore += 25;
        }

        if ($riskScore >= 50) {
            return 'high';
        } elseif ($riskScore >= 25) {
            return 'medium';
        } else {
            return 'low';
        }
    }

    private function predictSuccessProbability(Project $project): float
    {
        $characteristics = [
            'team_size' => $this->calculateTeamSizeFactor($project),
            'budget' => $this->calculateBudgetEfficiency($project),
            'collaboration' => $this->calculateCollaborationDensity($project),
            'risk' => $this->assessRiskProfile($project),
        ];

        $probability = 50; // Base probability

        // Adjust based on characteristics
        $probability += ($characteristics['team_size'] - 0.8) * 20;
        $probability += ($characteristics['budget'] - 0.5) * 15;
        $probability += $characteristics['collaboration'] * 10;

        // Risk penalty
        if ($characteristics['risk'] === 'high') {
            $probability -= 20;
        } elseif ($characteristics['risk'] === 'medium') {
            $probability -= 10;
        }

        return max(0, min(100, $probability));
    }

    private function encodeDNA(array $characteristics): string
    {
        $parts = [];
        
        foreach ($characteristics as $key => $value) {
            if (is_float($value)) {
                $parts[] = sprintf("%s:%.2f", $key, $value);
            } else {
                $parts[] = sprintf("%s:%s", $key, $value);
            }
        }

        return implode('|', $parts);
    }

    private function findSimilarProjects(string $dnaHash, Project $currentProject): Collection
    {
        // In a real implementation, this would query a database of project DNA hashes
        // For now, return empty collection
        return collect([]);
    }

    private function getRecommendedPractices(array $characteristics): array
    {
        $practices = [];

        if ($characteristics['complexity_score'] > 70) {
            $practices[] = [
                'type' => 'complexity',
                'recommendation' => 'Consider breaking down complex projects into smaller sub-projects',
                'priority' => 'high',
            ];
        }

        if ($characteristics['team_size_factor'] < 0.8) {
            $practices[] = [
                'type' => 'team',
                'recommendation' => 'Optimal team size is 3-7 members. Consider adjusting team composition',
                'priority' => 'medium',
            ];
        }

        if ($characteristics['timeline_pattern'] === 'prolonged') {
            $practices[] = [
                'type' => 'timeline',
                'recommendation' => 'Tasks are taking longer than expected. Review dependencies and resource allocation',
                'priority' => 'high',
            ];
        }

        if ($characteristics['budget_efficiency'] < 0.7) {
            $practices[] = [
                'type' => 'budget',
                'recommendation' => 'Budget utilization is below optimal. Review spending patterns and adjust forecasts',
                'priority' => 'medium',
            ];
        }

        if ($characteristics['collaboration_density'] < 0.3) {
            $practices[] = [
                'type' => 'collaboration',
                'recommendation' => 'Increase team communication through regular updates and comment threads',
                'priority' => 'low',
            ];
        }

        return $practices;
    }
}
