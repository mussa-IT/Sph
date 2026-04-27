<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Free',
                'slug' => 'free',
                'description' => 'Perfect for getting started with basic project management',
                'monthly_price' => 0,
                'yearly_price' => 0,
                'features' => [
                    '3 projects',
                    'Basic AI chat (10 messages/month)',
                    'Task management',
                    'Basic templates',
                    'Email support',
                ],
                'limits' => [
                    'projects' => 3,
                    'ai_messages' => 10,
                    'tasks_per_project' => 20,
                    'file_storage_mb' => 100,
                ],
                'sort_order' => 1,
                'is_active' => true,
                'is_popular' => false,
                'trial_days' => 0,
            ],
            [
                'name' => 'Pro',
                'slug' => 'pro',
                'description' => 'For professionals who need advanced features and unlimited projects',
                'monthly_price' => 29.99,
                'yearly_price' => 299.90,
                'features' => [
                    'Unlimited projects',
                    'Advanced AI chat (unlimited)',
                    'AI Smart Builder',
                    'Advanced analytics',
                    'Priority support',
                    'Custom templates',
                    'Team collaboration (up to 5 members)',
                    'Web3 features',
                ],
                'limits' => [
                    'projects' => -1, // unlimited
                    'ai_messages' => -1,
                    'tasks_per_project' => 100,
                    'file_storage_mb' => 1000,
                    'team_members' => 5,
                ],
                'sort_order' => 2,
                'is_active' => true,
                'is_popular' => true,
                'trial_days' => 14,
            ],
            [
                'name' => 'Team',
                'slug' => 'team',
                'description' => 'For growing teams that need advanced collaboration features',
                'monthly_price' => 99.99,
                'yearly_price' => 999.90,
                'features' => [
                    'Everything in Pro',
                    'Unlimited team members',
                    'Advanced team roles',
                    'Team analytics',
                    'Shared workspaces',
                    'SSO authentication',
                    'API access',
                    'Custom integrations',
                    'Dedicated support',
                ],
                'limits' => [
                    'projects' => -1,
                    'ai_messages' => -1,
                    'tasks_per_project' => -1,
                    'file_storage_mb' => 10000,
                    'team_members' => -1,
                    'api_calls_per_month' => 100000,
                ],
                'sort_order' => 3,
                'is_active' => true,
                'is_popular' => false,
                'trial_days' => 14,
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'description' => 'Custom solutions for large organizations with specific needs',
                'monthly_price' => 299.99,
                'yearly_price' => 2999.90,
                'features' => [
                    'Everything in Team',
                    'Custom features',
                    'White-label options',
                    'On-premise deployment',
                    'Advanced security',
                    'Compliance tools',
                    '24/7 phone support',
                    'Custom training',
                    'SLA guarantee',
                    'Dedicated account manager',
                ],
                'limits' => [
                    'projects' => -1,
                    'ai_messages' => -1,
                    'tasks_per_project' => -1,
                    'file_storage_mb' => -1,
                    'team_members' => -1,
                    'api_calls_per_month' => -1,
                ],
                'sort_order' => 4,
                'is_active' => true,
                'is_popular' => false,
                'trial_days' => 30,
            ],
        ];

        foreach ($plans as $planData) {
            Plan::updateOrCreate(
                ['slug' => $planData['slug']],
                $planData
            );
        }
    }
}
