@extends('layouts.app')

@section('title', 'Revenue Dashboard')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-7xl">
    <div class="mb-8">
        <h1 class="text-3xl md:text-4xl font-bold text-foreground dark:text-foreground-dark">Revenue Dashboard</h1>
        <p class="text-muted mt-2">Monitor your SaaS metrics and financial performance</p>
    </div>

    <!-- Key Metrics -->
    <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4 mb-8">
        <div class="surface-card interactive-lift p-6">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm text-muted dark:text-muted-dark">Monthly Recurring Revenue</span>
                <span class="text-xs {{ $summary['mrr_growth']['growth'] >= 0 ? 'text-success' : 'text-danger' }}">
                    {{ $summary['mrr_growth']['growth'] >= 0 ? '+' : '' }}{{ number_format($summary['mrr_growth']['growth'], 1) }}%
                </span>
            </div>
            <div class="text-2xl font-bold text-foreground dark:text-foreground-dark">
                ${{ number_format($summary['mrr'], 2) }}
            </div>
            <div class="text-xs text-muted dark:text-muted-dark mt-1">
                ${{ number_format($summary['arr'], 2) }} ARR
            </div>
        </div>

        <div class="surface-card interactive-lift p-6">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm text-muted dark:text-muted-dark">Active Subscriptions</span>
                <span class="text-xs text-muted dark:text-muted-dark">
                    {{ $summary['conversion_rate'] }}% conversion
                </span>
            </div>
            <div class="text-2xl font-bold text-foreground dark:text-foreground-dark">
                {{ $summary['active_subscriptions'] }}
            </div>
            <div class="text-xs text-muted dark:text-muted-dark mt-1">
                Total customers
            </div>
        </div>

        <div class="surface-card interactive-lift p-6">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm text-muted dark:text-muted-dark">Customer Lifetime Value</span>
                <span class="text-xs text-muted dark:text-muted-dark">
                    ${{ number_format($summary['arpu'], 2) }} ARPU
                </span>
            </div>
            <div class="text-2xl font-bold text-foreground dark:text-foreground-dark">
                ${{ number_format($summary['clv'], 2) }}
            </div>
            <div class="text-xs text-muted dark:text-muted-dark mt-1">
                Average revenue per user
            </div>
        </div>

        <div class="surface-card interactive-lift p-6">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm text-muted dark:text-muted-dark">Churn Rate</span>
                <span class="text-xs {{ $summary['churn_rate'] <= 5 ? 'text-success' : ($summary['churn_rate'] <= 10 ? 'text-warning' : 'text-danger') }}">
                    {{ $summary['churn_rate'] <= 5 ? 'Good' : ($summary['churn_rate'] <= 10 ? 'Warning' : 'High') }}
                </span>
            </div>
            <div class="text-2xl font-bold text-foreground dark:text-foreground-dark">
                {{ number_format($summary['churn_rate'], 1) }}%
            </div>
            <div class="text-xs text-muted dark:text-muted-dark mt-1">
                Monthly churn
            </div>
        </div>
    </div>

    <div class="grid gap-8 lg:grid-cols-3">
        <!-- Main Charts -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Revenue Chart -->
            <div class="surface-card interactive-lift">
                <div class="p-6 border-b border-muted/10">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-semibold text-foreground dark:text-foreground-dark">Revenue Trend</h2>
                        <select id="revenue-period" class="input-brand text-sm" onchange="updateRevenueChart()">
                            <option value="month">Monthly</option>
                            <option value="week">Weekly</option>
                            <option value="day">Daily</option>
                        </select>
                    </div>
                </div>
                <div class="p-6">
                    <canvas id="revenue-chart" width="400" height="200"></canvas>
                </div>
            </div>

            <!-- Subscriptions by Plan -->
            <div class="surface-card interactive-lift">
                <div class="p-6 border-b border-muted/10">
                    <h2 class="text-xl font-semibold text-foreground dark:text-foreground-dark">Subscriptions by Plan</h2>
                </div>
                <div class="p-6">
                    <canvas id="subscriptions-chart" width="400" height="200"></canvas>
                </div>
            </div>

            <!-- Revenue by Gateway -->
            <div class="surface-card interactive-lift">
                <div class="p-6 border-b border-muted/10">
                    <h2 class="text-xl font-semibold text-foreground dark:text-foreground-dark">Revenue by Payment Gateway</h2>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        @foreach($revenue_by_gateway as $gateway)
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center text-primary text-xs font-bold">
                                        {{ substr($gateway->payment_gateway, 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-foreground dark:text-foreground-dark">
                                            {{ ucfirst($gateway->payment_gateway) }}
                                        </p>
                                        <p class="text-xs text-muted dark:text-muted-dark">
                                            {{ $gateway->transactions }} transactions
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-medium text-foreground dark:text-foreground-dark">
                                        ${{ number_format($gateway->revenue, 2) }}
                                    </p>
                                    <p class="text-xs text-muted dark:text-muted-dark">
                                        {{ number_format(($gateway->revenue / $summary['total_revenue']) * 100, 1) }}%
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Conversion Metrics -->
            <div class="surface-card interactive-lift">
                <div class="p-6 border-b border-muted/10">
                    <h2 class="text-lg font-semibold text-foreground dark:text-foreground-dark">Conversion Metrics</h2>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-muted dark:text-muted-dark">User to Paid</span>
                        <span class="text-sm font-medium text-foreground dark:text-foreground-dark">
                            {{ number_format($summary['conversion_rate'], 1) }}%
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-muted dark:text-muted-dark">Trial to Paid</span>
                        <span class="text-sm font-medium text-foreground dark:text-foreground-dark">
                            {{ number_format($summary['trial_conversion_rate'], 1) }}%
                        </span>
                    </div>
                </div>
            </div>

            <!-- Top Customers -->
            <div class="surface-card interactive-lift">
                <div class="p-6 border-b border-muted/10">
                    <h2 class="text-lg font-semibold text-foreground dark:text-foreground-dark">Top Customers</h2>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        @foreach($top_customers->take(5) as $customer)
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-muted/20 flex items-center justify-center text-xs font-medium">
                                        {{ substr($customer->user->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-foreground dark:text-foreground-dark">
                                            {{ $customer->user->name }}
                                        </p>
                                        <p class="text-xs text-muted dark:text-muted-dark">
                                            {{ $customer->transactions }} payments
                                        </p>
                                    </div>
                                </div>
                                <span class="text-xs font-medium text-foreground dark:text-foreground-dark">
                                    ${{ number_format($customer->total_spent, 2) }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Forecast -->
            <div class="surface-card interactive-lift">
                <div class="p-6 border-b border-muted/10">
                    <h2 class="text-lg font-semibold text-foreground dark:text-foreground-dark">Revenue Forecast</h2>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-muted dark:text-muted-dark">Next Month</span>
                        <span class="text-sm font-medium text-foreground dark:text-foreground-dark">
                            ${{ number_format($forecast['next_month'], 2) }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-muted dark:text-muted-dark">Next Quarter</span>
                        <span class="text-sm font-medium text-foreground dark:text-foreground-dark">
                            ${{ number_format($forecast['next_quarter'], 2) }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-muted dark:text-muted-dark">Next Year</span>
                        <span class="text-sm font-medium text-foreground dark:text-foreground-dark">
                            ${{ number_format($forecast['next_year'], 2) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="surface-card interactive-lift">
                <div class="p-6 border-b border-muted/10">
                    <h2 class="text-lg font-semibold text-foreground dark:text-foreground-dark">Quick Actions</h2>
                </div>
                <div class="p-6 space-y-3">
                    <button onclick="exportData('csv')" class="w-full btn-brand-muted text-sm">
                        Export CSV
                    </button>
                    <button onclick="exportData('xlsx')" class="w-full btn-brand-muted text-sm">
                        Export Excel
                    </button>
                    <button onclick="refreshMetrics()" class="w-full btn-brand-muted text-sm">
                        Refresh Metrics
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let revenueChart = null;
let subscriptionsChart = null;

document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();
    loadRevenueData('month');
    loadSubscriptionsData();
});

function initializeCharts() {
    // Revenue Chart
    const revenueCtx = document.getElementById('revenue-chart').getContext('2d');
    revenueChart = new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Revenue',
                data: [],
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // Subscriptions Chart
    const subscriptionsCtx = document.getElementById('subscriptions-chart').getContext('2d');
    subscriptionsChart = new Chart(subscriptionsCtx, {
        type: 'doughnut',
        data: {
            labels: [],
            datasets: [{
                data: [],
                backgroundColor: [
                    'rgba(59, 130, 246, 0.8)',
                    'rgba(34, 197, 94, 0.8)',
                    'rgba(251, 146, 60, 0.8)',
                    'rgba(139, 92, 246, 0.8)',
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

async function loadRevenueData(period) {
    try {
        const response = await fetch(`/revenue/revenue-by-period?period=${period}`);
        const result = await response.json();
        
        if (result.success) {
            const labels = result.revenue_data.map(item => {
                if (period === 'month') {
                    return new Date(item.year, item.month - 1).toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
                } else if (period === 'week') {
                    return `Week ${item.week}`;
                } else {
                    return new Date(item.period).toLocaleDateString();
                }
            });
            
            const data = result.revenue_data.map(item => item.revenue);
            
            revenueChart.data.labels = labels;
            revenueChart.data.datasets[0].data = data;
            revenueChart.update();
        }
    } catch (error) {
        console.error('Error loading revenue data:', error);
    }
}

async function loadSubscriptionsData() {
    try {
        const response = await fetch('/revenue/subscriptions-by-plan');
        const result = await response.json();
        
        if (result.success) {
            const labels = result.subscriptions_by_plan.map(item => item.plan_name);
            const data = result.subscriptions_by_plan.map(item => item.subscriptions_count);
            
            subscriptionsChart.data.labels = labels;
            subscriptionsChart.data.datasets[0].data = data;
            subscriptionsChart.update();
        }
    } catch (error) {
        console.error('Error loading subscriptions data:', error);
    }
}

function updateRevenueChart() {
    const period = document.getElementById('revenue-period').value;
    loadRevenueData(period);
}

async function exportData(format) {
    try {
        const response = await fetch(`/revenue/export?format=${format}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            // TODO: Handle file download
            alert('Export functionality coming soon!');
        } else {
            alert(result.message || 'Failed to export data.');
        }
    } catch (error) {
        console.error('Error exporting data:', error);
        alert('An error occurred while exporting data.');
    }
}

async function refreshMetrics() {
    try {
        const response = await fetch('/revenue/real-time-metrics');
        const result = await response.json();
        
        if (result.success) {
            // Update metrics on page
            location.reload();
        }
    } catch (error) {
        console.error('Error refreshing metrics:', error);
    }
}
</script>
@endsection
