<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Web3Controller extends Controller
{
    public function profile(): View
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Get user's onchain projects
        $onchainProjects = Project::query()
            ->where('user_id', $user->id)
            ->whereNotNull('transaction_hash')
            ->whereNotNull('blockchain_verified_at')
            ->withCount(['tasks', 'budgets'])
            ->latest('blockchain_verified_at')
            ->get();

        // Get user's badges (placeholder for now)
        $badges = $this->getUserBadges($user);

        // Get wallet connection stats
        $walletStats = $this->getWalletStats($user);

        return view('pages.web3-profile', compact(
            'onchainProjects',
            'badges',
            'walletStats'
        ));
    }

    public function verification(): View
    {
        return view('pages.web3-verification');
    }

    public function verifyProject(Request $request): JsonResponse
    {
        $request->validate([
            'identifier' => ['required', 'string', 'max:255'],
        ]);

        $identifier = $request->input('identifier');
        
        // Check if it's a project hash or wallet address
        if (strlen($identifier) === 66 && str_starts_with($identifier, '0x')) {
            // It's a project hash
            $project = Project::query()
                ->where('project_hash', $identifier)
                ->whereNotNull('transaction_hash')
                ->with('user')
                ->first();

            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Project not found or not verified onchain.'
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'type' => 'project',
                    'project' => [
                        'title' => $project->title,
                        'description' => $project->description,
                        'hash' => $project->project_hash,
                        'owner' => $project->user->name,
                        'wallet_address' => $project->user->wallet_address,
                        'transaction_hash' => $project->transaction_hash,
                        'verified_at' => $project->blockchain_verified_at->toISOString(),
                        'explorer_link' => "https://sepolia.basescan.org/tx/{$project->transaction_hash}",
                    ]
                ]
            ]);
        } elseif (strlen($identifier) === 42 && str_starts_with($identifier, '0x')) {
            // It's a wallet address
            $user = User::query()
                ->where('wallet_address', $identifier)
                ->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'No user found with this wallet address.'
                ]);
            }

            $projects = Project::query()
                ->where('user_id', $user->id)
                ->whereNotNull('transaction_hash')
                ->latest('blockchain_verified_at')
                ->get(['title', 'project_hash', 'transaction_hash', 'blockchain_verified_at']);

            return response()->json([
                'success' => true,
                'data' => [
                    'type' => 'wallet',
                    'owner' => $user->name,
                    'wallet_address' => $user->wallet_address,
                    'projects_count' => $projects->count(),
                    'projects' => $projects->map(function ($project) {
                        return [
                            'title' => $project->title,
                            'hash' => $project->project_hash,
                            'transaction_hash' => $project->transaction_hash,
                            'verified_at' => $project->blockchain_verified_at->toISOString(),
                            'explorer_link' => "https://sepolia.basescan.org/tx/{$project->transaction_hash}",
                        ];
                    }),
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid identifier. Please provide a valid project hash or wallet address.'
        ]);
    }

    public function publishProject(Request $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);

        $request->validate([
            'transaction_hash' => ['required', 'string', 'size:66'],
            'wallet_address' => ['required', 'string', 'size:42'],
        ]);

        try {
            // Update project with blockchain data
            $project->update([
                'transaction_hash' => $request->input('transaction_hash'),
                'wallet_address' => $request->input('wallet_address'),
                'blockchain_verified_at' => now(),
            ]);

            // Update user's wallet address if not set
            $user = Auth::user();
            if (!$user->wallet_address) {
                $user->update(['wallet_address' => $request->input('wallet_address')]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Project successfully published onchain!',
                'data' => [
                    'explorer_link' => "https://sepolia.basescan.org/tx/{$request->input('transaction_hash')}",
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to publish project onchain. Please try again.'
            ], 500);
        }
    }

    private function getUserBadges(User $user): array
    {
        // Placeholder badges - will be expanded with actual badge logic
        $badges = [];

        // Project Completed Badge
        $completedProjects = $user->projects()
            ->where('status', 'completed')
            ->count();
        
        if ($completedProjects > 0) {
            $badges[] = [
                'id' => 'project_completed',
                'name' => 'Project Completed',
                'description' => "Completed {$completedProjects} project(s)",
                'icon' => '🎯',
                'earned_at' => $user->projects()
                    ->where('status', 'completed')
                    ->latest('updated_at')
                    ->first()?->updated_at?->toISOString(),
                'rarity' => 'common'
            ];
        }

        // Early Adopter Badge
        $badges[] = [
            'id' => 'early_adopter',
            'name' => 'Early Adopter',
            'description' => 'One of the first to use Web3 features',
            'icon' => '🚀',
            'earned_at' => $user->created_at->toISOString(),
            'rarity' => 'rare'
        ];

        return $badges;
    }

    private function getWalletStats(User $user): array
    {
        return [
            'wallet_connected' => !is_null($user->wallet_address),
            'wallet_address' => $user->wallet_address,
            'onchain_projects' => $user->projects()
                ->whereNotNull('transaction_hash')
                ->count(),
            'total_transactions' => $user->projects()
                ->whereNotNull('transaction_hash')
                ->count(),
            'first_transaction' => $user->projects()
                ->whereNotNull('transaction_hash')
                ->oldest('blockchain_verified_at')
                ->first()?->blockchain_verified_at?->toISOString(),
            'network' => 'Base Sepolia',
            'explorer_link' => $user->wallet_address 
                ? "https://sepolia.basescan.org/address/{$user->wallet_address}"
                : null,
        ];
    }
}
