<?php

namespace App\Services;

use App\Models\BillingHistory;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class RevenueService
{
    public function getMRR(): float
    {
        return Subscription::where('status', 'active')
            ->where('billing_cycle', 'monthly')
            ->sum('price');
    }

    public function getARR(): float
    {
        return $this->getMRR() * 12;
    }

    public function getTotalRevenue(Carbon $startDate = null, Carbon $endDate = null): float
    {
        $query = BillingHistory::where('status', 'completed');
        
        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }
        
        return $query->sum('amount');
    }

    public function getRevenueByPeriod(string $period = 'month'): Collection
    {
        $query = BillingHistory::where('status', 'completed');
        
        switch ($period) {
            case 'day':
                $query->selectRaw('DATE(created_at) as period, SUM(amount) as revenue')
                    ->where('created_at', '>=', now()->subDays(30))
                    ->groupBy('period')
                    ->orderBy('period');
                break;
                
            case 'week':
                $query->selectRaw('YEAR(created_at) as year, WEEK(created_at) as week, SUM(amount) as revenue')
                    ->where('created_at', '>=', now()->subMonths(3))
                    ->groupBy('year', 'week')
                    ->orderBy('year')
                    ->orderBy('week');
                break;
                
            case 'month':
            default:
                $query->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, SUM(amount) as revenue')
                    ->where('created_at', '>=', now()->subYear())
                    ->groupBy('year', 'month')
                    ->orderBy('year')
                    ->orderBy('month');
                break;
        }
        
        return $query->get();
    }

    public function getMRRGrowth(): array
    {
        $currentMRR = $this->getMRR();
        $previousMRR = $this->getMRRForDate(now()->subMonth());
        
        $growth = $previousMRR > 0 ? (($currentMRR - $previousMRR) / $previousMRR) * 100 : 0;
        
        return [
            'current' => $currentMRR,
            'previous' => $previousMRR,
            'growth' => $growth,
            'growth_amount' => $currentMRR - $previousMRR,
        ];
    }

    public function getMRRForDate(Carbon $date): float
    {
        return Subscription::where('status', 'active')
            ->where('billing_cycle', 'monthly')
            ->where('created_at', '<=', $date)
            ->sum('price');
    }

    public function getActiveSubscriptionsCount(): int
    {
        return Subscription::where('status', 'active')->count();
    }

    public function getSubscriptionsByPlan(): Collection
    {
        return Plan::withCount(['subscriptions' => function ($query) {
                $query->where('status', 'active');
            }])
            ->get()
            ->map(function ($plan) {
                return [
                    'plan_name' => $plan->name,
                    'plan_slug' => $plan->slug,
                    'subscriptions_count' => $plan->subscriptions_count,
                    'revenue' => $plan->subscriptions_count * $plan->monthly_price,
                ];
            });
    }

    public function getChurnRate(): float
    {
        $totalSubscriptions = Subscription::where('created_at', '>=', now()->subMonth())->count();
        $cancelledSubscriptions = Subscription::where('status', 'cancelled')
            ->where('cancelled_at', '>=', now()->subMonth())
            ->count();
            
        return $totalSubscriptions > 0 ? ($cancelledSubscriptions / $totalSubscriptions) * 100 : 0;
    }

    public function getCustomerLifetimeValue(): float
    {
        $totalRevenue = $this->getTotalRevenue();
        $totalCustomers = BillingHistory::distinct('user_id')->count();
        
        return $totalCustomers > 0 ? $totalRevenue / $totalCustomers : 0;
    }

    public function getAverageRevenuePerUser(): float
    {
        $activeSubscriptions = $this->getActiveSubscriptionsCount();
        $totalMRR = $this->getMRR();
        
        return $activeSubscriptions > 0 ? $totalMRR / $activeSubscriptions : 0;
    }

    public function getConversionRate(): float
    {
        $totalUsers = User::count();
        $payingUsers = User::whereHas('subscriptions', function ($query) {
            $query->where('status', 'active');
        })->count();
        
        return $totalUsers > 0 ? ($payingUsers / $totalUsers) * 100 : 0;
    }

    public function getTrialConversionRate(): float
    {
        $totalTrials = Subscription::where('status', 'trial')->count();
        $convertedTrials = Subscription::where('status', 'converted')->count();
        
        return $totalTrials > 0 ? ($convertedTrials / $totalTrials) * 100 : 0;
    }

    public function getRevenueByPaymentGateway(): Collection
    {
        return BillingHistory::where('status', 'completed')
            ->selectRaw('payment_gateway, SUM(amount) as revenue, COUNT(*) as transactions')
            ->groupBy('payment_gateway')
            ->get();
    }

    public function getTopCustomers(int $limit = 10): Collection
    {
        return BillingHistory::where('status', 'completed')
            ->selectRaw('user_id, SUM(amount) as total_spent, COUNT(*) as transactions')
            ->with('user:id,name,email')
            ->groupBy('user_id')
            ->orderByDesc('total_spent')
            ->limit($limit)
            ->get();
    }

    public function getRevenueForecast(): array
    {
        $currentMRR = $this->getMRR();
        $churnRate = $this->getChurnRate();
        $growthRate = $this->getMRRGrowth()['growth'];
        
        // Simple forecast based on current trends
        $nextMonthMRR = $currentMRR * (1 + ($growthRate / 100)) * (1 - ($churnRate / 100));
        $nextQuarterMRR = $nextMonthMRR * pow(1 + ($growthRate / 100), 3) * pow(1 - ($churnRate / 100), 3);
        $nextYearMRR = $currentMRR * 12 * (1 + ($growthRate / 100)) * (1 - ($churnRate / 100));
        
        return [
            'next_month' => $nextMonthMRR,
            'next_quarter' => $nextQuarterMRR,
            'next_year' => $nextYearMRR,
        ];
    }

    public function getCohortAnalysis(): Collection
    {
        // Get users who signed up in the last 12 months
        $cohorts = User::where('created_at', '>=', now()->subYear())
            ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();
        
        return $cohorts->map(function ($cohort) {
            $cohortDate = Carbon::create($cohort->year, $cohort->month, 1);
            $cohortUsers = User::whereYear('created_at', $cohort->year)
                ->whereMonth('created_at', $cohort->month)
                ->get();
            
            $retentionData = [];
            
            for ($i = 1; $i <= 12; $i++) {
                $periodDate = $cohortDate->copy()->addMonths($i);
                $retainedUsers = $cohortUsers->filter(function ($user) use ($periodDate) {
                    return $user->subscriptions()
                        ->where('created_at', '<=', $periodDate)
                        ->where('status', 'active')
                        ->exists();
                });
                
                $retentionRate = $cohortUsers->count() > 0 ? 
                    ($retainedUsers->count() / $cohortUsers->count()) * 100 : 0;
                
                $retentionData[$i] = $retentionRate;
            }
            
            return [
                'cohort_date' => $cohortDate->format('Y-m'),
                'cohort_size' => $cohortUsers->count(),
                'retention_data' => $retentionData,
            ];
        });
    }

    public function getRevenueSummary(): array
    {
        return [
            'mrr' => $this->getMRR(),
            'arr' => $this->getARR(),
            'total_revenue' => $this->getTotalRevenue(),
            'active_subscriptions' => $this->getActiveSubscriptionsCount(),
            'churn_rate' => $this->getChurnRate(),
            'clv' => $this->getCustomerLifetimeValue(),
            'arpu' => $this->getAverageRevenuePerUser(),
            'conversion_rate' => $this->getConversionRate(),
            'trial_conversion_rate' => $this->getTrialConversionRate(),
            'mrr_growth' => $this->getMRRGrowth(),
        ];
    }

    public function getDashboardData(): array
    {
        return [
            'summary' => $this->getRevenueSummary(),
            'revenue_by_period' => $this->getRevenueByPeriod(),
            'subscriptions_by_plan' => $this->getSubscriptionsByPlan(),
            'revenue_by_gateway' => $this->getRevenueByPaymentGateway(),
            'top_customers' => $this->getTopCustomers(),
            'forecast' => $this->getRevenueForecast(),
            'cohort_analysis' => $this->getCohortAnalysis(),
        ];
    }
}
