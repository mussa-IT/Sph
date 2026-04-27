<?php

namespace App\Jobs;

use App\Models\ChatSession;
use App\Models\Project;
use App\Models\User;
use App\Services\AIServiceInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class AutoCreateProjectPrototypeJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;

    public function __construct(
        public int $chatSessionId,
        public int $userId
    ) {
    }

    public function handle(AIServiceInterface $aiService): void
    {
        $chatSession = ChatSession::query()->find($this->chatSessionId);
        $user = User::query()->find($this->userId);

        if (!$chatSession || !$user) {
            return;
        }

        // Check if user already has projects from this session
        $existingProject = Project::query()
            ->where('user_id', $this->userId)
            ->where('chat_session_id', $this->chatSessionId)
            ->first();

        if ($existingProject) {
            return;
        }

        try {
            // Analyze conversation to extract project idea
            $conversationContext = $this->buildConversationContext($chatSession);
            $projectIdea = $this->extractProjectIdea($conversationContext);

            if (!$projectIdea) {
                Log::info('No clear project idea found in conversation', [
                    'session_id' => $this->chatSessionId,
                    'message_count' => count($conversationContext['recent_messages'])
                ]);
                return;
            }

            // Generate project details using AI
            $analysis = $aiService->analyzeProjectIdea($projectIdea);
            
            // Create the project prototype
            $project = $user->projects()->create([
                'title' => $this->generateProjectTitle($projectIdea, $conversationContext),
                'description' => $analysis['idea_summary'],
                'status' => 'prototype',
                'chat_session_id' => $this->chatSessionId,
                'project_type' => $analysis['project_type'],
                'difficulty' => $analysis['difficulty'],
                'estimated_timeline_weeks' => $analysis['estimated_timeline_weeks'],
                'confidence_score' => $analysis['confidence_score'],
                'feasibility_score' => $analysis['feasibility_score'],
                'budget_data' => $analysis['estimated_budget'],
                'tools_data' => $analysis['tools_list'],
                'step_by_step_plan' => $analysis['step_by_step_plan'],
                'success_factors' => $analysis['success_factors'],
                'next_actions' => $analysis['next_actions'],
            ]);

            Log::info('Auto-created project prototype', [
                'project_id' => $project->id,
                'session_id' => $this->chatSessionId,
                'user_id' => $this->userId,
                'project_type' => $analysis['project_type']
            ]);

        } catch (Throwable $exception) {
            Log::error('Failed to auto-create project prototype', [
                'session_id' => $this->chatSessionId,
                'user_id' => $this->userId,
                'error' => $exception->getMessage()
            ]);
        }
    }

    private function buildConversationContext(ChatSession $chatSession): array
    {
        $recentMessages = $chatSession->messages()
            ->latest('created_at')
            ->take(20) // Take more messages for better analysis
            ->get(['sender', 'message', 'created_at'])
            ->map(function ($message) {
                return [
                    'sender' => $message->sender,
                    'message' => $message->message,
                    'timestamp' => $message->created_at->toISOString(),
                ];
            })
            ->reverse()
            ->toArray();

        return [
            'session_id' => $chatSession->id,
            'session_title' => $chatSession->title,
            'message_count' => $chatSession->messages()->count(),
            'recent_messages' => $recentMessages,
        ];
    }

    private function extractProjectIdea(array $context): ?string
    {
        $messages = array_filter($context['recent_messages'], fn($msg) => $msg['sender'] === 'user');
        
        if (count($messages) < 3) {
            return null; // Need at least 3 user messages to extract idea
        }

        // Look for key project indicators
        $projectKeywords = [
            'build', 'create', 'develop', 'make', 'design', 'launch', 'start',
            'app', 'website', 'platform', 'system', 'tool', 'service',
            'project', 'business', 'startup', 'company', 'product',
            'tengeneza', 'jenga', 'unda', 'tengeneza', 'anzisha', 'mradi'
        ];

        $conversationText = '';
        foreach ($messages as $message) {
            $conversationText .= ' ' . strtolower($message['message']);
        }

        $hasProjectKeywords = false;
        foreach ($projectKeywords as $keyword) {
            if (str_contains($conversationText, $keyword)) {
                $hasProjectKeywords = true;
                break;
            }
        }

        if (!$hasProjectKeywords) {
            return null;
        }

        // Extract the main idea from the first few user messages
        $ideaMessages = array_slice($messages, 0, 3);
        $ideaParts = [];
        
        foreach ($ideaMessages as $message) {
            $cleanMessage = preg_replace('/\b(hello|hi|hey|habari|mambo)\b/i', '', $message['message']);
            $cleanMessage = preg_replace('/\b(help|please|assist|nisaidie|naomba)\b/i', '', $cleanMessage);
            $cleanMessage = trim($cleanMessage);
            
            if (strlen($cleanMessage) > 10) {
                $ideaParts[] = $cleanMessage;
            }
        }

        return !empty($ideaParts) ? implode(' ', $ideaParts) : null;
    }

    private function generateProjectTitle(string $projectIdea, array $context): string
    {
        // Extract key concepts for title
        $words = explode(' ', strtolower($projectIdea));
        $titleWords = [];

        $importantWords = [
            'app', 'website', 'platform', 'system', 'tool', 'service', 'portal',
            'ecommerce', 'dashboard', 'mobile', 'web', 'api', 'software',
            'programu', 'tovuti', 'mfumo', 'jukwaa', 'bidhaa'
        ];

        foreach ($words as $word) {
            if (strlen($word) > 3 && in_array($word, $importantWords)) {
                $titleWords[] = ucfirst($word);
            }
        }

        if (empty($titleWords)) {
            // Fallback to session title or generate generic title
            return !empty($context['session_title']) 
                ? $context['session_title'] . ' Project'
                : 'Project Prototype';
        }

        $baseTitle = implode(' ', array_slice($titleWords, 0, 2));
        
        // Add user's name or session identifier
        $identifier = !empty($context['session_title']) 
            ? $context['session_title']
            : 'Auto-Generated';

        return "{$baseTitle} - {$identifier}";
    }
}
