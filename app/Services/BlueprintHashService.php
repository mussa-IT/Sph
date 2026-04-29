<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class BlueprintHashService
{
    /**
     * Calculate SHA256 hash of a file
     */
    public function calculateFileHash(string $filePath): string
    {
        if (!file_exists($filePath)) {
            throw new \Exception("File not found: {$filePath}");
        }

        return hash_file('sha256', $filePath);
    }

    /**
     * Calculate hash from uploaded file
     */
    public function calculateUploadedFileHash($file): string
    {
        return hash_file('sha256', $file->getRealPath());
    }

    /**
     * Verify file integrity by comparing hashes
     */
    public function verifyFileHash(string $filePath, string $expectedHash): bool
    {
        $actualHash = $this->calculateFileHash($filePath);
        return hash_equals($expectedHash, $actualHash);
    }

    /**
     * Generate blockchain hash (shorter version for onchain storage)
     */
    public function generateBlockchainHash(string $fileHash): string
    {
        // Use first 32 bytes of SHA256 for onchain storage
        return '0x' . substr($fileHash, 0, 64);
    }

    /**
     * Anchor hash on blockchain (placeholder for smart contract interaction)
     */
    public function anchorHashOnchain(string $blockchainHash, string $walletAddress): ?string
    {
        // In production, this would interact with a smart contract
        // For now, return a placeholder transaction hash
        return '0x' . Str::random(64);
    }

    /**
     * Verify hash on blockchain (placeholder)
     */
    public function verifyOnchainHash(string $blockchainHash): bool
    {
        // In production, this would query the smart contract
        // For now, return true if hash format is valid
        return strlen($blockchainHash) === 66 && str_starts_with($blockchainHash, '0x');
    }

    /**
     * Get file metadata
     */
    public function getFileMetadata($file): array
    {
        return [
            'name' => $file->getClientOriginalName(),
            'type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
            'extension' => $file->getClientOriginalExtension(),
        ];
    }

    /**
     * Store blueprint file
     */
    public function storeBlueprint($file, string $directory = 'blueprints'): string
    {
        $fileName = time() . '_' . Str::random(40) . '.' . $file->getClientOriginalExtension();
        return $file->storeAs($directory, $fileName, 'public');
    }

    /**
     * Delete blueprint file
     */
    public function deleteBlueprint(string $filePath): bool
    {
        return Storage::disk('public')->delete($filePath);
    }
}
