<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Task;
use App\Models\Budget;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class AIAutopilotService
{
    private string $apiKey;
    private string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key');
        $this->baseUrl = 'https://api.openai.com/v1';
    }

    public function generateProjectFromPrompt(string $prompt, User $user): array
    {
        $response = $this->callOpenAI([
            'model' => 'gpt-4',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are an expert project manager and AI assistant. Generate a complete project structure based on the user\'s prompt. Return a structured JSON response with project details, tasks, and budget estimates.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'functions' => [
                [
                    'name' => 'create_project',
                    'description' => 'Create a complete project structure',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'title' => ['type' => 'string'],
                            'description' => ['type' => 'string'],
                            'category' => ['type' => 'string'],
                            'estimated_duration' => ['type' => 'string'],
                            'complexity' => ['type' => 'string', 'enum' => ['simple', 'medium', 'complex']],
                            'budget_estimate' => ['type' => 'number'],
                            'currency' => ['type' => 'string', 'default' => 'USD'],
                            'tasks' => [
                                'type' => 'array',
                                'items' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'title' => ['type' => 'string'],
                                        'description' => ['type' => 'string'],
                                        'priority' => ['type' => 'string', 'enum' => ['low', 'medium', 'high']],
                                        'estimated_duration' => ['type' => 'string'],
                                        'dependencies' => ['type' => 'array', 'items' => ['type' => 'string']],
                                        'skills_required' => ['type' => 'array', 'items' => ['type' => 'string']],
                                    ]
                                ]
                            ],
                            'milestones' => [
                                'type' => 'array',
                                'items' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'title' => ['type' => 'string'],
                                        'description' => ['type' => 'string'],
                                        'due_date' => ['type' => 'string'],
                                        'tasks' => ['type' => 'array', 'items' => ['type' => 'string']],
                                    ]
                                ]
                            ],
                            'risks' => [
                                'type' => 'array',
                                'items' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'description' => ['type' => 'string'],
                                        'probability' => ['type' => 'string', 'enum' => ['low', 'medium', 'high']],
                                        'impact' => ['type' => 'string', 'enum' => ['low', 'medium', 'high']],
                                        'mitigation' => ['type' => 'string'],
                                    ]
                                ]
                            ],
                            'resources' => [
                                'type' => 'array',
                                'items' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'name' => ['type' => 'string'],
                                        'type' => ['type' => 'string', 'enum' => ['human', 'tool', 'software']],
                                        'quantity' => ['type' => 'integer'],
                                        'cost_estimate' => ['type' => 'number'],
                                    ]
                                ]
                            ]
                        ],
                        'required' => ['title', 'description', 'category', 'estimated_duration', 'complexity', 'budget_estimate', 'tasks']
                    ]
                ]
            ],
            'function_call' => 'auto'
        ]);

        $data = json_decode($response->getBody(), true);
        
        if (isset($data['choices'][0]['message']['function_call']['arguments'])) {
            $projectData = json_decode($data['choices'][0]['message']['function_call']['arguments'], true);
            return $this->createProjectFromData($projectData, $user);
        }

        throw new \Exception('Failed to generate project structure');
    }

    public function optimizeProject(Project $project): array
    {
        $projectData = [
            'title' => $project->title,
            'description' => $project->description,
            'current_tasks' => $project->tasks()->get()->map(function ($task) {
                return [
                    'title' => $task->title,
                    'description' => $task->description,
                    'status' => $task->status,
                    'priority' => $task->priority,
                ];
            })->toArray(),
            'current_progress' => $project->progress,
        ];

        $response = $this->callOpenAI([
            'model' => 'gpt-4',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are an expert project manager. Analyze the current project and provide optimization suggestions, task reorganization, and risk mitigation strategies.'
                ],
                [
                    'role' => 'user',
                    'content' => json_encode($projectData)
                ]
            ],
            'functions' => [
                [
                    'name' => 'optimize_project',
                    'description' => 'Provide project optimization recommendations',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'recommendations' => [
                                'type' => 'array',
                                'items' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'type' => ['type' => 'string', 'enum' => ['task_reorganization', 'risk_mitigation', 'resource_optimization', 'timeline_adjustment']],
                                        'description' => ['type' => 'string'],
                                        'priority' => ['type' => 'string', 'enum' => ['low', 'medium', 'high']],
                                        'impact' => ['type' => 'string', 'enum' => ['low', 'medium', 'high']],
                                    ]
                                ]
                            ],
                            'suggested_tasks' => [
                                'type' => 'array',
                                'items' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'title' => ['type' => 'string'],
                                        'description' => ['type' => 'string'],
                                        'priority' => ['type' => 'string', 'enum' => ['low', 'medium', 'high']],
                                        'estimated_duration' => ['type' => 'string'],
                                    ]
                                ]
                            ],
                            'timeline_adjustments' => [
                                'type' => 'array',
                                'items' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'task_id' => ['type' => 'integer'],
                                        'new_duration' => ['type' => 'string'],
                                        'reason' => ['type' => 'string'],
                                    ]
                                ]
                            ],
                            'risk_assessment' => [
                                'type' => 'array',
                                'items' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'risk' => ['type' => 'string'],
                                        'probability' => ['type' => 'string', 'enum' => ['low', 'medium', 'high']],
                                        'impact' => ['type' => 'string', 'enum' => ['low', 'medium', 'high']],
                                        'mitigation' => ['type' => 'string'],
                                    ]
                                ]
                            ]
                        ],
                        'required' => ['recommendations']
                    ]
                ]
            ],
            'function_call' => 'auto'
        ]);

        $data = json_decode($response->getBody(), true);
        
        if (isset($data['choices'][0]['message']['function_call']['arguments'])) {
            return json_decode($data['choices'][0]['message']['function_call']['arguments'], true);
        }

        throw new \Exception('Failed to optimize project');
    }

    public function generateTasksForProject(Project $project, string $additionalContext = ''): Collection
    {
        $context = [
            'project_title' => $project->title,
            'project_description' => $project->description,
            'existing_tasks' => $project->tasks()->get()->map(function ($task) {
                return [
                    'title' => $task->title,
                    'description' => $task->description,
                    'status' => $task->status,
                ];
            })->toArray(),
            'project_progress' => $project->progress,
            'additional_context' => $additionalContext,
        ];

        $response = $this->callOpenAI([
            'model' => 'gpt-4',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are an expert project manager. Generate detailed tasks for the given project based on the current state and context. Tasks should be specific, actionable, and properly prioritized.'
                ],
                [
                    'role' => 'user',
                    'content' => json_encode($context)
                ]
            ],
            'functions' => [
                [
                    'name' => 'generate_tasks',
                    'description' => 'Generate tasks for a project',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'tasks' => [
                                'type' => 'array',
                                'items' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'title' => ['type' => 'string'],
                                        'description' => ['type' => 'string'],
                                        'priority' => ['type' => 'string', 'enum' => ['low', 'medium', 'high']],
                                        'estimated_duration' => ['type' => 'string'],
                                        'dependencies' => ['type' => 'array', 'items' => ['type' => 'string']],
                                        'skills_required' => ['type' => 'array', 'items' => ['type' => 'string']],
                                        'acceptance_criteria' => ['type' => 'array', 'items' => ['type' => 'string']],
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'function_call' => 'auto'
        ]);

        $data = json_decode($response->getBody(), true);
        
        if (isset($data['choices'][0]['message']['function_call']['arguments'])) {
            $tasksData = json_decode($data['choices'][0]['message']['function_call']['arguments'], true);
            return collect($tasksData['tasks']);
        }

        throw new \Exception('Failed to generate tasks');
    }

    public function generateBudgetEstimate(Project $project): array
    {
        $projectData = [
            'title' => $project->title,
            'description' => $project->description,
            'tasks' => $project->tasks()->get()->map(function ($task) {
                return [
                    'title' => $task->title,
                    'description' => $task->description,
                    'priority' => $task->priority,
                ];
            })->toArray(),
            'complexity' => $this->assessProjectComplexity($project),
        ];

        $response = $this->callOpenAI([
            'model' => 'gpt-4',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are an expert project estimator. Generate a detailed budget estimate for the project including costs for labor, tools, materials, and contingency.'
                ],
                [
                    'role' => 'user',
                    'content' => json_encode($projectData)
                ]
            ],
            'functions' => [
                [
                    'name' => 'estimate_budget',
                    'description' => 'Generate a detailed budget estimate',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'total_estimate' => ['type' => 'number'],
                            'currency' => ['type' => 'string', 'default' => 'USD'],
                            'breakdown' => [
                                'type' => 'array',
                                'items' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'category' => ['type' => 'string'],
                                        'description' => ['type' => 'string'],
                                        'estimated_cost' => ['type' => 'number'],
                                        'confidence_level' => ['type' => 'string', 'enum' => ['low', 'medium', 'high']],
                                    ]
                                ]
                            ],
                            'contingency_percentage' => ['type' => 'number'],
                            'timeline_months' => ['type' => 'number'],
                            'risk_factors' => [
                                'type' => 'array',
                                'items' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'factor' => ['type' => 'string'],
                                        'impact' => ['type' => 'string', 'enum' => ['low', 'medium', 'high']],
                                        'mitigation' => ['type' => 'string'],
                                    ]
                                ]
                            ]
                        ],
                        'required' => ['total_estimate', 'breakdown']
                    ]
                ]
            ],
            'function_call' => 'auto'
        ]);

        $data = json_decode($response->getBody(), true);
        
        if (isset($data['choices'][0]['message']['function_call']['arguments'])) {
            return json_decode($data['choices'][0]['message']['function_call']['arguments'], true);
        }

        throw new \Exception('Failed to generate budget estimate');
    }

    public function generateProjectReport(Project $project): array
    {
        $projectData = [
            'title' => $project->title,
            'description' => $project->description,
            'progress' => $project->progress,
            'status' => $project->status,
            'created_at' => $project->created_at,
            'tasks' => $project->tasks()->get()->map(function ($task) {
                return [
                    'title' => $task->title,
                    'status' => $task->status,
                    'priority' => $task->priority,
                    'completed_at' => $task->completed_at,
                ];
            })->toArray(),
            'budgets' => $project->budgets()->get()->map(function ($budget) {
                return [
                    'amount' => $budget->amount,
                    'spent' => $budget->spent,
                    'currency' => $budget->currency,
                ];
            })->toArray(),
        ];

        $response = $this->callOpenAI([
            'model' => 'gpt-4',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are an expert project analyst. Generate a comprehensive project report including progress analysis, performance metrics, and recommendations.'
                ],
                [
                    'role' => 'user',
                    'content' => json_encode($projectData)
                ]
            ],
            'functions' => [
                [
                    'name' => 'generate_report',
                    'description' => 'Generate a comprehensive project report',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'executive_summary' => ['type' => 'string'],
                            'progress_analysis' => [
                                'type' => 'object',
                                'properties' => [
                                    'overall_progress' => ['type' => 'number'],
                                    'task_completion_rate' => ['type' => 'number'],
                                    'budget_utilization' => ['type' => 'number'],
                                    'timeline_adherence' => ['type' => 'string'],
                                ]
                            ],
                            'key_metrics' => [
                                'type' => 'array',
                                'items' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'metric' => ['type' => 'string'],
                                        'value' => ['type' => 'string'],
                                        'status' => ['type' => 'string', 'enum' => ['on_track', 'at_risk', 'behind']],
                                    ]
                                ]
                            ],
                            'achievements' => ['type' => 'array', 'items' => ['type' => 'string']],
                            'challenges' => ['type' => 'array', 'items' => ['type' => 'string']],
                            'recommendations' => [
                                'type' => 'array',
                                'items' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'action' => ['type' => 'string'],
                                        'priority' => ['type' => 'string', 'enum' => ['low', 'medium', 'high']],
                                        'impact' => ['type' => 'string'],
                                        'timeline' => ['type' => 'string'],
                                    ]
                                ]
                            ],
                            'next_steps' => ['type' => 'array', 'items' => ['type' => 'string']],
                        ],
                        'required' => ['executive_summary', 'progress_analysis', 'key_metrics', 'recommendations']
                    ]
                ]
            ],
            'function_call' => 'auto'
        ]);

        $data = json_decode($response->getBody(), true);
        
        if (isset($data['choices'][0]['message']['function_call']['arguments'])) {
            return json_decode($data['choices'][0]['message']['function_call']['arguments'], true);
        }

        throw new \Exception('Failed to generate project report');
    }

    private function createProjectFromData(array $projectData, User $user): array
    {
        $project = Project::create([
            'title' => $projectData['title'],
            'description' => $projectData['description'],
            'status' => 'planning',
            'visibility' => 'private',
            'user_id' => $user->id,
        ]);

        // Create tasks
        $createdTasks = [];
        foreach ($projectData['tasks'] as $taskData) {
            $task = Task::create([
                'title' => $taskData['title'],
                'description' => $taskData['description'],
                'priority' => $taskData['priority'],
                'project_id' => $project->id,
                'assigned_to' => $user->id,
            ]);
            $createdTasks[] = $task;
        }

        // Create budget
        if (isset($projectData['budget_estimate']) && $projectData['budget_estimate'] > 0) {
            Budget::create([
                'amount' => $projectData['budget_estimate'],
                'currency' => $projectData['currency'] ?? 'USD',
                'project_id' => $project->id,
            ]);
        }

        return [
            'project' => $project,
            'tasks' => $createdTasks,
            'milestones' => $projectData['milestones'] ?? [],
            'risks' => $projectData['risks'] ?? [],
            'resources' => $projectData['resources'] ?? [],
        ];
    }

    private function assessProjectComplexity(Project $project): string
    {
        $taskCount = $project->tasks()->count();
        $budgetCount = $project->budgets()->count();
        
        if ($taskCount > 20 || $budgetCount > 5) {
            return 'complex';
        } elseif ($taskCount > 5 || $budgetCount > 1) {
            return 'medium';
        } else {
            return 'simple';
        }
    }

    private function callOpenAI(array $payload): \Illuminate\Http\Client\Response
    {
        return Http::withHeaders([
            'Authorization' => "Bearer {$this->apiKey}",
            'Content-Type' => 'application/json',
        ])->post("{$this->baseUrl}/chat/completions", $payload);
    }
}
