<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Services\AIAutopilotService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\View\View;

class AIAutopilotController extends Controller
{
    private AIAutopilotService $aiService;

    public function __construct(AIAutopilotService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function index(): View
    {
        return view('pages.ai-autopilot.index');
    }

    public function createProject(): View
    {
        return view('pages.ai-autopilot.create');
    }

    public function generateProject(Request $request): JsonResponse
    {
        $request->validate([
            'prompt' => ['required', 'string', 'min:10', 'max:1000'],
            'context' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $result = $this->aiService->generateProjectFromPrompt(
                $request->prompt,
                $request->user()
            );

            return response()->json([
                'success' => true,
                'message' => 'Project generated successfully!',
                'project' => $result['project'],
                'tasks' => $result['tasks'],
                'milestones' => $result['milestones'],
                'risks' => $result['risks'],
                'resources' => $result['resources'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate project: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function optimizeProject(Request $request, Project $project): JsonResponse
    {
        if (!$project->userCanEdit($request->user())) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied.',
            ], 403);
        }

        try {
            $recommendations = $this->aiService->optimizeProject($project);

            return response()->json([
                'success' => true,
                'message' => 'Project optimization completed!',
                'recommendations' => $recommendations['recommendations'],
                'suggested_tasks' => $recommendations['suggested_tasks'] ?? [],
                'timeline_adjustments' => $recommendations['timeline_adjustments'] ?? [],
                'risk_assessment' => $recommendations['risk_assessment'] ?? [],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to optimize project: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function generateTasks(Request $request, Project $project): JsonResponse
    {
        if (!$project->userCanEdit($request->user())) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied.',
            ], 403);
        }

        $request->validate([
            'context' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $tasks = $this->aiService->generateTasksForProject(
                $project,
                $request->context ?? ''
            );

            return response()->json([
                'success' => true,
                'message' => 'Tasks generated successfully!',
                'tasks' => $tasks,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate tasks: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function generateBudget(Request $request, Project $project): JsonResponse
    {
        if (!$project->userCanEdit($request->user())) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied.',
            ], 403);
        }

        try {
            $budget = $this->aiService->generateBudgetEstimate($project);

            return response()->json([
                'success' => true,
                'message' => 'Budget estimate generated successfully!',
                'budget' => $budget,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate budget estimate: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function generateReport(Request $request, Project $project): JsonResponse
    {
        if (!$project->userCanView($request->user())) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied.',
            ], 403);
        }

        try {
            $report = $this->aiService->generateProjectReport($project);

            return response()->json([
                'success' => true,
                'message' => 'Project report generated successfully!',
                'report' => $report,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate project report: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function chat(Request $request): JsonResponse
    {
        $request->validate([
            'message' => ['required', 'string', 'min:1', 'max:1000'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'context' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $project = $request->project_id ? Project::find($request->project_id) : null;
            $context = $this->buildChatContext($project, $request->context);

            $response = $this->aiService->chatWithProject(
                $request->message,
                $context,
                $request->user()
            );

            return response()->json([
                'success' => true,
                'response' => $response,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process chat message: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function analyzeProject(Request $request, Project $project): JsonResponse
    {
        if (!$project->userCanView($request->user())) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied.',
            ], 403);
        }

        try {
            $analysis = $this->aiService->analyzeProjectHealth($project);

            return response()->json([
                'success' => true,
                'message' => 'Project analysis completed!',
                'analysis' => $analysis,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to analyze project: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function suggestNextSteps(Request $request, Project $project): JsonResponse
    {
        if (!$project->userCanView($request->user())) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied.',
            ], 403);
        }

        try {
            $suggestions = $this->aiService->suggestNextSteps($project);

            return response()->json([
                'success' => true,
                'message' => 'Next steps suggestions generated!',
                'suggestions' => $suggestions,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate suggestions: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function autoSchedule(Request $request, Project $project): JsonResponse
    {
        if (!$project->userCanEdit($request->user())) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied.',
            ], 403);
        }

        $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'constraints' => ['nullable', 'array'],
        ]);

        try {
            $schedule = $this->aiService->autoScheduleProject(
                $project,
                $request->start_date,
                $request->end_date,
                $request->constraints ?? []
            );

            return response()->json([
                'success' => true,
                'message' => 'Project auto-scheduled successfully!',
                'schedule' => $schedule,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to auto-schedule project: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function buildChatContext(?Project $project, ?string $additionalContext): array
    {
        $context = [];

        if ($project) {
            $context['project'] = [
                'id' => $project->id,
                'title' => $project->title,
                'description' => $project->description,
                'status' => $project->status,
                'progress' => $project->progress,
                'tasks_count' => $project->tasks()->count(),
                'completed_tasks_count' => $project->tasks()->where('status', 'completed')->count(),
            ];
        }

        if ($additionalContext) {
            $context['additional'] = $additionalContext;
        }

        return $context;
    }
}
