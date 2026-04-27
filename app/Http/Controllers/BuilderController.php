<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnalyzeIdeaRequest;
use App\Services\AIServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Throwable;

class BuilderController extends Controller
{
    public function __construct(private AIServiceInterface $aiService)
    {
    }

    public function analyzeIdea(AnalyzeIdeaRequest $request): JsonResponse
    {
        $idea = (string) $request->input('idea');
        $cacheTtlSeconds = (int) config('services.openai.builder.cache_ttl_seconds', 120);
        $cacheKey = 'ai-builder:' . (Auth::id() ?? 'guest') . ':' . sha1(mb_strtolower(trim($idea)));

        try {
            $payload = Cache::remember(
                $cacheKey,
                now()->addSeconds($cacheTtlSeconds),
                function () use ($idea): array {
                    $analysis = $this->aiService->analyzeProject($idea);
                    $budget = $this->aiService->generateBudget($idea);
                    $tools = $this->aiService->suggestTools($idea);

                    $difficulty = (string) ($analysis['difficulty'] ?? 'Intermediate');

                    return [
                        'project_type' => (string) ($analysis['project_type'] ?? 'Custom Software Solution'),
                        'difficulty' => $difficulty,
                        'feasibility_score' => $this->feasibilityScoreForDifficulty($difficulty),
                        'estimated_budget' => [
                            'currency' => (string) ($budget['currency'] ?? 'USD'),
                            'range' => [
                                'min' => (int) ($budget['minimum_budget'] ?? ($budget['range']['min'] ?? 0)),
                                'max' => (int) ($budget['ideal_budget'] ?? ($budget['range']['max'] ?? 0)),
                            ],
                            'minimum_budget' => (int) ($budget['minimum_budget'] ?? 0),
                            'ideal_budget' => (int) ($budget['ideal_budget'] ?? 0),
                            'estimated_hours' => (int) ($budget['estimated_hours'] ?? 0),
                            'component_cost_breakdown' => $budget['component_cost_breakdown'] ?? [],
                            'cost_saving_alternatives' => $budget['cost_saving_alternatives'] ?? [],
                            'breakdown' => $budget['breakdown'] ?? [],
                        ],
                        'recommended_tools' => [
                            'categories' => $tools['categories'] ?? [],
                            'stack' => $tools['recommended_stack'] ?? [],
                            'primary_tools' => $tools['primary_tools'] ?? [],
                            'cheap_alternatives' => $tools['cheap_alternatives'] ?? [],
                            'free_software_alternatives' => $tools['free_software_alternatives'] ?? [],
                            'diy_options' => $tools['diy_options'] ?? [],
                            'local_sourcing_suggestions' => $tools['local_sourcing_suggestions'] ?? [],
                        ],
                        'alternatives' => $this->buildAlternatives($tools),
                        'steps' => $analysis['step_by_step_plan'] ?? [],
                        'timeline' => [
                            'weeks' => (int) ($analysis['estimated_timeline']['weeks'] ?? 0),
                            'label' => (string) ($analysis['estimated_timeline']['label'] ?? ''),
                        ],
                        'risks' => $analysis['risks'] ?? [],
                    ];
                }
            );

            return response()->json([
                'success' => true,
                'data' => $payload,
                'message' => 'Project idea analyzed successfully',
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'success' => false,
                'message' => 'AI analysis service is temporarily unavailable. Please try again shortly.',
            ], 503);
        }
    }

    private function feasibilityScoreForDifficulty(string $difficulty): int
    {
        return match (strtolower($difficulty)) {
            'beginner' => 92,
            'advanced' => 73,
            default => 84,
        };
    }

    private function buildAlternatives(array $tools): array
    {
        $alternatives = [];
        $categories = $tools['categories'] ?? [];
        foreach ($categories as $categoryBlock) {
            $category = (string) ($categoryBlock['category'] ?? 'General');
            $toolList = array_values($categoryBlock['tools'] ?? []);
            if (count($toolList) >= 2) {
                $alternatives[] = [
                    'category' => $category,
                    'primary' => (string) $toolList[0],
                    'alternative' => (string) $toolList[1],
                ];
            }
        }

        return $alternatives;
    }
}
