<?php

namespace App\Jobs;

use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Services\AIServiceInterface;
use App\Services\ChatMessageSanitizer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class GenerateChatReplyJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(
        public int $chatSessionId,
        public int $userMessageId,
        public int $assistantMessageId,
        public array $context = []
    ) {
    }

    public function handle(AIServiceInterface $aiService, ChatMessageSanitizer $sanitizer): void
    {
        $chatSession = ChatSession::query()->find($this->chatSessionId);
        $userMessage = ChatMessage::query()
            ->whereKey($this->userMessageId)
            ->where('chat_session_id', $this->chatSessionId)
            ->first();
        $assistantMessage = ChatMessage::query()
            ->whereKey($this->assistantMessageId)
            ->where('chat_session_id', $this->chatSessionId)
            ->where('sender', 'ai')
            ->first();

        if (! $chatSession || ! $userMessage || ! $assistantMessage) {
            return;
        }

        try {
            $context = $this->context ?: $this->buildContextFromSession($chatSession);
            $aiResult = $aiService->chat($userMessage->message, $context);
            $aiReply = $sanitizer->sanitize((string) ($aiResult['reply'] ?? ''));
            if ($aiReply === '') {
                $aiReply = 'I could not generate a response right now. Please try again.';
            }

            $assistantMessage->update([
                'message' => $aiReply,
            ]);
            $chatSession->touch();
        } catch (Throwable $exception) {
            report($exception);

            $assistantMessage->update([
                'message' => 'I could not generate a response right now. Please try again.',
            ]);
            $chatSession->touch();
        }
    }

    private function buildContextFromSession(ChatSession $chatSession): array
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
            'language_mode' => 'mixed', // Default for async jobs
        ];
    }
}
