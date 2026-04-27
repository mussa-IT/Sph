<?php

namespace App\Http\Controllers;

use App\Services\PWAService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class PWAController extends Controller
{
    private PWAService $pwaService;

    public function __construct(PWAService $pwaService)
    {
        $this->pwaService = $pwaService;
    }

    public function manifest(): JsonResponse
    {
        $manifest = $this->pwaService->generateManifest();
        
        return response()->json($manifest)
            ->header('Content-Type', 'application/manifest+json')
            ->header('Cache-Control', 'public, max-age=86400'); // Cache for 24 hours
    }

    public function serviceWorker(): Response
    {
        $content = $this->pwaService->getServiceWorkerContent();
        
        return response($content)
            ->header('Content-Type', 'application/javascript')
            ->header('Cache-Control', 'public, max-age=3600'); // Cache for 1 hour
    }

    public function offline(): Response
    {
        $content = $this->pwaService->generateOfflinePage();
        
        return response($content)
            ->header('Content-Type', 'text/html')
            ->header('Cache-Control', 'public, max-age=86400'); // Cache for 24 hours
    }

    public function compatibility(): JsonResponse
    {
        $compatibility = $this->pwaService->checkPWACompatibility();
        
        return response()->json([
            'compatibility' => $compatibility,
            'is_pwa_supported' => $compatibility['service_worker'] && $compatibility['manifest'],
            'can_install' => $compatibility['install_prompt'],
            'supports_push' => $compatibility['push_notifications'],
            'supports_background_sync' => $compatibility['background_sync'],
        ]);
    }

    public function capabilities(): JsonResponse
    {
        $capabilities = $this->pwaService->getDeviceCapabilities();
        $optimizations = $this->pwaService->optimizeForDevice();
        
        return response()->json([
            'capabilities' => $capabilities,
            'optimizations' => $optimizations,
            'is_mobile' => $this->isMobile(),
            'is_tablet' => $this->isTablet(),
            'is_desktop' => $this->isDesktop(),
        ]);
    }

    public function offlineData(): JsonResponse
    {
        if (!auth()->user()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $data = $this->pwaService->generateOfflineData();
        
        return response()->json($data)
            ->header('Cache-Control', 'private, max-age=300'); // Cache for 5 minutes
    }

    public function installPrompt(): JsonResponse
    {
        $config = $this->pwaService->getInstallPromptConfig();
        
        return response()->json([
            'config' => $config,
            'can_install' => $this->canShowInstallPrompt(),
            'install_count' => $this->getInstallCount(),
            'dismissed' => $this->hasDismissedInstallPrompt(),
        ]);
    }

    public function dismissInstallPrompt(Request $request): JsonResponse
    {
        $request->validate([
            'reason' => ['nullable', 'string', 'in:not_now,never,installed'],
        ]);

        $this->setInstallPromptPreference($request->reason ?? 'not_now');
        
        return response()->json([
            'success' => true,
            'message' => 'Install prompt preference saved',
        ]);
    }

    public function syncStatus(): JsonResponse
    {
        if (!auth()->user()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $status = [
            'is_online' => $this->isOnline(),
            'last_sync' => $this->getLastSyncTime(),
            'pending_sync' => $this->getPendingSyncCount(),
            'sync_enabled' => $this->isSyncEnabled(),
        ];

        return response()->json($status);
    }

    public function triggerSync(Request $request): JsonResponse
    {
        if (!auth()->user()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $request->validate([
            'force' => ['nullable', 'boolean'],
        ]);

        try {
            $result = $this->performSync($request->boolean('force', false));
            
            return response()->json([
                'success' => true,
                'message' => 'Sync completed successfully',
                'result' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function subscribePush(Request $request): JsonResponse
    {
        if (!auth()->user()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $request->validate([
            'endpoint' => ['required', 'url'],
            'keys' => ['required', 'array'],
            'keys.p256dh' => ['required', 'string'],
            'keys.auth' => ['required', 'string'],
        ]);

        try {
            $subscription = $this->savePushSubscription($request->all());
            
            return response()->json([
                'success' => true,
                'message' => 'Push subscription saved',
                'subscription_id' => $subscription->id,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save push subscription: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function unsubscribePush(Request $request): JsonResponse
    {
        if (!auth()->user()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $request->validate([
            'endpoint' => ['required', 'url'],
        ]);

        try {
            $this->removePushSubscription($request->endpoint);
            
            return response()->json([
                'success' => true,
                'message' => 'Push subscription removed',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove push subscription: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function testNotification(Request $request): JsonResponse
    {
        if (!auth()->user()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $request->validate([
            'title' => ['required', 'string', 'max:100'],
            'body' => ['required', 'string', 'max:255'],
            'icon' => ['nullable', 'url'],
            'data' => ['nullable', 'array'],
        ]);

        try {
            $result = $this->sendTestNotification($request->all());
            
            return response()->json([
                'success' => true,
                'message' => 'Test notification sent',
                'result' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send test notification: ' . $e->getMessage(),
            ], 500);
        }
    }

    // Helper methods
    private function isMobile(): bool
    {
        $userAgent = request()->header('User-Agent', '');
        return preg_match('/Mobile|Android|iPhone|iPad|iPod/', $userAgent) && !preg_match('/iPad/', $userAgent);
    }

    private function isTablet(): bool
    {
        $userAgent = request()->header('User-Agent', '');
        return preg_match('/iPad|Tablet/', $userAgent);
    }

    private function isDesktop(): bool
    {
        return !$this->isMobile() && !$this->isTablet();
    }

    private function canShowInstallPrompt(): bool
    {
        if (!auth()->user()) {
            return false;
        }

        $preference = $this->getInstallPromptPreference();
        
        if ($preference === 'never') {
            return false;
        }

        if ($preference === 'not_now') {
            $lastDismissed = $this->getLastDismissedTime();
            return $lastDismissed ? now()->diffInDays($lastDismissed) >= 7 : true;
        }

        return true;
    }

    private function getInstallPromptPreference(): ?string
    {
        return auth()->user()->settings['pwa_install_prompt'] ?? null;
    }

    private function setInstallPromptPreference(string $preference): void
    {
        $settings = auth()->user()->settings ?? [];
        $settings['pwa_install_prompt'] = $preference;
        $settings['pwa_install_prompt_updated'] = now()->toISOString();
        
        auth()->user()->update(['settings' => $settings]);
    }

    private function getLastDismissedTime(): ?\Carbon\Carbon
    {
        $settings = auth()->user()->settings ?? [];
        $timestamp = $settings['pwa_install_prompt_updated'] ?? null;
        
        return $timestamp ? \Carbon\Carbon::parse($timestamp) : null;
    }

    private function getInstallCount(): int
    {
        return auth()->user()->settings['pwa_install_count'] ?? 0;
    }

    private function hasDismissedInstallPrompt(): bool
    {
        return $this->getInstallPromptPreference() !== null;
    }

    private function isOnline(): bool
    {
        return !request()->header('X-Offline-Mode');
    }

    private function getLastSyncTime(): ?string
    {
        return auth()->user()->settings['last_sync'] ?? null;
    }

    private function getPendingSyncCount(): int
    {
        // TODO: Implement offline sync queue
        return 0;
    }

    private function isSyncEnabled(): bool
    {
        return auth()->user()->settings['sync_enabled'] ?? true;
    }

    private function performSync(bool $force = false): array
    {
        // TODO: Implement actual sync logic
        $syncTime = now()->toISOString();
        
        $settings = auth()->user()->settings ?? [];
        $settings['last_sync'] = $syncTime;
        
        auth()->user()->update(['settings' => $settings]);

        return [
            'sync_time' => $syncTime,
            'items_synced' => 0,
            'errors' => [],
        ];
    }

    private function savePushSubscription(array $data): object
    {
        // TODO: Implement push subscription storage
        return (object) ['id' => 'temp-id'];
    }

    private function removePushSubscription(string $endpoint): void
    {
        // TODO: Implement push subscription removal
    }

    private function sendTestNotification(array $data): array
    {
        // TODO: Implement push notification sending
        return [
            'sent' => true,
            'recipients' => 1,
        ];
    }
}
