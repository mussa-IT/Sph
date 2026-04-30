<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ChatSession;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        // Get simple data without complex services
        $totalProjects = Project::count();
        $completedProjects = Project::where('status', 'completed')->count();
        $totalChats = ChatSession::count();
        $recentProjects = Project::latest()->take(5)->get();
        $notifications = Notification::latest()->take(5)->get();

        return view('pages.dashboard', compact(
            'totalProjects',
            'completedProjects',
            'totalChats',
            'recentProjects',
            'notifications'
        ));
    }
}
