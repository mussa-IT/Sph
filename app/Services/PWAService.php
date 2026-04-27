<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PWAService
{
    public function generateManifest(): array
    {
        return [
            'name' => config('app.name', 'SmartProjectHub'),
            'short_name' => config('app.short_name', 'SPH'),
            'description' => config('app.description', 'AI-powered project management and collaboration platform'),
            'start_url' => '/',
            'display' => 'standalone',
            'background_color' => '#ffffff',
            'theme_color' => config('app.theme_color', '#3B82F6'),
            'orientation' => 'portrait-primary',
            'scope' => '/',
            'lang' => app()->getLocale(),
            'categories' => ['productivity', 'business', 'utilities'],
            'icons' => $this->getIconManifest(),
            'shortcuts' => $this->getShortcuts(),
            'screenshots' => $this->getScreenshots(),
            'related_applications' => [],
            'prefer_related_applications' => false,
            'edge_side_panel' => [
                'preferred_width' => 400
            ]
        ];
    }

    public function getServiceWorkerContent(): string
    {
        return file_get_contents(public_path('sw.js'));
    }

    public function getIconManifest(): array
    {
        $icons = [];
        $sizes = [72, 96, 128, 144, 152, 192, 384, 512];

        foreach ($sizes as $size) {
            $icons[] = [
                'src' => "/icons/icon-{$size}x{$size}.png",
                'sizes' => "{$size}x{$size}",
                'type' => 'image/png',
                'purpose' => 'maskable any'
            ];
        }

        return $icons;
    }

    public function getShortcuts(): array
    {
        return [
            [
                'name' => 'New Project',
                'short_name' => 'New Project',
                'description' => 'Create a new project',
                'url' => '/projects/create',
                'icons' => [
                    [
                        'src' => '/icons/shortcut-project.png',
                        'sizes' => '96x96'
                    ]
                ]
            ],
            [
                'name' => 'Tasks',
                'short_name' => 'Tasks',
                'description' => 'View your tasks',
                'url' => '/tasks',
                'icons' => [
                    [
                        'src' => '/icons/shortcut-tasks.png',
                        'sizes' => '96x96'
                    ]
                ]
            ],
            [
                'name' => 'Dashboard',
                'short_name' => 'Dashboard',
                'description' => 'Open dashboard',
                'url' => '/dashboard',
                'icons' => [
                    [
                        'src' => '/icons/shortcut-dashboard.png',
                        'sizes' => '96x96'
                    ]
                ]
            ]
        ];
    }

    public function getScreenshots(): array
    {
        return [
            [
                'src' => '/screenshots/desktop-1.png',
                'sizes' => '1280x720',
                'type' => 'image/png',
                'form_factor' => 'wide',
                'label' => 'Desktop dashboard view'
            ],
            [
                'src' => '/screenshots/mobile-1.png',
                'sizes' => '375x667',
                'type' => 'image/png',
                'form_factor' => 'narrow',
                'label' => 'Mobile dashboard view'
            ]
        ];
    }

    public function generateOfflinePage(): string
    {
        return view('offline')->render();
    }

    public function checkPWACompatibility(): array
    {
        return [
            'service_worker' => true, // 'serviceWorker' in navigator,
            'manifest' => true, // 'manifest' in document.documentElement,
            'push_notifications' => false, // 'PushManager' in window,
            'background_sync' => false, // 'SyncManager' in window,
            'periodic_sync' => false, // 'PeriodicSyncManager' in window,
            'install_prompt' => false, // 'BeforeInstallPromptEvent' in window,
            'share_api' => false, // 'navigator.share' !== undefined,
            'web_share_target' => false, // 'WebShareTarget' in window,
            'file_system_access' => false, // 'showOpenFilePicker' in window,
            'wake_lock' => false, // 'WakeLock' in window,
            'screen_orientation' => true, // 'screen.orientation' !== undefined,
            'device_memory' => false, // 'deviceMemory' in navigator,
            'hardware_concurrency' => false, // 'hardwareConcurrency' in navigator,
            'connection' => false, // 'connection' in navigator,
        ];
    }

    public function getDeviceCapabilities(): array
    {
        return [
            'device_memory' => $this->getDeviceMemory(),
            'hardware_concurrency' => $this->getHardwareConcurrency(),
            'connection' => $this->getConnectionInfo(),
            'screen' => $this->getScreenInfo(),
            'battery' => $this->getBatteryInfo(),
            'storage' => $this->getStorageInfo(),
        ];
    }

    private function getDeviceMemory(): ?int
    {
        return isset($_SERVER['HTTP_DEVICE_MEMORY']) ? (int)$_SERVER['HTTP_DEVICE_MEMORY'] : null;
    }

    private function getHardwareConcurrency(): ?int
    {
        return isset($_SERVER['HTTP_HARDWARE_CONCURRENCY']) ? (int)$_SERVER['HTTP_HARDWARE_CONCURRENCY'] : null;
    }

    private function getConnectionInfo(): array
    {
        $connection = [];
        
        if (isset($_SERVER['HTTP_SAVE_DATA'])) {
            $connection['save_data'] = $_SERVER['HTTP_SAVE_DATA'] === '1';
        }
        
        if (isset($_SERVER['HTTP_ECT'])) {
            $connection['effective_type'] = $_SERVER['HTTP_ECT'];
        }
        
        if (isset($_SERVER['HTTP_RTT'])) {
            $connection['rtt'] = (int)$_SERVER['HTTP_RTT'];
        }
        
        return $connection;
    }

    private function getScreenInfo(): array
    {
        return [
            'width' => $_SERVER['HTTP_SCREEN_WIDTH'] ?? null,
            'height' => $_SERVER['HTTP_SCREEN_HEIGHT'] ?? null,
            'pixel_depth' => $_SERVER['HTTP_SCREEN_PIXEL_DEPTH'] ?? null,
            'color_depth' => $_SERVER['HTTP_SCREEN_COLOR_DEPTH'] ?? null,
        ];
    }

    private function getBatteryInfo(): array
    {
        return [
            'level' => $_SERVER['HTTP_BATTERY_LEVEL'] ?? null,
            'charging' => $_SERVER['HTTP_BATTERY_CHARGING'] ?? null,
        ];
    }

    private function getStorageInfo(): array
    {
        return [
            'quota' => null,
            'usage' => null,
        ];
    }

    public function optimizeForDevice(): array
    {
        $capabilities = $this->getDeviceCapabilities();
        $optimizations = [];

        // Memory optimizations
        if (isset($capabilities['device_memory'])) {
            if ($capabilities['device_memory'] < 4) {
                $optimizations[] = 'reduce_animations';
                $optimizations[] = 'lazy_load_images';
                $optimizations[] = 'minimal_features';
            } elseif ($capabilities['device_memory'] < 8) {
                $optimizations[] = 'lazy_load_images';
                $optimizations[] = 'reduce_animations';
            }
        }

        // Connection optimizations
        if (isset($capabilities['connection']['effective_type'])) {
            $connectionType = $capabilities['connection']['effective_type'];
            
            if ($connectionType === 'slow-2g' || $connectionType === '2g') {
                $optimizations[] = 'text_only_mode';
                $optimizations[] = 'minimal_images';
                $optimizations[] = 'offline_first';
            } elseif ($connectionType === '3g') {
                $optimizations[] = 'compress_images';
                $optimizations[] = 'lazy_load_images';
            }
            
            if (isset($capabilities['connection']['save_data']) && $capabilities['connection']['save_data']) {
                $optimizations[] = 'data_saver_mode';
            }
        }

        // Battery optimizations
        if (isset($capabilities['battery']['level']) && $capabilities['battery']['level'] < 0.2) {
            $optimizations[] = 'battery_saver_mode';
            $optimizations[] = 'reduce_animations';
            $optimizations[] = 'disable_background_sync';
        }

        // Screen size optimizations
        if (isset($capabilities['screen']['width'])) {
            $width = $capabilities['screen']['width'];
            
            if ($width < 768) {
                $optimizations[] = 'mobile_layout';
                $optimizations[] = 'touch_optimized';
            } elseif ($width < 1024) {
                $optimizations[] = 'tablet_layout';
            }
        }

        return array_unique($optimizations);
    }

    public function generateOfflineData(): array
    {
        // Generate essential data for offline mode
        return [
            'user' => auth()->user() ? [
                'id' => auth()->user()->id,
                'name' => auth()->user()->name,
                'email' => auth()->user()->email,
                'avatar' => auth()->user()->avatar_url,
            ] : null,
            'projects' => $this->getOfflineProjects(),
            'tasks' => $this->getOfflineTasks(),
            'settings' => $this->getOfflineSettings(),
            'last_sync' => now()->toISOString(),
        ];
    }

    private function getOfflineProjects(): array
    {
        if (!auth()->user()) {
            return [];
        }

        return auth()->user()->projects()
            ->with(['tasks', 'budgets'])
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($project) {
                return [
                    'id' => $project->id,
                    'title' => $project->title,
                    'description' => $project->description,
                    'status' => $project->status,
                    'progress' => $project->progress,
                    'updated_at' => $project->updated_at->toISOString(),
                    'tasks_count' => $project->tasks->count(),
                    'completed_tasks_count' => $project->tasks->where('status', 'completed')->count(),
                ];
            })
            ->toArray();
    }

    private function getOfflineTasks(): array
    {
        if (!auth()->user()) {
            return [];
        }

        return auth()->user()->assignedTasks()
            ->with('project')
            ->latest()
            ->take(20)
            ->get()
            ->map(function ($task) {
                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'description' => $task->description,
                    'status' => $task->status,
                    'priority' => $task->priority,
                    'due_date' => $task->due_date?->toISOString(),
                    'project' => [
                        'id' => $task->project->id,
                        'title' => $task->project->title,
                    ],
                    'updated_at' => $task->updated_at->toISOString(),
                ];
            })
            ->toArray();
    }

    private function getOfflineSettings(): array
    {
        if (!auth()->user()) {
            return [];
        }

        return [
            'theme' => auth()->user()->theme ?? 'light',
            'language' => auth()->user()->language ?? 'en',
            'timezone' => auth()->user()->timezone ?? 'UTC',
            'notifications' => auth()->user()->notification_settings ?? [],
            'preferences' => auth()->user()->preferences ?? [],
        ];
    }

    public function cacheKey(string $type, string $identifier): string
    {
        return "pwa:{$type}:" . md5($identifier);
    }

    public function isOfflineMode(): bool
    {
        return request()->header('X-Offline-Mode') === 'true' || 
               request()->get('offline') === 'true';
    }

    public function shouldServeOffline(): bool
    {
        return $this->isOfflineMode() || 
               !request()->isMethod('GET') ||
               str_contains(request->header('User-Agent', ''), 'Mobile');
    }

    public function getInstallPromptConfig(): array
    {
        return [
            'title' => 'Install SmartProjectHub',
            'description' => 'Install our app for faster access and offline capabilities',
            'install_text' => 'Install',
            'ios_instructions' => 'To install: Tap Share → Add to Home Screen',
            'android_instructions' => 'Tap the install button above or open in Chrome menu',
            'desktop_instructions' => 'Click the install icon in your browser address bar',
        ];
    }
}
