<?php

namespace App\Http\Controllers;

use App\Services\RevenueService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RevenueController extends Controller
{
    public function __construct(
        private RevenueService $revenueService
    ) {}

    public function index(): View
    {
        // Only admin users should access revenue dashboard
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Access denied.');
        }

        $dashboardData = $this->revenueService->getDashboardData();

        return view('pages.revenue.index', $dashboardData);
    }

    public function getMRR(): JsonResponse
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied.',
            ]);
        }

        $mrr = $this->revenueService->getMRR();
        $growth = $this->revenueService->getMRRGrowth();

        return response()->json([
            'success' => true,
            'mrr' => $mrr,
            'growth' => $growth,
        ]);
    }

    public function getRevenueByPeriod(Request $request): JsonResponse
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied.',
            ]);
        }

        $period = $request->input('period', 'month');
        $revenueData = $this->revenueService->getRevenueByPeriod($period);

        return response()->json([
            'success' => true,
            'revenue_data' => $revenueData,
        ]);
    }

    public function getSubscriptionsByPlan(): JsonResponse
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied.',
            ]);
        }

        $subscriptionsByPlan = $this->revenueService->getSubscriptionsByPlan();

        return response()->json([
            'success' => true,
            'subscriptions_by_plan' => $subscriptionsByPlan,
        ]);
    }

    public function getChurnRate(): JsonResponse
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied.',
            ]);
        }

        $churnRate = $this->revenueService->getChurnRate();

        return response()->json([
            'success' => true,
            'churn_rate' => $churnRate,
        ]);
    }

    public function getCLV(): JsonResponse
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied.',
            ]);
        }

        $clv = $this->revenueService->getCustomerLifetimeValue();
        $arpu = $this->revenueService->getAverageRevenuePerUser();

        return response()->json([
            'success' => true,
            'clv' => $clv,
            'arpu' => $arpu,
        ]);
    }

    public function getConversionRates(): JsonResponse
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied.',
            ]);
        }

        $conversionRate = $this->revenueService->getConversionRate();
        $trialConversionRate = $this->revenueService->getTrialConversionRate();

        return response()->json([
            'success' => true,
            'conversion_rate' => $conversionRate,
            'trial_conversion_rate' => $trialConversionRate,
        ]);
    }

    public function getRevenueByGateway(): JsonResponse
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied.',
            ]);
        }

        $revenueByGateway = $this->revenueService->getRevenueByPaymentGateway();

        return response()->json([
            'success' => true,
            'revenue_by_gateway' => $revenueByGateway,
        ]);
    }

    public function getTopCustomers(): JsonResponse
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied.',
            ]);
        }

        $limit = request('limit', 10);
        $topCustomers = $this->revenueService->getTopCustomers($limit);

        return response()->json([
            'success' => true,
            'top_customers' => $topCustomers,
        ]);
    }

    public function getForecast(): JsonResponse
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied.',
            ]);
        }

        $forecast = $this->revenueService->getRevenueForecast();

        return response()->json([
            'success' => true,
            'forecast' => $forecast,
        ]);
    }

    public function getCohortAnalysis(): JsonResponse
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied.',
            ]);
        }

        $cohortAnalysis = $this->revenueService->getCohortAnalysis();

        return response()->json([
            'success' => true,
            'cohort_analysis' => $cohortAnalysis,
        ]);
    }

    public function getSummary(): JsonResponse
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied.',
            ]);
        }

        $summary = $this->revenueService->getRevenueSummary();

        return response()->json([
            'success' => true,
            'summary' => $summary,
        ]);
    }

    public function exportRevenue(Request $request): JsonResponse
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied.',
            ]);
        }

        $request->validate([
            'format' => ['required', 'in:csv,xlsx'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
        ]);

        // TODO: Implement revenue export functionality
        // This would generate CSV/XLSX files with revenue data

        return response()->json([
            'success' => false,
            'message' => 'Export functionality coming soon.',
        ]);
    }

    public function getRealTimeMetrics(): JsonResponse
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied.',
            ]);
        }

        $metrics = [
            'current_mrr' => $this->revenueService->getMRR(),
            'active_subscriptions' => $this->revenueService->getActiveSubscriptionsCount(),
            'churn_rate' => $this->revenueService->getChurnRate(),
            'conversion_rate' => $this->revenueService->getConversionRate(),
            'today_revenue' => $this->revenueService->getTotalRevenue(
                now()->startOfDay(),
                now()->endOfDay()
            ),
            'month_revenue' => $this->revenueService->getTotalRevenue(
                now()->startOfMonth(),
                now()->endOfMonth()
            ),
        ];

        return response()->json([
            'success' => true,
            'metrics' => $metrics,
            'timestamp' => now()->toISOString(),
        ]);
    }

    public function getComparisonData(Request $request): JsonResponse
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied.',
            ]);
        }

        $request->validate([
            'period' => ['required', 'in:day,week,month,year'],
            'compare_with' => ['required', 'in:previous_period,same_period_last_year'],
        ]);

        $period = $request->input('period');
        $compareWith = $request->input('compare_with');

        // TODO: Implement comparison data logic
        // This would compare current period with previous period or same period last year

        return response()->json([
            'success' => false,
            'message' => 'Comparison data coming soon.',
        ]);
    }
}
