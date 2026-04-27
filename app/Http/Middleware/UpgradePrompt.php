<?php

namespace App\Http\Middleware;

use App\Services\UpgradePromptService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UpgradePrompt
{
    public function __construct(
        private UpgradePromptService $upgradePromptService
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only add prompts to HTML responses
        if ($response instanceof \Illuminate\Http\Response && 
            $response->headers->get('content-type') === 'text/html; charset=UTF-8') {
            
            $user = Auth::user();
            
            if ($user && $this->upgradePromptService->shouldShowPrompt($user)) {
                $prompts = $this->upgradePromptService->getUpgradePrompts($user);
                $activePrompt = $prompts->first();
                
                if ($activePrompt && !$this->upgradePromptService->isPromptDismissed($user, $activePrompt['id'])) {
                    $this->injectUpgradePrompt($response, $activePrompt, $user);
                }
            }
        }

        return $response;
    }

    private function injectUpgradePrompt(Response $response, array $prompt, User $user): void
    {
        $content = $response->getContent();
        
        if (!$content) {
            return;
        }

        $promptHtml = $this->generatePromptHtml($prompt, $user);
        
        // Inject prompt before closing body tag
        $content = str_replace('</body>', $promptHtml . '</body>', $content);
        
        $response->setContent($content);
    }

    private function generatePromptHtml(array $prompt, User $user): string
    {
        $urgencyClass = match($prompt['urgency']) {
            'high' => 'border-danger/20 bg-danger/5',
            'medium' => 'border-warning/20 bg-warning/5',
            'low' => 'border-info/20 bg-info/5',
            default => 'border-muted/20 bg-muted/5',
        };

        $iconClass = match($prompt['type']) {
            'upgrade' => '🚀',
            'limit' => '⚠️',
            'feature' => '✨',
            'warning' => '⚡',
            default => '💡',
        };

        return <<<HTML
<div id="upgrade-prompt" class="fixed bottom-4 right-4 max-w-sm w-full z-50">
    <div class="surface-card interactive-lift p-4 {$urgencyClass} border-2 shadow-xl">
        <div class="flex items-start gap-3">
            <div class="text-2xl">{$iconClass}</div>
            <div class="flex-1">
                <h4 class="font-semibold text-foreground dark:text-foreground-dark text-sm mb-1">
                    {$prompt['title']}
                </h4>
                <p class="text-xs text-muted dark:text-muted-dark mb-3">
                    {$prompt['message']}
                </p>
                
                <div class="flex items-center gap-2">
                    <button 
                        onclick="handleUpgradePrompt('{$prompt['action']}', '{$prompt['target_plan']}', '{$prompt['id']}')"
                        class="btn-brand text-xs"
                    >
                        {$this->getActionButtonText($prompt)}
                    </button>
                    
                    <button 
                        onclick="dismissUpgradePrompt('{$prompt['id']}')"
                        class="btn-brand-muted text-xs"
                    >
                        Maybe Later
                    </button>
                </div>
            </div>
            
            <button 
                onclick="dismissUpgradePrompt('{$prompt['id']}')"
                class="text-muted hover:text-foreground transition-colors"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>
</div>

<script>
function handleUpgradePrompt(action, targetPlan, promptId) {
    // Dismiss the prompt
    dismissUpgradePrompt(promptId);
    
    // Handle the action
    if (action === 'upgrade') {
        window.location.href = '/pricing?feature=' + promptId + '&plan=' + targetPlan;
    }
}

function dismissUpgradePrompt(promptId) {
    const prompt = document.getElementById('upgrade-prompt');
    if (prompt) {
        prompt.remove();
    }
    
    // Send dismissal to server
    fetch('/upgrade-prompt/dismiss/' + promptId, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
    });
}
</script>
HTML;
    }

    private function getActionButtonText(array $prompt): string
    {
        return match($prompt['action']) {
            'upgrade' => 'Upgrade Now',
            'trial' => 'Start Free Trial',
            'discount' => 'Get Discount',
            default => 'Learn More',
        };
    }
}
