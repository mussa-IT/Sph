<?php

namespace App\Http\Controllers;

use App\Services\DashboardDataService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    private DashboardDataService $dashboardService;

    public function __construct(DashboardDataService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function index(): View
    {
        $summary = $this->dashboardService->getDashboardSummary();
        $weeklySignups = $this->dashboardService->getWeeklySignups();
        $projectCompletion = $this->dashboardService->getProjectCompletionTrend();
        $aiChatUsage = $this->dashboardService->getAIChatUsage();
        $onchainMints = $this->dashboardService->getOnchainMints();
        $bountyRevenue = $this->dashboardService->getBountyRevenue();
        $onchainActivity = $this->dashboardService->getOnchainActivityFeed();

        return view('pages.dashboard', compact(
            'summary',
            'weeklySignups',
            'projectCompletion',
            'aiChatUsage',
            'onchainMints',
            'bountyRevenue',
            'onchainActivity'
        ));
    }

    public function apiStats(Request $request)
    {
        $type = $request->input('type');

        return match($type) {
            'weekly_signups' => response()->json($this->dashboardService->getWeeklySignups()),
            'project_completion' => response()->json($this->dashboardService->getProjectCompletionTrend()),
            'ai_chat_usage' => response()->json($this->dashboardService->getAIChatUsage()),
            'onchain_mints' => response()->json($this->dashboardService->getOnchainMints()),
            'bounty_revenue' => response()->json($this->dashboardService->getBountyRevenue()),
            'onchain_activity' => response()->json($this->dashboardService->getOnchainActivityFeed()),
            default => response()->json(['error' => 'Invalid type'], 400),
        };
    }
}
