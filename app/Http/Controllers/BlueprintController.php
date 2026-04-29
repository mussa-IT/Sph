<?php

namespace App\Http\Controllers;

use App\Models\Blueprint;
use App\Models\Project;
use App\Services\BlueprintHashService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class BlueprintController extends Controller
{
    private BlueprintHashService $hashService;

    public function __construct(BlueprintHashService $hashService)
    {
        $this->hashService = $hashService;
    }

    public function index()
    {
        $blueprints = Auth::user()->blueprints()->latest()->get();
        return view('pages.blueprints', compact('blueprints'));
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'file' => ['required', 'file', 'mimes:pdf,png,jpg,jpeg', 'max:10240'], // 10MB
            'anchor_onchain' => ['nullable', 'boolean'],
        ]);

        try {
            $file = $request->file('file');
            $metadata = $this->hashService->getFileMetadata($file);
            
            // Calculate file hash
            $fileHash = $this->hashService->calculateUploadedFileHash($file);
            
            // Store file
            $filePath = $this->hashService->storeBlueprint($file);
            
            // Generate blockchain hash
            $blockchainHash = $this->hashService->generateBlockchainHash($fileHash);
            
            // Create blueprint record
            $blueprint = Blueprint::create([
                'user_id' => Auth::id(),
                'project_id' => $request->project_id,
                'title' => $request->title,
                'description' => $request->description,
                'file_path' => $filePath,
                'file_name' => $metadata['name'],
                'file_type' => $metadata['type'],
                'file_size' => $metadata['size'],
                'file_hash' => $fileHash,
                'blockchain_hash' => null,
                'transaction_hash' => null,
                'blockchain_anchored_at' => null,
                'is_verified' => false,
            ]);

            // Anchor onchain if requested
            if ($request->boolean('anchor_onchain') && Auth::user()->wallet_address) {
                $txHash = $this->hashService->anchorHashOnchain($blockchainHash, Auth::user()->wallet_address);
                
                if ($txHash) {
                    $blueprint->update([
                        'blockchain_hash' => $blockchainHash,
                        'transaction_hash' => $txHash,
                        'blockchain_anchored_at' => now(),
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Blueprint uploaded successfully',
                'data' => $blueprint,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload blueprint: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show(Blueprint $blueprint)
    {
        $this->authorize('view', $blueprint);
        
        return view('pages.blueprint-detail', compact('blueprint'));
    }

    public function anchorOnchain(Request $request, Blueprint $blueprint): JsonResponse
    {
        $this->authorize('update', $blueprint);

        if (!Auth::user()->wallet_address) {
            return response()->json([
                'success' => false,
                'message' => 'Please connect your wallet first',
            ], 400);
        }

        try {
            $blockchainHash = $this->hashService->generateBlockchainHash($blueprint->file_hash);
            $txHash = $this->hashService->anchorHashOnchain($blockchainHash, Auth::user()->wallet_address);
            
            if ($txHash) {
                $blueprint->update([
                    'blockchain_hash' => $blockchainHash,
                    'transaction_hash' => $txHash,
                    'blockchain_anchored_at' => now(),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Blueprint anchored on blockchain',
                    'data' => [
                        'transaction_hash' => $txHash,
                        'explorer_link' => "https://sepolia.basescan.org/tx/{$txHash}",
                    ],
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to anchor blueprint on blockchain',
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to anchor blueprint: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Blueprint $blueprint): JsonResponse
    {
        $this->authorize('delete', $blueprint);

        try {
            // Delete file from storage
            $this->hashService->deleteBlueprint($blueprint->file_path);
            
            // Delete database record
            $blueprint->delete();

            return response()->json([
                'success' => true,
                'message' => 'Blueprint deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete blueprint: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function verify(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file'],
            'blueprint_id' => ['required', 'exists:blueprints,id'],
        ]);

        $blueprint = Blueprint::findOrFail($request->blueprint_id);
        $this->authorize('view', $blueprint);

        try {
            $uploadedFile = $request->file('file');
            $uploadedHash = $this->hashService->calculateUploadedFileHash($uploadedFile);
            
            $isMatch = $this->hashService->verifyFileHash(
                Storage::disk('public')->path($blueprint->file_path),
                $uploadedHash
            );

            $isAuthentic = $isMatch && $blueprint->is_anchored;

            return response()->json([
                'success' => true,
                'data' => [
                    'is_authentic' => $isAuthentic,
                    'hash_matches' => $isMatch,
                    'stored_hash' => $blueprint->file_hash,
                    'uploaded_hash' => $uploadedHash,
                    'is_anchored' => $blueprint->is_anchored,
                    'blockchain_hash' => $blueprint->blockchain_hash,
                    'transaction_hash' => $blueprint->transaction_hash,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to verify blueprint: ' . $e->getMessage(),
            ], 500);
        }
    }
}
