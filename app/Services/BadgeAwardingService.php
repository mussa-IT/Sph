<?php

namespace App\Services;

use App\Models\User;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class BadgeAwardingService
{
    private string $badgeContractAddress;
    private string $rpcUrl;
    private string $adminPrivateKey;

    public function __construct()
    {
        $this->badgeContractAddress = env('BADGE_CONTRACT_ADDRESS', '0x0000000000000000000000000000000000000000');
        $this->rpcUrl = env('BASE_SEPOLIA_RPC_URL', 'https://sepolia.base.org');
        $this->adminPrivateKey = env('WEB3_ADMIN_PRIVATE_KEY');
    }

    /**
     * Check and award badges based on user achievements
     */
    public function checkAndAwardBadges(User $user): array
    {
        $awardedBadges = [];

        // Check Project Completed badge
        if ($this->shouldAwardProjectCompleted($user)) {
            $awardedBadges[] = $this->awardBadge($user, 'PROJECT_COMPLETED');
        }

        // Check Top Innovator badge (5+ completed projects)
        if ($this->shouldAwardTopInnovator($user)) {
            $awardedBadges[] = $this->awardBadge($user, 'TOP_INNOVATOR');
        }

        // Check Mentor badge (helped other users)
        if ($this->shouldAwardMentor($user)) {
            $awardedBadges[] = $this->awardBadge($user, 'MENTOR');
        }

        // Check Task Master badge (100+ tasks completed)
        if ($this->shouldAwardTaskMaster($user)) {
            $awardedBadges[] = $this->awardBadge($user, 'TASKS_100');
        }

        // Check Early Adopter badge (first 1000 users)
        if ($this->shouldAwardEarlyAdopter($user)) {
            $awardedBadges[] = $this->awardBadge($user, 'EARLY_ADOPTER');
        }

        // Check Verified Builder badge (has onchain projects)
        if ($this->shouldAwardVerifiedBuilder($user)) {
            $awardedBadges[] = $this->awardBadge($user, 'VERIFIED_BUILDER');
        }

        return $awardedBadges;
    }

    /**
     * Check if user should receive Project Completed badge
     */
    private function shouldAwardProjectCompleted(User $user): bool
    {
        $completedProjects = $user->projects()
            ->where('status', 'completed')
            ->count();

        return $completedProjects >= 1 && !$this->hasBadge($user, 'PROJECT_COMPLETED');
    }

    /**
     * Check if user should receive Top Innovator badge
     */
    private function shouldAwardTopInnovator(User $user): bool
    {
        $completedProjects = $user->projects()
            ->where('status', 'completed')
            ->count();

        return $completedProjects >= 5 && !$this->hasBadge($user, 'TOP_INNOVATOR');
    }

    /**
     * Check if user should receive Mentor badge
     */
    private function shouldAwardMentor(User $user): bool
    {
        // Check if user has helped others (comments on other users' projects)
        $helpfulComments = $user->comments()
            ->whereHas('task.project', function ($query) use ($user) {
                $query->where('user_id', '!=', $user->id);
            })
            ->count();

        return $helpfulComments >= 10 && !$this->hasBadge($user, 'MENTOR');
    }

    /**
     * Check if user should receive Task Master badge
     */
    private function shouldAwardTaskMaster(User $user): bool
    {
        $completedTasks = $user->assignedTasks()
            ->where('status', 'completed')
            ->count();

        return $completedTasks >= 100 && !$this->hasBadge($user, 'TASKS_100');
    }

    /**
     * Check if user should receive Early Adopter badge
     */
    private function shouldAwardEarlyAdopter(User $user): bool
    {
        // First 1000 users
        $userCount = User::where('id', '<=', $user->id)->count();
        
        return $userCount <= 1000 && !$this->hasBadge($user, 'EARLY_ADOPTER');
    }

    /**
     * Check if user should receive Verified Builder badge
     */
    private function shouldAwardVerifiedBuilder(User $user): bool
    {
        $onchainProjects = $user->projects()
            ->whereNotNull('transaction_hash')
            ->whereNotNull('blockchain_verified_at')
            ->count();

        return $onchainProjects >= 1 && !$this->hasBadge($user, 'VERIFIED_BUILDER');
    }

    /**
     * Check if user already has a badge
     */
    private function hasBadge(User $user, string $badgeType): bool
    {
        $badges = $user->badges ?? [];
        return in_array($badgeType, array_column($badges, 'type'));
    }

    /**
     * Award a badge to a user onchain
     */
    private function awardBadge(User $user, string $badgeType): ?array
    {
        if (!$user->wallet_address) {
            Log::warning("User {$user->id} has no wallet address, cannot award badge");
            return null;
        }

        if (!$this->adminPrivateKey) {
            Log::warning("No admin private key configured, cannot award badge onchain");
            // Award badge offchain only
            return $this->awardBadgeOffchain($user, $badgeType);
        }

        try {
            // Prepare badge metadata URI
            $metadataUri = $this->generateBadgeMetadataUri($user, $badgeType);

            // Call smart contract to mint badge
            $txHash = $this->mintBadgeOnchain($user->wallet_address, $badgeType, $metadataUri);

            if ($txHash) {
                // Save badge to database
                $this->saveBadgeToDatabase($user, $badgeType, $txHash);

                return [
                    'type' => $badgeType,
                    'tx_hash' => $txHash,
                    'status' => 'onchain',
                ];
            }
        } catch (\Exception $e) {
            Log::error("Failed to award badge onchain: " . $e->getMessage());
            // Fallback to offchain awarding
            return $this->awardBadgeOffchain($user, $badgeType);
        }

        return null;
    }

    /**
     * Award badge offchain (database only)
     */
    private function awardBadgeOffchain(User $user, string $badgeType): array
    {
        $this->saveBadgeToDatabase($user, $badgeType, null);

        return [
            'type' => $badgeType,
            'tx_hash' => null,
            'status' => 'offchain',
        ];
    }

    /**
     * Generate badge metadata URI
     */
    private function generateBadgeMetadataUri(User $user, string $badgeType): string
    {
        $badgeNames = [
            'PROJECT_COMPLETED' => 'Project Completed',
            'TOP_INNOVATOR' => 'Top Innovator',
            'MENTOR' => 'Mentor',
            'TASKS_100' => 'Task Master',
            'EARLY_ADOPTER' => 'Early Adopter',
            'VERIFIED_BUILDER' => 'Verified Builder',
        ];

        $metadata = [
            'name' => $badgeNames[$badgeType] ?? $badgeType,
            'description' => $this->getBadgeDescription($badgeType),
            'image' => $this->getBadgeImage($badgeType),
            'attributes' => [
                'type' => $badgeType,
                'owner' => $user->wallet_address,
                'awarded_at' => now()->toISOString(),
            ],
        ];

        // In production, this would be uploaded to IPFS or a metadata server
        // For now, return a placeholder URI
        return 'data:application/json;base64,' . base64_encode(json_encode($metadata));
    }

    /**
     * Get badge description
     */
    private function getBadgeDescription(string $badgeType): string
    {
        $descriptions = [
            'PROJECT_COMPLETED' => 'Successfully completed a project',
            'TOP_INNOVATOR' => 'Completed 5+ projects with innovative solutions',
            'MENTOR' => 'Helped guide other builders through comments and feedback',
            'TASKS_100' => 'Completed 100+ tasks across all projects',
            'EARLY_ADOPTER' => 'One of the first 1000 users to join Smart Project Hub',
            'VERIFIED_BUILDER' => 'Published projects on blockchain for verifiable ownership',
        ];

        return $descriptions[$badgeType] ?? 'Achievement badge';
    }

    /**
     * Get badge image URL
     */
    private function getBadgeImage(string $badgeType): string
    {
        $images = [
            'PROJECT_COMPLETED' => 'https://sph.example.com/badges/project-completed.png',
            'TOP_INNOVATOR' => 'https://sph.example.com/badges/top-innovator.png',
            'MENTOR' => 'https://sph.example.com/badges/mentor.png',
            'TASKS_100' => 'https://sph.example.com/badges/task-master.png',
            'EARLY_ADOPTER' => 'https://sph.example.com/badges/early-adopter.png',
            'VERIFIED_BUILDER' => 'https://sph.example.com/badges/verified-builder.png',
        ];

        return $images[$badgeType] ?? 'https://sph.example.com/badges/default.png';
    }

    /**
     * Mint badge on blockchain
     */
    private function mintBadgeOnchain(string $walletAddress, string $badgeType, string $metadataUri): ?string
    {
        // This would use ethers.js or similar to interact with the smart contract
        // For now, return a placeholder transaction hash
        // In production, this would:
        // 1. Create a wallet from admin private key
        // 2. Build the transaction data
        // 3. Sign and send the transaction
        // 4. Wait for confirmation
        // 5. Return the transaction hash

        Log::info("Minting badge {$badgeType} to {$walletAddress}");
        
        // Placeholder - in production, use actual smart contract interaction
        return '0x' . str_repeat('0', 64); // Placeholder
    }

    /**
     * Save badge to database
     */
    private function saveBadgeToDatabase(User $user, string $badgeType, ?string $txHash): void
    {
        $badges = $user->badges ?? [];
        
        $badges[] = [
            'type' => $badgeType,
            'tx_hash' => $txHash,
            'awarded_at' => now()->toISOString(),
        ];

        $user->badges = $badges;
        $user->save();
    }

    /**
     * Award badge manually (admin function)
     */
    public function awardBadgeManually(User $user, string $badgeType): array
    {
        return $this->awardBadge($user, $badgeType);
    }

    /**
     * Get all badges for a user
     */
    public function getUserBadges(User $user): array
    {
        return $user->badges ?? [];
    }

    /**
     * Revoke a badge (admin function)
     */
    public function revokeBadge(User $user, string $badgeType): bool
    {
        $badges = $user->badges ?? [];
        
        $filteredBadges = array_filter($badges, function ($badge) use ($badgeType) {
            return $badge['type'] !== $badgeType;
        });

        $user->badges = array_values($filteredBadges);
        $user->save();

        return true;
    }
}
