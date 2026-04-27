<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AvatarImageService
{
    public function storeOptimized(UploadedFile $file, int $userId, ?string $oldPath = null): string
    {
        $disk = Storage::disk('public');
        $path = 'avatars/' . $userId . '/' . Str::uuid() . '.webp';

        $optimized = $this->optimizeToWebp($file);
        if ($optimized !== null) {
            $disk->put($path, $optimized, ['visibility' => 'public']);
        } else {
            $path = $file->store('avatars/' . $userId, 'public');
        }

        if (filled($oldPath) && $oldPath !== $path) {
            $disk->delete((string) $oldPath);
        }

        return $path;
    }

    private function optimizeToWebp(UploadedFile $file): ?string
    {
        if (! function_exists('imagecreatefromstring') || ! function_exists('imagewebp')) {
            return null;
        }

        $sourceBytes = @file_get_contents($file->getRealPath());
        if ($sourceBytes === false) {
            return null;
        }

        $source = @imagecreatefromstring($sourceBytes);
        if ($source === false) {
            return null;
        }

        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);
        if ($sourceWidth <= 0 || $sourceHeight <= 0) {
            imagedestroy($source);
            return null;
        }

        $maxSize = 512;
        $ratio = min($maxSize / $sourceWidth, $maxSize / $sourceHeight, 1);
        $targetWidth = max(1, (int) round($sourceWidth * $ratio));
        $targetHeight = max(1, (int) round($sourceHeight * $ratio));

        $target = imagecreatetruecolor($targetWidth, $targetHeight);
        if ($target === false) {
            imagedestroy($source);
            return null;
        }

        imagealphablending($target, false);
        imagesavealpha($target, true);
        $transparent = imagecolorallocatealpha($target, 0, 0, 0, 127);
        imagefilledrectangle($target, 0, 0, $targetWidth, $targetHeight, $transparent);

        $resampled = imagecopyresampled(
            $target,
            $source,
            0,
            0,
            0,
            0,
            $targetWidth,
            $targetHeight,
            $sourceWidth,
            $sourceHeight
        );

        if (! $resampled) {
            imagedestroy($target);
            imagedestroy($source);
            return null;
        }

        ob_start();
        $encoded = imagewebp($target, null, 82);
        $output = ob_get_clean();

        imagedestroy($target);
        imagedestroy($source);

        if (! $encoded || ! is_string($output) || $output === '') {
            return null;
        }

        return $output;
    }
}

