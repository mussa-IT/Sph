<?php

namespace App\Services;

use App\Models\User;
use App\Models\Project;
use App\Models\ChatSession;
use Illuminate\Support\Facades\DB;

class DashboardDataService
{
    /**
     * Get weekly signup data for the last 12 weeks
     */
    public function getWeeklySignups(): array
    {
        $data = [];
        for ($i = 11; $i >= 0; $i--) {
            $weekStart = now()->subWeeks($i)->startOfWeek();
            $weekEnd = now()->subWeeks($i)->endOfWeek();
            
            $count = User::whereBetween('created_at', [$weekStart, $weekEnd])->count();
            
            $data[] = [
                'week' => $weekStart->format('M j'),
                'count' => $count,
            ];
        }
        
        return $data;
    }

    /**
     * Get project completion trend for the last 6 months
     */
    public function getProjectCompletionTrend(): array
    {
        $data = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthStart = now()->subMonths($i)->startOfMonth();
            $monthEnd = now()->subMonths($i)->endOfMonth();
            
            $completed = Project::where('status', 'completed')
                ->whereBetween('updated_at', [$monthStart, $monthEnd])
                ->count();
            
            $total = Project::whereBetween('created_at', [$monthStart, $monthEnd])->count();
            
            $data[] = [
                'month' => $monthStart->format('M Y'),
                'completed' => $completed,
                'total' => $total,
                'rate' => $total > 0 ? round(($completed / $total) * 100, 1) : 0,
            ];
        }
        
        return $data;
    }

    /**
     * Get AI chat usage statistics
     */
    public function getAIChatUsage(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $dayStart = now()->subDays($i)->startOfDay();
            $dayEnd = now()->subDays($i)->endOfDay();
            
            $sessions = ChatSession::whereBetween('created_at', [$dayStart, $dayEnd])->count();
            $messages = DB::table('chat_messages')
                ->whereBetween('created_at', [$dayStart, $dayEnd])
                ->count();
            
            $data[] = [
                'date' => $dayStart->format('M j'),
                'sessions' => $sessions,
                'messages' => $messages,
            ];
        }
        
        return $data;
    }

    /**
     * Get onchain mint statistics
     */
    public function getOnchainMints(): array
    {
        $data = [];
        for ($i = 11; $i >= 0; $i--) {
            $weekStart = now()->subWeeks($i)->startOfWeek();
            $weekEnd = now()->subWeeks($i)->endOfWeek();
            
            $projectMints = Project::whereNotNull('transaction_hash')
                ->whereBetween('blockchain_verified_at', [$weekStart, $weekEnd])
                ->count();
            
            $badgeMints = User::whereNotNull('wallet_address')
                ->whereBetween('created_at', [$weekStart, $weekEnd])
                ->count();
            
            $data[] = [
                'week' => $weekStart->format('M j'),
                'projects' => $projectMints,
                'badges' => $badgeMints,
                'total' => $projectMints + $badgeMints,
            ];
        }
        
        return $data;
    }

    /**
     * Get revenue from bounties
     */
    public function getBountyRevenue(): array
    {
        $data = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthStart = now()->subMonths($i)->startOfMonth();
            $monthEnd = now()->subMonths($i)->endOfMonth();
            
            // In production, this would query actual bounty transactions
            // For demo, we'll generate realistic data
            $revenue = rand(2000, 15000);
            
            $data[] = [
                'month' => $monthStart->format('M Y'),
                'revenue' => $revenue,
                'bounties_completed' => rand(3, 12),
            ];
        }
        
        return $data;
    }

    /**
     * Get dashboard summary statistics
     */
    public function getDashboardSummary(): array
    {
        return [
            'total_users' => User::count(),
            'total_projects' => Project::count(),
            'completed_projects' => Project::where('status', 'completed')->count(),
            'active_projects' => Project::where('status', 'in_progress')->count(),
            'onchain_projects' => Project::whereNotNull('transaction_hash')->count(),
            'wallet_connected' => User::whereNotNull('wallet_address')->count(),
            'total_chat_sessions' => ChatSession::count(),
            'total_chat_messages' => DB::table('chat_messages')->count(),
        ];
    }

    /**
     * Get onchain activity feed
     */
    public function getOnchainActivityFeed(): array
    {
        $activities = [];
        
        // Project ownership proofs
        $projects = Project::whereNotNull('transaction_hash')
            ->with('user')
            ->latest('blockchain_verified_at')
            ->take(5)
            ->get();
        
        foreach ($projects as $project) {
            $activities[] = [
                'type' => 'project_ownership',
                'description' => "Project ownership verified: {$project->title}",
                'wallet' => $this->shortenAddress($project->wallet_address),
                'full_address' => $project->wallet_address,
                'tx_hash' => $this->shortenHash($project->transaction_hash),
                'full_hash' => $project->transaction_hash,
                'timestamp' => $project->blockchain_verified_at,
                'explorer_link' => "https://sepolia.basescan.org/tx/{$project->transaction_hash}",
                'network' => 'Base Sepolia',
            ];
        }
        
        // Badge mints (simulated from user badges)
        $users = User::whereNotNull('wallet_address')
            ->whereNotNull('badges')
            ->take(5)
            ->get();
        
        foreach ($users as $user) {
            $badges = json_decode($user->badges, true);
            if (is_array($badges) && count($badges) > 0) {
                $latestBadge = $badges[array_key_last($badges)];
                $activities[] = [
                    'type' => 'badge_mint',
                    'description' => "Badge earned: " . $this->getBadgeName($latestBadge['type']),
                    'wallet' => $this->shortenAddress($user->wallet_address),
                    'full_address' => $user->wallet_address,
                    'tx_hash' => '0x' . substr(hash('sha256', $user->id . $latestBadge['type']), 0, 8),
                    'full_hash' => '0x' . hash('sha256', $user->id . $latestBadge['type']),
                    'timestamp' => $latestBadge['awarded_at'] ?? now(),
                    'explorer_link' => "https://sepolia.basescan.org/address/{$user->wallet_address}",
                    'network' => 'Base Sepolia',
                ];
            }
        }
        
        // Sort by timestamp
        usort($activities, function ($a, $b) {
            return strtotime($b['timestamp']) - strtotime($a['timestamp']);
        });
        
        return array_slice($activities, 0, 10);
    }

    private function shortenAddress(string $address): string
    {
        return substr($address, 0, 6) . '...' . substr($address, -4);
    }

    private function shortenHash(string $hash): string
    {
        return substr($hash, 0, 10) . '...' . substr($hash, -6);
    }

    private function getBadgeName(string $type): string
    {
        $names = [
            'PROJECT_COMPLETED' => 'Project Completed',
            'TOP_INNOVATOR' => 'Top Innovator',
            'MENTOR' => 'Mentor',
            'TASKS_100' => 'Task Master',
            'EARLY_ADOPTER' => 'Early Adopter',
            'VERIFIED_BUILDER' => 'Verified Builder',
        ];
        
        return $names[$type] ?? $type;
    }
}
