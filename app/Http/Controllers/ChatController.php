<?php

namespace App\Http\Controllers;

use App\Jobs\AutoCreateProjectPrototypeJob;
use App\Jobs\GenerateChatReplyJob;
use App\Models\ChatSession;
use App\Models\User;
use App\Services\AIServiceInterface;
use App\Services\ChatMessageSanitizer;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ChatController extends Controller
{
    public function __construct(
        private AIServiceInterface $aiService,
        private ChatMessageSanitizer $sanitizer
    ) {
    }

    public function index(): View
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $sessions = $this->loadUserSessions($user);

        return view('pages.chat', compact('sessions'));
    }

    public function storeSession(Request $request): JsonResponse
    {
        $request->validate([
            'title' => ['required', 'string', 'max:150'],
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $session = $user->chatSessions()->create([
            'title' => $request->input('title'),
        ]);

        return response()->json([
            'success' => true,
            'data' => $session,
            'message' => 'Chat session created successfully.',
        ]);
    }

    public function sendMessage(Request $request, ChatSession $chatSession): JsonResponse
    {
        $this->ensureSessionOwnership($chatSession);

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $cleanUserMessage = $this->sanitizer->sanitize($validated['message']);
        if ($cleanUserMessage === '') {
            throw ValidationException::withMessages([
                'message' => ['Please enter a valid message.'],
            ]);
        }

        $userMessage = $chatSession->messages()->create([
            'sender' => 'user',
            'message' => $cleanUserMessage,
        ]);

        if ((bool) config('services.openai.chat_async', false)) {
            $assistantMessage = $chatSession->messages()->create([
                'sender' => 'ai',
                'message' => 'Assistant is preparing a response...',
            ]);

            GenerateChatReplyJob::dispatch(
                chatSessionId: (int) $chatSession->id,
                userMessageId: (int) $userMessage->id,
                assistantMessageId: (int) $assistantMessage->id,
                context: $this->buildConversationContext($chatSession)
            );
            $chatSession->touch();

            return response()->json([
                'success' => true,
                'data' => [
                    'user_message' => $userMessage,
                    'ai_message' => $assistantMessage,
                    'ai' => null,
                    'queued' => true,
                ],
                'message' => 'Message queued for AI processing.',
            ]);
        }

        $context = $this->buildConversationContext($chatSession);
        $aiResult = $this->aiService->chat($cleanUserMessage, $context);
        $aiReply = $this->sanitizer->sanitize((string) ($aiResult['reply'] ?? ''));
        if ($aiReply === '') {
            $aiReply = 'I could not generate a response right now. Please try again.';
        }

        $assistantMessage = $chatSession->messages()->create([
            'sender' => 'ai',
            'message' => $aiReply,
        ]);
        $chatSession->touch();

        // Trigger auto-creation of project prototype if conditions are met
        $this->checkAndTriggerAutoPrototype($chatSession);

        return response()->json([
            'success' => true,
            'data' => [
                'user_message' => $userMessage,
                'ai_message' => $assistantMessage,
                'ai' => $aiResult,
                'queued' => false,
            ],
            'message' => 'Message processed successfully.',
        ]);
    }

    public function showSession(ChatSession $chatSession): View
    {
        $this->ensureSessionOwnership($chatSession);

        $chatSession->load(['messages' => function ($query) {
            $query->oldest('created_at');
        }]);
        $chatSession->loadCount('messages');

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $sessions = $this->loadUserSessions($user);

        return view('pages.chat', compact('chatSession', 'sessions'));
    }

    public function renameSession(Request $request, ChatSession $chatSession): JsonResponse
    {
        $this->ensureSessionOwnership($chatSession);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
        ]);

        $chatSession->update([
            'title' => $validated['title'],
        ]);

        return response()->json([
            'success' => true,
            'data' => $chatSession->fresh()->only(['id', 'title', 'updated_at']),
            'message' => 'Chat session renamed successfully.',
        ]);
    }

    public function deleteSession(ChatSession $chatSession): JsonResponse
    {
        $this->ensureSessionOwnership($chatSession);

        $chatSession->delete();

        return response()->json([
            'success' => true,
            'message' => 'Chat session deleted successfully.',
        ]);
    }

    private function ensureSessionOwnership(ChatSession $chatSession): void
    {
        if ((int) $chatSession->user_id !== (int) Auth::id()) {
            throw new HttpException(403, 'You are not authorized to access this chat session.');
        }
    }

    private function loadUserSessions(User $user)
    {
        return $user->chatSessions()
            ->withCount('messages')
            ->latest('updated_at')
            ->get();
    }

    private function buildConversationContext(ChatSession $chatSession): array
    {
        $recentMessages = $chatSession->messages()
            ->latest('created_at')
            ->take(10)
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
            'language_mode' => $this->detectSessionLanguageMode($recentMessages),
        ];
    }

    private function detectSessionLanguageMode(array $messages): string
    {
        $swahiliKeywords = ['habari', 'mambo', 'asante', 'karibu', 'mradi', 'wazo', 'bajeti', 'mpango'];
        $englishKeywords = ['hello', 'project', 'budget', 'plan', 'timeline', 'build', 'create'];
        
        $swCount = 0;
        $enCount = 0;
        
        foreach (array_slice($messages, -5) as $message) {
            $text = strtolower($message['message'] ?? '');
            
            foreach ($swahiliKeywords as $keyword) {
                if (str_contains($text, $keyword)) {
                    $swCount++;
                }
            }
            
            foreach ($englishKeywords as $keyword) {
                if (str_contains($text, $keyword)) {
                    $enCount++;
                }
            }
        }
        
        if ($swCount > $enCount) {
            return 'sw';
        } elseif ($enCount > $swCount) {
            return 'en';
        }
        
        return 'mixed';
    }

    private function checkAndTriggerAutoPrototype(ChatSession $chatSession): void
    {
        $messageCount = $chatSession->messages()->count();
        
        // Only trigger after at least 8 messages (4 exchanges)
        if ($messageCount < 8) {
            return;
        }

        // Check if user already has a project from this session
        $existingProject = \App\Models\Project::query()
            ->where('user_id', Auth::id())
            ->where('chat_session_id', $chatSession->id)
            ->first();

        if ($existingProject) {
            return;
        }

        // Check if conversation contains project-related keywords
        $recentUserMessages = $chatSession->messages()
            ->where('sender', 'user')
            ->latest('created_at')
            ->take(6)
            ->pluck('message')
            ->implode(' ');

        $projectKeywords = [
            'build', 'create', 'develop', 'make', 'design', 'launch', 'start',
            'app', 'website', 'platform', 'system', 'tool', 'service',
            'project', 'business', 'startup', 'company', 'product',
            'tengeneza', 'jenga', 'unda', 'anzisha', 'mradi', 'bidhaa'
        ];

        $hasProjectKeywords = false;
        foreach ($projectKeywords as $keyword) {
            if (str_contains(strtolower($recentUserMessages), $keyword)) {
                $hasProjectKeywords = true;
                break;
            }
        }

        if (!$hasProjectKeywords) {
            return;
        }

        // Trigger the auto-creation job
        AutoCreateProjectPrototypeJob::dispatch(
            chatSessionId: (int) $chatSession->id,
            userId: (int) Auth::id()
        )->delay(now()->addMinutes(2)); // Delay to allow for more conversation
    }
}
