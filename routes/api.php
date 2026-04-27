<?php

use App\Http\Controllers\Api\V1\ProjectController;
use App\Http\Controllers\Api\V1\TeamController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application.
| These routes are loaded by the RouteServiceProvider.
|
*/

Route::middleware('auth:sanctum')->group(function () {
    // User info
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // API v1 Routes
    Route::prefix('v1')->group(function () {
        // User endpoints
        Route::get('/user/profile', [UserController::class, 'profile']);
        Route::put('/user/profile', [UserController::class, 'updateProfile']);
        Route::get('/user/stats', [UserController::class, 'stats']);

        // Project endpoints
        Route::apiResource('projects', ProjectController::class);
        Route::get('/projects/{project}/tasks', [ProjectController::class, 'tasks']);
        Route::get('/projects/{project}/comments', [ProjectController::class, 'comments']);
        Route::get('/projects/{project}/budgets', [ProjectController::class, 'budgets']);

        // Team endpoints
        Route::apiResource('teams', TeamController::class);
        Route::get('/teams/{team}/members', [TeamController::class, 'members']);
        Route::post('/teams/{team}/invite', [TeamController::class, 'invite']);
        Route::post('/teams/{team}/leave', [TeamController::class, 'leave']);
    });

    // Activity Feed API
    Route::get('/activities', function (Request $request) {
        $limit = min($request->integer('limit', 20), 50);
        $before = $request->input('before');

        // Generate mock activities (in production, query from database)
        $activities = [];
        $types = ['project', 'task', 'system'];
        $icons = [
            'project' => ['📁', 'bg-blue-500/10 text-blue-600 dark:bg-blue-500/20 dark:text-blue-400'],
            'task' => ['✅', 'bg-emerald-500/10 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400'],
            'system' => ['🔔', 'bg-violet-500/10 text-violet-600 dark:bg-violet-500/20 dark:text-violet-400'],
        ];

        for ($i = 0; $i < $limit; $i++) {
            $type = $types[array_rand($types)];
            $id = $before ? $before - $i - 1 : time() - ($i * 3600);

            $activities[] = [
                'id' => $id,
                'type' => $type,
                'icon' => $icons[$type][0],
                'iconBg' => $icons[$type][1],
                'message' => $type === 'project'
                    ? '<strong>Project Alpha</strong> was updated by <strong>You</strong>'
                    : ($type === 'task'
                        ? 'New task <strong>Design Review</strong> was created'
                        : 'System <strong>backup completed</strong> successfully'),
                'timestamp' => now()->subHours($i)->toIso8601String(),
                'read' => $i > 3, // First 4 are unread
                'projectName' => $type === 'project' ? 'Alpha' : null,
                'url' => $type === 'project' ? '/projects/1' : null,
            ];
        }

        return response()->json([
            'activities' => $activities,
            'hasMore' => true,
        ]);
    })->name('api.activities.index');

    // Check for new activities
    Route::get('/activities/check', function (Request $request) {
        $after = $request->input('after');

        // Return mock new activities
        // In production, query: Activity::where('id', '>', $after)->get()
        return response()->json([
            'new' => [], // Empty for demo, would contain new items
        ]);
    })->name('api.activities.check');

    // Mark activity as read
    Route::post('/activities/{id}/read', function ($id) {
        // In production: Activity::where('id', $id)->update(['read' => true]);
        return response()->json(['success' => true]);
    })->name('api.activities.read');

    // Mark all activities as read
    Route::post('/activities/mark-all-read', function () {
        // In production: Activity::where('user_id', auth()->id())->update(['read' => true]);
        return response()->json(['success' => true]);
    })->name('api.activities.mark-all-read');
});

// Public health check
Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now()->toIso8601String(),
        'version' => config('app.version', '1.0.0'),
    ]);
})->name('api.health');
