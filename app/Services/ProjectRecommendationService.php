<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\Team;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class ProjectRecommendationService
{
    public function generateRecommendations(User $user): array
    {
        return [
            'project_templates' => $this->recommendProjectTemplates($user),
            'team_suggestions' => $this->suggestTeamMembers($user),
            'skill_development' => $this->recommendSkillDevelopment($user),
            'workflow_optimizations' => $this->suggestWorkflowOptimizations($user),
            'resource_recommendations' => $this->recommendResources($user),
            'best_practices' => $this->recommendBestPractices($user),
            'tool_suggestions' => $this->suggestTools($user),
            'learning_paths' => $this->recommendLearningPaths($user),
        ];
    }

    public function recommendProjectTemplates(User $user): array
    {
        $userProjects = $user->projects;
        $userSkills = $this->extractUserSkills($user);
        $industry = $this->detectUserIndustry($user);
        
        $templates = [
            'industry_specific' => $this->getIndustryTemplates($industry),
            'skill_based' => $this->getSkillBasedTemplates($userSkills),
            'complexity_matched' => $this->getComplexityMatchedTemplates($userProjects),
            'trending' => $this->getTrendingTemplates(),
        ];
        
        return array_merge($templates['industry_specific'], $templates['skill_based'], $templates['complexity_matched'], $templates['trending']);
    }

    public function suggestTeamMembers(User $user): array
    {
        $currentProjects = $user->projects()->where('status', 'active')->get();
        $suggestions = [];
        
        foreach ($currentProjects as $project) {
            $projectSuggestions = $this->getProjectTeamSuggestions($project, $user);
            $suggestions = array_merge($suggestions, $projectSuggestions);
        }
        
        // Remove duplicates and sort by relevance
        $suggestions = $this->deduplicateAndSortSuggestions($suggestions);
        
        return array_slice($suggestions, 0, 10); // Top 10 suggestions
    }

    public function recommendSkillDevelopment(User $user): array
    {
        $currentSkills = $this->extractUserSkills($user);
        $projectRequirements = $this->analyzeProjectRequirements($user);
        $skillGaps = $this->identifySkillGaps($currentSkills, $projectRequirements);
        
        $recommendations = [];
        
        foreach ($skillGaps as $gap) {
            $recommendations[] = [
                'skill' => $gap['skill'],
                'current_level' => $gap['current_level'],
                'target_level' => $gap['target_level'],
                'importance' => $gap['importance'],
                'learning_resources' => $this->getLearningResources($gap['skill']),
                'estimated_time' => $this->estimateLearningTime($gap['skill']),
                'career_impact' => $this->assessCareerImpact($gap['skill']),
            ];
        }
        
        return $recommendations;
    }

    public function suggestWorkflowOptimizations(User $user): array
    {
        $userWorkflows = $this->analyzeUserWorkflows($user);
        $inefficiencies = $this->identifyWorkflowInefficiencies($userWorkflows);
        
        $optimizations = [];
        
        foreach ($inefficiencies as $inefficiency) {
            $optimizations[] = [
                'area' => $inefficiency['area'],
                'current_process' => $inefficiency['current_process'],
                'issue' => $inefficiency['issue'],
                'suggested_solution' => $inefficiency['solution'],
                'expected_improvement' => $inefficiency['improvement'],
                'implementation_difficulty' => $inefficiency['difficulty'],
                'tools_needed' => $inefficiency['tools'],
            ];
        }
        
        return $optimizations;
    }

    public function recommendResources(User $user): array
    {
        $userContext = $this->buildUserContext($user);
        $recommendations = [
            'productivity_tools' => $this->recommendProductivityTools($userContext),
            'learning_materials' => $this->recommendLearningMaterials($userContext),
            'templates_assets' => $this->recommendTemplatesAndAssets($userContext),
            'integrations' => $this->recommendIntegrations($userContext),
        ];
        
        return array_merge(
            $recommendations['productivity_tools'],
            $recommendations['learning_materials'],
            $recommendations['templates_assets'],
            $recommendations['integrations']
        );
    }

    public function recommendBestPractices(User $user): array
    {
        $projectTypes = $this->getUserProjectTypes($user);
        $bestPractices = [];
        
        foreach ($projectTypes as $type) {
            $practices = $this->getBestPracticesForType($type);
            $bestPractices = array_merge($bestPractices, $practices);
        }
        
        // Personalize based on user's current performance
        $personalizedPractices = $this->personalizeBestPractices($bestPractices, $user);
        
        return $personalizedPractices;
    }

    public function suggestTools(User $user): array
    {
        $currentTools = $this->getUserCurrentTools($user);
        $projectNeeds = $this->analyzeProjectToolNeeds($user);
        $toolGaps = $this->identifyToolGaps($currentTools, $projectNeeds);
        
        $suggestions = [];
        
        foreach ($toolGaps as $gap) {
            $suggestions[] = [
                'category' => $gap['category'],
                'recommended_tools' => $gap['tools'],
                'benefits' => $gap['benefits'],
                'integration_options' => $gap['integrations'],
                'cost_estimate' => $gap['cost'],
                'learning_curve' => $gap['learning_curve'],
            ];
        }
        
        return $suggestions;
    }

    public function recommendLearningPaths(User $user): array
    {
        $careerGoals = $this->inferCareerGoals($user);
        $currentSkills = $this->extractUserSkills($user);
        $industryTrends = $this->getIndustryTrends($user);
        
        $paths = [];
        
        foreach ($careerGoals as $goal) {
            $path = $this->buildLearningPath($goal, $currentSkills, $industryTrends);
            $paths[] = $path;
        }
        
        return $paths;
    }

    // Helper methods
    private function extractUserSkills(User $user): array
    {
        // Extract skills from user's projects, tasks, and profile
        $skills = [];
        
        $projects = $user->projects;
        foreach ($projects as $project) {
            $projectSkills = $this->extractSkillsFromProject($project);
            $skills = array_merge($skills, $projectSkills);
        }
        
        // Add skills from user profile (if available)
        if (isset($user->profile['skills'])) {
            $skills = array_merge($skills, $user->profile['skills']);
        }
        
        // Count skill frequency and return with confidence scores
        return $this->calculateSkillConfidence($skills);
    }

    private function extractSkillsFromProject(Project $project): array
    {
        $skills = [];
        
        // Extract from project description and category
        $skills = array_merge($skills, $this->extractSkillsFromText($project->description));
        $skills[] = $project->category;
        
        // Extract from tasks
        foreach ($project->tasks as $task) {
            $taskSkills = $this->extractSkillsFromText($task->description);
            $skills = array_merge($skills, $taskSkills);
        }
        
        return $skills;
    }

    private function extractSkillsFromText(string $text): array
    {
        // Simplified skill extraction - in a real implementation, this would use NLP
        $skillKeywords = [
            'javascript', 'python', 'react', 'vue', 'angular', 'nodejs', 'php', 'laravel',
            'design', 'ui', 'ux', 'frontend', 'backend', 'database', 'api', 'testing',
            'agile', 'scrum', 'project management', 'leadership', 'communication',
            'marketing', 'seo', 'analytics', 'data analysis', 'machine learning',
            'devops', 'aws', 'docker', 'kubernetes', 'git', 'ci/cd', 'security'
        ];
        
        $foundSkills = [];
        $text = strtolower($text);
        
        foreach ($skillKeywords as $skill) {
            if (str_contains($text, $skill)) {
                $foundSkills[] = $skill;
            }
        }
        
        return $foundSkills;
    }

    private function calculateSkillConfidence(array $skills): array
    {
        $skillCounts = array_count_values($skills);
        $totalOccurrences = array_sum($skillCounts);
        
        $skillConfidence = [];
        
        foreach ($skillCounts as $skill => $count) {
            $confidence = ($count / $totalOccurrences) * 100;
            $skillConfidence[$skill] = [
                'skill' => $skill,
                'confidence' => $confidence,
                'occurrences' => $count,
                'level' => $this->assessSkillLevel($confidence),
            ];
        }
        
        // Sort by confidence
        uasort($skillConfidence, function ($a, $b) {
            return $b['confidence'] <=> $a['confidence'];
        });
        
        return array_values($skillConfidence);
    }

    private function assessSkillLevel(float $confidence): string
    {
        if ($confidence >= 70) return 'expert';
        if ($confidence >= 40) return 'intermediate';
        return 'beginner';
    }

    private function detectUserIndustry(User $user): string
    {
        // Detect industry based on project patterns and user profile
        $projects = $user->projects;
        $industryKeywords = [];
        
        foreach ($projects as $project) {
            $industryKeywords = array_merge($industryKeywords, $this->extractIndustryKeywords($project));
        }
        
        $industryCounts = array_count_values($industryKeywords);
        
        if (empty($industryCounts)) {
            return 'general';
        }
        
        return array_keys($industryCounts, max($industryCounts))[0];
    }

    private function extractIndustryKeywords(Project $project): array
    {
        $keywords = [];
        $text = strtolower($project->title . ' ' . $project->description);
        
        $industryMap = [
            'technology' => ['software', 'app', 'web', 'tech', 'development', 'programming'],
            'healthcare' => ['medical', 'health', 'hospital', 'clinic', 'pharmaceutical'],
            'finance' => ['banking', 'financial', 'investment', 'insurance', 'fintech'],
            'education' => ['school', 'university', 'education', 'learning', 'training'],
            'retail' => ['shop', 'store', 'ecommerce', 'retail', 'sales'],
            'manufacturing' => ['factory', 'production', 'manufacturing', 'industrial'],
            'marketing' => ['marketing', 'advertising', 'brand', 'campaign', 'promotion'],
        ];
        
        foreach ($industryMap as $industry => $industryKeywords) {
            foreach ($industryKeywords as $keyword) {
                if (str_contains($text, $keyword)) {
                    $keywords[] = $industry;
                    break;
                }
            }
        }
        
        return $keywords;
    }

    private function getIndustryTemplates(string $industry): array
    {
        $templates = [
            'technology' => [
                ['name' => 'Software Development Sprint', 'type' => 'agile', 'complexity' => 'medium'],
                ['name' => 'Mobile App Launch', 'type' => 'product', 'complexity' => 'high'],
                ['name' => 'API Development', 'type' => 'technical', 'complexity' => 'medium'],
            ],
            'healthcare' => [
                ['name' => 'Medical Research Project', 'type' => 'research', 'complexity' => 'high'],
                ['name' => 'Healthcare System Implementation', 'type' => 'implementation', 'complexity' => 'high'],
            ],
            'finance' => [
                ['name' => 'Fintech Product Development', 'type' => 'product', 'complexity' => 'high'],
                ['name' => 'Compliance Audit', 'type' => 'compliance', 'complexity' => 'medium'],
            ],
            'general' => [
                ['name' => 'General Project Management', 'type' => 'management', 'complexity' => 'medium'],
                ['name' => 'Team Collaboration', 'type' => 'collaboration', 'complexity' => 'low'],
            ],
        ];
        
        return $templates[$industry] ?? $templates['general'];
    }

    private function getSkillBasedTemplates(array $userSkills): array
    {
        $templates = [];
        
        foreach ($userSkills as $skillData) {
            $skill = $skillData['skill'];
            $level = $skillData['level'];
            
            $skillTemplates = $this->getTemplatesForSkill($skill, $level);
            $templates = array_merge($templates, $skillTemplates);
        }
        
        return $templates;
    }

    private function getTemplatesForSkill(string $skill, string $level): array
    {
        $templateMap = [
            'javascript' => [
                'Frontend Development Project',
                'JavaScript Application',
                'Web Component Library',
            ],
            'python' => [
                'Data Analysis Project',
                'Python Web Application',
                'Machine Learning Prototype',
            ],
            'project management' => [
                'Agile Sprint Planning',
                'Project Kickoff',
                'Stakeholder Management',
            ],
        ];
        
        return $templateMap[$skill] ?? [];
    }

    private function getComplexityMatchedTemplates(Collection $userProjects): array
    {
        $avgComplexity = $this->calculateAverageComplexity($userProjects);
        
        if ($avgComplexity < 3) {
            return [
                ['name' => 'Simple Project Template', 'type' => 'basic', 'complexity' => 'low'],
                ['name' => 'Quick Start Guide', 'type' => 'guide', 'complexity' => 'low'],
            ];
        } elseif ($avgComplexity < 7) {
            return [
                ['name' => 'Standard Project Template', 'type' => 'standard', 'complexity' => 'medium'],
                ['name' => 'Team Collaboration Setup', 'type' => 'collaboration', 'complexity' => 'medium'],
            ];
        } else {
            return [
                ['name' => 'Complex Enterprise Project', 'type' => 'enterprise', 'complexity' => 'high'],
                ['name' => 'Multi-team Coordination', 'type' => 'coordination', 'complexity' => 'high'],
            ];
        }
    }

    private function calculateAverageComplexity(Collection $projects): float
    {
        if ($projects->isEmpty()) return 5;
        
        $totalComplexity = 0;
        
        foreach ($projects as $project) {
            // Simple complexity calculation based on tasks and team size
            $taskComplexity = min(10, $project->tasks->count());
            $teamComplexity = min(5, $project->teamMembers->count());
            $projectComplexity = ($taskComplexity + $teamComplexity) / 2;
            
            $totalComplexity += $projectComplexity;
        }
        
        return $totalComplexity / $projects->count();
    }

    private function getTrendingTemplates(): array
    {
        // In a real implementation, this would query actual usage data
        return [
            ['name' => 'Remote Team Setup', 'type' => 'remote', 'complexity' => 'medium', 'trending' => true],
            ['name' => 'AI Integration Project', 'type' => 'ai', 'complexity' => 'high', 'trending' => true],
            ['name' => 'Sustainability Initiative', 'type' => 'sustainability', 'complexity' => 'medium', 'trending' => true],
        ];
    }

    private function getProjectTeamSuggestions(Project $project, User $user): array
    {
        $projectSkills = $this->extractSkillsFromProject($project);
        $currentTeam = $project->teamMembers->pluck('id')->toArray();
        
        $suggestions = [];
        
        // Find users with complementary skills
        $potentialMembers = $this->findUsersWithSkills($projectSkills, $currentTeam);
        
        foreach ($potentialMembers as $member) {
            $suggestions[] = [
                'user_id' => $member['user_id'],
                'name' => $member['name'],
                'skills' => $member['skills'],
                'relevance_score' => $member['relevance'],
                'availability' => $member['availability'],
                'reason' => $member['reason'],
            ];
        }
        
        return $suggestions;
    }

    private function findUsersWithSkills(array $requiredSkills, array $excludeIds): array
    {
        // Simplified user search - in a real implementation, this would query the database
        $potentialUsers = [];
        
        // This is a placeholder - real implementation would search actual users
        for ($i = 1; $i <= 5; $i++) {
            if (!in_array($i, $excludeIds)) {
                $potentialUsers[] = [
                    'user_id' => $i,
                    'name' => "User {$i}",
                    'skills' => array_slice($requiredSkills, 0, 2),
                    'relevance' => rand(60, 95),
                    'availability' => rand(0, 1) ? 'available' : 'busy',
                    'reason' => 'Has complementary skills for the project',
                ];
            }
        }
        
        return $potentialUsers;
    }

    private function deduplicateAndSortSuggestions(array $suggestions): array
    {
        $unique = [];
        $seen = [];
        
        foreach ($suggestions as $suggestion) {
            $key = $suggestion['user_id'];
            
            if (!isset($seen[$key])) {
                $unique[] = $suggestion;
                $seen[$key] = true;
            }
        }
        
        // Sort by relevance score
        usort($unique, function ($a, $b) {
            return $b['relevance_score'] <=> $a['relevance_score'];
        });
        
        return $unique;
    }

    private function analyzeProjectRequirements(User $user): array
    {
        $projects = $user->projects;
        $requirements = [];
        
        foreach ($projects as $project) {
            $projectRequirements = $this->extractProjectRequirements($project);
            $requirements = array_merge($requirements, $projectRequirements);
        }
        
        return array_unique($requirements);
    }

    private function extractProjectRequirements(Project $project): array
    {
        // Extract skill requirements from project
        return $this->extractSkillsFromProject($project);
    }

    private function identifySkillGaps(array $currentSkills, array $requiredSkills): array
    {
        $gaps = [];
        $currentSkillNames = array_column($currentSkills, 'skill');
        
        foreach ($requiredSkills as $requiredSkill) {
            if (!in_array($requiredSkill, $currentSkillNames)) {
                $gaps[] = [
                    'skill' => $requiredSkill,
                    'current_level' => 'none',
                    'target_level' => 'intermediate',
                    'importance' => 'medium',
                ];
            }
        }
        
        return $gaps;
    }

    private function getLearningResources(string $skill): array
    {
        $resourceMap = [
            'javascript' => [
                ['type' => 'course', 'title' => 'JavaScript Complete Guide', 'provider' => 'Udemy'],
                ['type' => 'tutorial', 'title' => 'MDN JavaScript Tutorial', 'provider' => 'Mozilla'],
            ],
            'python' => [
                ['type' => 'course', 'title' => 'Python for Data Science', 'provider' => 'Coursera'],
                ['type' => 'book', 'title' => 'Python Crash Course', 'provider' => 'O\'Reilly'],
            ],
            'project management' => [
                ['type' => 'certification', 'title' => 'PMP Certification', 'provider' => 'PMI'],
                ['type' => 'course', 'title' => 'Agile Project Management', 'provider' => 'LinkedIn Learning'],
            ],
        ];
        
        return $resourceMap[$skill] ?? [
            ['type' => 'course', 'title' => 'General Learning Path', 'provider' => 'Various'],
        ];
    }

    private function estimateLearningTime(string $skill): string
    {
        $timeMap = [
            'javascript' => '3-6 months',
            'python' => '2-4 months',
            'project management' => '1-3 months',
            'design' => '4-8 months',
        ];
        
        return $timeMap[$skill] ?? '2-6 months';
    }

    private function assessCareerImpact(string $skill): string
    {
        $impactMap = [
            'javascript' => 'High - Essential for web development',
            'python' => 'High - Versatile for data science and automation',
            'project management' => 'High - Critical for leadership roles',
            'design' => 'Medium - Important for product development',
        ];
        
        return $impactMap[$skill] ?? 'Medium - Valuable addition to skillset';
    }

    private function analyzeUserWorkflows(User $user): array
    {
        // Analyze user's typical workflows and patterns
        return [
            'task_creation' => [
                'frequency' => 'high',
                'efficiency' => 'medium',
                'tools_used' => ['manual', 'templates'],
            ],
            'team_communication' => [
                'frequency' => 'high',
                'efficiency' => 'low',
                'tools_used' => ['chat', 'email'],
            ],
            'progress_tracking' => [
                'frequency' => 'medium',
                'efficiency' => 'high',
                'tools_used' => ['dashboard', 'reports'],
            ],
        ];
    }

    private function identifyWorkflowInefficiencies(array $workflows): array
    {
        $inefficiencies = [];
        
        foreach ($workflows as $area => $workflow) {
            if ($workflow['efficiency'] === 'low') {
                $inefficiencies[] = [
                    'area' => $area,
                    'current_process' => $workflow['tools_used'],
                    'issue' => 'Low efficiency detected',
                    'solution' => $this->suggestWorkflowImprovement($area),
                    'improvement' => '30-50% efficiency gain',
                    'difficulty' => 'medium',
                    'tools' => $this->recommendWorkflowTools($area),
                ];
            }
        }
        
        return $inefficiencies;
    }

    private function suggestWorkflowImprovement(string $area): string
    {
        $improvements = [
            'task_creation' => 'Use task templates and automation',
            'team_communication' => 'Implement structured communication channels',
            'progress_tracking' => 'Add automated reporting and alerts',
        ];
        
        return $improvements[$area] ?? 'Review and optimize current process';
    }

    private function recommendWorkflowTools(string $area): array
    {
        $tools = [
            'task_creation' => ['Task templates', 'Automation rules', 'Bulk operations'],
            'team_communication' => ['Slack integration', 'Structured channels', 'Meeting scheduler'],
            'progress_tracking' => ['Automated reports', 'Dashboard widgets', 'Alert system'],
        ];
        
        return $tools[$area] ?? ['Process optimization tools'];
    }

    private function buildUserContext(User $user): array
    {
        return [
            'skills' => $this->extractUserSkills($user),
            'industry' => $this->detectUserIndustry($user),
            'project_types' => $this->getUserProjectTypes($user),
            'team_size' => $this->getAverageTeamSize($user),
            'experience_level' => $this->assessExperienceLevel($user),
        ];
    }

    private function getUserProjectTypes(User $user): array
    {
        $projects = $user->projects;
        $types = [];
        
        foreach ($projects as $project) {
            $types[] = $project->category;
        }
        
        return array_unique($types);
    }

    private function getAverageTeamSize(User $user): int
    {
        $projects = $user->projects;
        
        if ($projects->isEmpty()) return 1;
        
        $totalTeamSize = $projects->sum(function ($project) {
            return $project->teamMembers->count();
        });
        
        return (int)($totalTeamSize / $projects->count());
    }

    private function assessExperienceLevel(User $user): string
    {
        $projectCount = $user->projects->count();
        
        if ($projectCount >= 20) return 'expert';
        if ($projectCount >= 5) return 'intermediate';
        return 'beginner';
    }

    private function recommendProductivityTools(array $context): array
    {
        $tools = [];
        
        // Based on skills
        foreach ($context['skills'] as $skill) {
            $skillTools = $this->getToolsForSkill($skill['skill']);
            $tools = array_merge($tools, $skillTools);
        }
        
        return $tools;
    }

    private function getToolsForSkill(string $skill): array
    {
        $toolMap = [
            'javascript' => [
                ['name' => 'VS Code', 'type' => 'editor', 'benefit' => 'Advanced JavaScript support'],
                ['name' => 'ESLint', 'type' => 'linter', 'benefit' => 'Code quality improvement'],
            ],
            'python' => [
                ['name' => 'PyCharm', 'type' => 'ide', 'benefit' => 'Comprehensive Python development'],
                ['name' => 'Jupyter', 'type' => 'notebook', 'benefit' => 'Interactive development'],
            ],
        ];
        
        return $toolMap[$skill] ?? [];
    }

    private function recommendLearningMaterials(array $context): array
    {
        return [
            ['type' => 'book', 'title' => 'Project Management Best Practices', 'relevance' => 'high'],
            ['type' => 'course', 'title' => 'Advanced Team Collaboration', 'relevance' => 'medium'],
        ];
    }

    private function recommendTemplatesAndAssets(array $context): array
    {
        return [
            ['type' => 'template', 'name' => 'Project Kickoff Template', 'relevance' => 'high'],
            ['type' => 'asset', 'name' => 'Team Charter Document', 'relevance' => 'medium'],
        ];
    }

    private function recommendIntegrations(array $context): array
    {
        return [
            ['service' => 'Slack', 'benefit' => 'Real-time team communication'],
            ['service' => 'GitHub', 'benefit' => 'Code version control'],
            ['service' => 'Google Drive', 'benefit' => 'Document collaboration'],
        ];
    }

    private function getBestPracticesForType(string $type): array
    {
        $practices = [
            'software' => [
                ['practice' => 'Use version control', 'impact' => 'High'],
                ['practice' => 'Implement automated testing', 'impact' => 'High'],
                ['practice' => 'Follow coding standards', 'impact' => 'Medium'],
            ],
            'marketing' => [
                ['practice' => 'Define clear KPIs', 'impact' => 'High'],
                ['practice' => 'Use A/B testing', 'impact' => 'Medium'],
                ['practice' => 'Regular performance reviews', 'impact' => 'Medium'],
            ],
        ];
        
        return $practices[$type] ?? [
            ['practice' => 'Set clear goals', 'impact' => 'High'],
            ['practice' => 'Regular team communication', 'impact' => 'High'],
        ];
    }

    private function personalizeBestPractices(array $practices, User $user): array
    {
        // Personalize based on user's current performance and needs
        $personalized = [];
        
        foreach ($practices as $practice) {
            $personalized[] = array_merge($practice, [
                'personalized_reason' => $this->generatePersonalizedReason($practice, $user),
                'implementation_priority' => $this->assessImplementationPriority($practice, $user),
            ]);
        }
        
        return $personalized;
    }

    private function generatePersonalizedReason(array $practice, User $user): string
    {
        return "Based on your project patterns, this practice could significantly improve your outcomes";
    }

    private function assessImplementationPriority(array $practice, User $user): string
    {
        return $practice['impact'] === 'High' ? 'immediate' : 'short-term';
    }

    private function getUserCurrentTools(User $user): array
    {
        // In a real implementation, this would track actual tool usage
        return ['basic_project_management', 'email', 'chat'];
    }

    private function analyzeProjectToolNeeds(User $user): array
    {
        $projects = $user->projects;
        $needs = [];
        
        foreach ($projects as $project) {
            $projectNeeds = $this->analyzeProjectToolRequirements($project);
            $needs = array_merge($needs, $projectNeeds);
        }
        
        return array_unique($needs);
    }

    private function analyzeProjectToolRequirements(Project $project): array
    {
        $requirements = [];
        
        // Based on project complexity and type
        if ($project->teamMembers->count() > 5) {
            $requirements[] = 'advanced_collaboration';
        }
        
        if ($project->tasks->count() > 20) {
            $requirements[] = 'advanced_task_management';
        }
        
        return $requirements;
    }

    private function identifyToolGaps(array $currentTools, array $needs): array
    {
        $gaps = [];
        
        foreach ($needs as $need) {
            if (!in_array($need, $currentTools)) {
                $gaps[] = [
                    'category' => $need,
                    'tools' => $this->getToolsForCategory($need),
                    'benefits' => $this->getCategoryBenefits($need),
                    'integrations' => $this->getCategoryIntegrations($need),
                    'cost' => 'free',
                    'learning_curve' => 'medium',
                ];
            }
        }
        
        return $gaps;
    }

    private function getToolsForCategory(string $category): array
    {
        $toolMap = [
            'advanced_collaboration' => ['Slack', 'Microsoft Teams', 'Discord'],
            'advanced_task_management' => ['Jira', 'Asana', 'Monday.com'],
            'version_control' => ['Git', 'GitHub', 'GitLab'],
        ];
        
        return $toolMap[$category] ?? ['Generic tools'];
    }

    private function getCategoryBenefits(string $category): array
    {
        $benefits = [
            'advanced_collaboration' => ['Real-time communication', 'File sharing', 'Integration'],
            'advanced_task_management' => ['Advanced tracking', 'Automation', 'Reporting'],
            'version_control' => ['Code history', 'Collaboration', 'Backup'],
        ];
        
        return $benefits[$category] ?? ['Improved productivity'];
    }

    private function getCategoryIntegrations(string $category): array
    {
        $integrations = [
            'advanced_collaboration' => ['Google Drive', 'Dropbox', 'Calendar'],
            'advanced_task_management' => ['Email', 'Calendar', 'Time tracking'],
            'version_control' => ['CI/CD', 'Project management', 'Code review'],
        ];
        
        return $integrations[$category] ?? ['Standard integrations'];
    }

    private function inferCareerGoals(User $user): array
    {
        $skills = $this->extractUserSkills($user);
        $projects = $user->projects;
        
        $goals = [];
        
        // Infer goals from skills and project patterns
        if ($this->hasLeadershipSkills($skills)) {
            $goals[] = 'team_leadership';
        }
        
        if ($this->hasTechnicalSkills($skills)) {
            $goals[] = 'technical_expert';
        }
        
        if ($this->hasManagementSkills($skills)) {
            $goals[] = 'project_management';
        }
        
        return $goals ?: ['general_growth'];
    }

    private function hasLeadershipSkills(array $skills): bool
    {
        $leadershipKeywords = ['leadership', 'management', 'communication', 'team'];
        $skillNames = array_column($skills, 'skill');
        
        foreach ($leadershipKeywords as $keyword) {
            foreach ($skillNames as $skill) {
                if (str_contains(strtolower($skill), $keyword)) {
                    return true;
                }
            }
        }
        
        return false;
    }

    private function hasTechnicalSkills(array $skills): bool
    {
        $technicalKeywords = ['javascript', 'python', 'react', 'database', 'api'];
        $skillNames = array_column($skills, 'skill');
        
        foreach ($technicalKeywords as $keyword) {
            if (in_array($keyword, $skillNames)) {
                return true;
            }
        }
        
        return false;
    }

    private function hasManagementSkills(array $skills): bool
    {
        $managementKeywords = ['project management', 'agile', 'scrum', 'planning'];
        $skillNames = array_column($skills, 'skill');
        
        foreach ($managementKeywords as $keyword) {
            if (in_array($keyword, $skillNames)) {
                return true;
            }
        }
        
        return false;
    }

    private function getIndustryTrends(User $user): array
    {
        $industry = $this->detectUserIndustry($user);
        
        $trends = [
            'technology' => ['AI/ML', 'Cloud computing', 'DevOps', 'Cybersecurity'],
            'healthcare' => ['Telemedicine', 'Health informatics', 'AI diagnostics'],
            'finance' => ['Fintech', 'Blockchain', 'Digital banking', 'RegTech'],
            'general' => ['Remote work', 'Digital transformation', 'Sustainability'],
        ];
        
        return $trends[$industry] ?? $trends['general'];
    }

    private function buildLearningPath(string $goal, array $currentSkills, array $trends): array
    {
        $path = [
            'goal' => $goal,
            'current_level' => $this->assessCurrentLevel($goal, $currentSkills),
            'target_level' => 'expert',
            'estimated_duration' => '6-12 months',
            'modules' => $this->generateLearningModules($goal, $currentSkills, $trends),
            'milestones' => $this->generateMilestones($goal),
            'resources' => $this->getPathResources($goal),
        ];
        
        return $path;
    }

    private function assessCurrentLevel(string $goal, array $currentSkills): string
    {
        // Simplified level assessment
        $relevantSkills = $this->getRelevantSkillsForGoal($goal);
        $skillNames = array_column($currentSkills, 'skill');
        
        $hasRelevantSkills = false;
        foreach ($relevantSkills as $skill) {
            if (in_array($skill, $skillNames)) {
                $hasRelevantSkills = true;
                break;
            }
        }
        
        return $hasRelevantSkills ? 'intermediate' : 'beginner';
    }

    private function getRelevantSkillsForGoal(string $goal): array
    {
        $goalSkills = [
            'team_leadership' => ['leadership', 'communication', 'management'],
            'technical_expert' => ['javascript', 'python', 'database', 'api'],
            'project_management' => ['project management', 'agile', 'scrum', 'planning'],
        ];
        
        return $goalSkills[$goal] ?? ['general'];
    }

    private function generateLearningModules(string $goal, array $currentSkills, array $trends): array
    {
        $modules = [
            [
                'title' => 'Foundation Skills',
                'duration' => '1-2 months',
                'topics' => $this->getFoundationTopics($goal),
            ],
            [
                'title' => 'Advanced Concepts',
                'duration' => '2-4 months',
                'topics' => $this->getAdvancedTopics($goal),
            ],
            [
                'title' => 'Industry Trends',
                'duration' => '1-2 months',
                'topics' => $trends,
            ],
        ];
        
        return $modules;
    }

    private function getFoundationTopics(string $goal): array
    {
        $topics = [
            'team_leadership' => ['Communication basics', 'Team dynamics', 'Motivation'],
            'technical_expert' => ['Core concepts', 'Best practices', 'Tools'],
            'project_management' => ['Planning fundamentals', 'Risk management', 'Stakeholder management'],
        ];
        
        return $topics[$goal] ?? ['General topics'];
    }

    private function getAdvancedTopics(string $goal): array
    {
        $topics = [
            'team_leadership' => ['Advanced leadership', 'Conflict resolution', 'Strategic thinking'],
            'technical_expert' => ['Advanced techniques', 'Architecture', 'Optimization'],
            'project_management' => ['Advanced methodologies', 'Portfolio management', 'Metrics'],
        ];
        
        return $topics[$goal] ?? ['Advanced topics'];
    }

    private function generateMilestones(string $goal): array
    {
        return [
            ['milestone' => 'Complete foundation module', 'timeline' => '2 months'],
            ['milestone' => 'Complete advanced module', 'timeline' => '6 months'],
            ['milestone' => 'Complete trends module', 'timeline' => '8 months'],
            ['milestone' => 'Achieve expert level', 'timeline' => '12 months'],
        ];
    }

    private function getPathResources(string $goal): array
    {
        return [
            'courses' => $this->getRecommendedCourses($goal),
            'books' => $this->getRecommendedBooks($goal),
            'tools' => $this->getRecommendedTools($goal),
            'communities' => $this->getRecommendedCommunities($goal),
        ];
    }

    private function getRecommendedCourses(string $goal): array
    {
        $courses = [
            'team_leadership' => ['Leadership Essentials', 'Team Management'],
            'technical_expert' => ['Advanced Technical Skills', 'Industry Best Practices'],
            'project_management' => ['PMP Certification', 'Agile methodologies'],
        ];
        
        return $courses[$goal] ?? ['General courses'];
    }

    private function getRecommendedBooks(string $goal): array
    {
        $books = [
            'team_leadership' => ['The 7 Habits', 'Leadership in Turbulent Times'],
            'technical_expert' => ['Clean Code', 'The Pragmatic Programmer'],
            'project_management' => ['The Phoenix Project', 'Agile Estimating'],
        ];
        
        return $books[$goal] ?? ['General books'];
    }

    private function getRecommendedTools(string $goal): array
    {
        $tools = [
            'team_leadership' => ['Team assessment tools', 'Communication platforms'],
            'technical_expert' => ['Development environments', 'Testing tools'],
            'project_management' => ['Project management software', 'Reporting tools'],
        ];
        
        return $tools[$goal] ?? ['General tools'];
    }

    private function getRecommendedCommunities(string $goal): array
    {
        $communities = [
            'team_leadership' => ['Leadership forums', 'Management groups'],
            'technical_expert' => ['Technical communities', 'Developer groups'],
            'project_management' => ['PMI chapters', 'Agile groups'],
        ];
        
        return $communities[$goal] ?? ['General communities'];
    }
}
