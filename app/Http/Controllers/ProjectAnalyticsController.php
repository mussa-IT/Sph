<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Services\ProjectAnalyticsService;
use App\Services\IntelligentSchedulerService;
use App\Services\RealTimeCollaborationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProjectAnalyticsController extends Controller
{
    private ProjectAnalyticsService $analyticsService;
    private IntelligentSchedulerService $schedulerService;
    private RealTimeCollaborationService $collaborationService;

    public function __construct(
        ProjectAnalyticsService $analyticsService,
        IntelligentSchedulerService $schedulerService,
        RealTimeCollaborationService $collaborationService
    ) {
        $this->analyticsService = $analyticsService;
        $this->schedulerService = $schedulerService;
        $this->collaborationService = $collaborationService;
    }

    public function insights(Request $request, Project $project): JsonResponse
    {
        if (!$project->userCanView($request->user())) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $insights = $this->analyticsService->generateProjectInsights($project);

        return response()->json([
            'success' => true,
            'insights' => $insights,
        ]);
    }

    public function optimizeSchedule(Request $request, Project $project): JsonResponse
    {
        if (!$project->userCanEdit($request->user())) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $request->validate([
            'constraints' => ['nullable', 'array'],
        ]);

        $constraints = $request->constraints ?? [];
        $schedule = $this->schedulerService->optimizeTaskSchedule($project, $constraints);

        return response()->json([
            'success' => true,
            'schedule' => $schedule,
        ]);
    }

    public function collaborationInsights(Request $request, Project $project): JsonResponse
    {
        if (!$project->userCanView($request->user())) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $insights = $this->collaborationService->generateCollaborationInsights($project);

        return response()->json([
            'success' => true,
            'insights' => $insights,
        ]);
    }

    public function teamPerformance(Request $request, Project $project): JsonResponse
    {
        if (!$project->userCanView($request->user())) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $teamPerformance = $this->analyticsService->analyzeTeamProductivity($project);

        return response()->json([
            'success' => true,
            'team_performance' => $teamPerformance,
        ]);
    }

    public function budgetAnalysis(Request $request, Project $project): JsonResponse
    {
        if (!$project->userCanView($request->user())) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $budgetAnalysis = $this->analyticsService->analyzeBudgetPerformance($project);

        return response()->json([
            'success' => true,
            'budget_analysis' => $budgetAnalysis,
        ]);
    }

    public function riskAssessment(Request $request, Project $project): JsonResponse
    {
        if (!$project->userCanView($request->user())) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $riskFactors = $this->analyticsService->identifyRiskFactors($project);

        return response()->json([
            'success' => true,
            'risk_factors' => $riskFactors,
        ]);
    }

    public function timelinePrediction(Request $request, Project $project): JsonResponse
    {
        if (!$project->userCanView($request->user())) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $prediction = $this->analyticsService->predictTimeline($project);

        return response()->json([
            'success' => true,
            'prediction' => $prediction,
        ]);
    }

    public function comparativeAnalysis(Request $request, Project $project): JsonResponse
    {
        if (!$project->userCanView($request->user())) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $comparison = $this->analyticsService->compareToSimilarProjects($project);

        return response()->json([
            'success' => true,
            'comparison' => $comparison,
        ]);
    }

    public function performanceMetrics(Request $request, Project $project): JsonResponse
    {
        if (!$project->userCanView($request->user())) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $metrics = $this->analyticsService->calculatePerformanceMetrics($project);

        return response()->json([
            'success' => true,
            'metrics' => $metrics,
        ]);
    }

    public function criticalPath(Request $request, Project $project): JsonResponse
    {
        if (!$project->userCanView($request->user())) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $tasks = $project->tasks;
        $criticalPath = $this->schedulerService->analyzeCriticalPath($tasks);

        return response()->json([
            'success' => true,
            'critical_path' => $criticalPath,
        ]);
    }

    public function workloadBalance(Request $request, Project $project): JsonResponse
    {
        if (!$project->userCanView($request->user())) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $teamMembers = $project->teamMembers;
        $tasks = $project->tasks;
        $balance = $this->schedulerService->balanceWorkload($teamMembers, $tasks);

        return response()->json([
            'success' => true,
            'workload_balance' => $balance,
        ]);
    }

    public function resourceOptimization(Request $request, Project $project): JsonResponse
    {
        if (!$project->userCanView($request->user())) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $tasks = $project->tasks;
        $teamMembers = $project->teamMembers;
        $optimization = $this->schedulerService->optimizeResources($tasks, $teamMembers);

        return response()->json([
            'success' => true,
            'resource_optimization' => $optimization,
        ]);
    }

    public function dependencyAnalysis(Request $request, Project $project): JsonResponse
    {
        if (!$project->userCanView($request->user())) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $tasks = $project->tasks;
        $dependencies = $this->schedulerService->resolveDependencies($tasks);

        return response()->json([
            'success' => true,
            'dependencies' => $dependencies,
        ]);
    }

    public function recommendations(Request $request, Project $project): JsonResponse
    {
        if (!$project->userCanView($request->user())) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $recommendations = $this->analyticsService->generateRecommendations($project);

        return response()->json([
            'success' => true,
            'recommendations' => $recommendations,
        ]);
    }

    public function healthScore(Request $request, Project $project): JsonResponse
    {
        if (!$project->userCanView($request->user())) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $health = $this->analyticsService->calculateProjectHealth($project);

        return response()->json([
            'success' => true,
            'health' => $health,
        ]);
    }

    public function dashboard(Request $request, Project $project): JsonResponse
    {
        if (!$project->userCanView($request->user())) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $dashboard = [
            'health_score' => $this->analyticsService->calculateProjectHealth($project),
            'team_performance' => $this->analyticsService->analyzeTeamProductivity($project),
            'budget_analysis' => $this->analyticsService->analyzeBudgetPerformance($project),
            'risk_factors' => $this->analyticsService->identifyRiskFactors($project),
            'timeline_prediction' => $this->analyticsService->predictTimeline($project),
            'performance_metrics' => $this->analyticsService->calculatePerformanceMetrics($project),
            'collaboration_insights' => $this->collaborationService->generateCollaborationInsights($project),
        ];

        return response()->json([
            'success' => true,
            'dashboard' => $dashboard,
        ]);
    }
}
