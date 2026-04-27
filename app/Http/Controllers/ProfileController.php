<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;

class ProfileController extends Controller
{
    public function show(): View
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $badge = $user->email_verified_at
            ? ['label' => 'Verified Member', 'classes' => 'bg-emerald-500/15 text-emerald-700 dark:text-emerald-300']
            : ['label' => 'Standard Member', 'classes' => 'bg-primary/15 text-primary'];

        $joinedDate = ($user->created_at instanceof Carbon)
            ? $user->created_at->format('F j, Y')
            : 'Unknown';

        $insights = [
            'total_projects' => $user->projects()->count(),
            'completed_projects' => $user->projects()->where('status', 'completed')->count(),
            'ai_chats_used' => $user->chatSessions()->count(),
            'tasks_finished' => $user->tasks()->where('status', Task::STATUS_DONE)->count(),
            'member_since' => $joinedDate,
        ];

        return view('pages.profile', compact('user', 'badge', 'joinedDate', 'insights'));
    }
}
