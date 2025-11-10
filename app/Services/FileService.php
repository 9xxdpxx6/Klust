<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileService
{
    /**
     * Store avatar file
     *
     * @return string Path to stored file
     */
    public function storeAvatar(UploadedFile $file): string
    {
        // Validate file
        $this->validateImage($file, 5120); // 5MB max

        // Generate unique filename
        $filename = $this->generateUniqueFilename($file, 'avatar');

        // Store file in public disk
        $path = $file->storeAs('avatars', $filename, 'public');

        return $path;
    }

    /**
     * Store partner logo file
     *
     * @return string Path to stored file
     */
    public function storeLogo(UploadedFile $file): string
    {
        // Validate file
        $this->validateImage($file, 5120); // 5MB max

        // Generate unique filename
        $filename = $this->generateUniqueFilename($file, 'logo');

        // Store file in public disk
        $path = $file->storeAs('logos', $filename, 'public');

        return $path;
    }

    /**
     * Store badge icon file
     *
     * @return string Path to stored file
     */
    public function storeBadgeIcon(UploadedFile $file): string
    {
        // Validate file
        $this->validateImage($file, 2048); // 2MB max

        // Generate unique filename
        $filename = $this->generateUniqueFilename($file, 'badge');

        // Store file in public disk
        $path = $file->storeAs('badge-icons', $filename, 'public');

        return $path;
    }

    /**
     * Store simulator preview image
     *
     * @return string Path to stored file
     */
    public function storeSimulatorPreview(UploadedFile $file): string
    {
        // Validate file
        $this->validateImage($file, 10240); // 10MB max

        // Generate unique filename
        $filename = $this->generateUniqueFilename($file, 'simulator');

        // Store file in public disk
        $path = $file->storeAs('simulator-previews', $filename, 'public');

        return $path;
    }

    /**
     * Delete file from storage
     */
    public function deleteFile(string $path): bool
    {
        if (empty($path)) {
            return false;
        }

        // Check if file exists and delete
        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->delete($path);
        }

        return false;
    }

    /**
     * Validate image file
     *
     * @param  int  $maxSizeKb  Maximum size in kilobytes
     *
     * @throws \Exception
     */
    private function validateImage(UploadedFile $file, int $maxSizeKb): void
    {
        // Check if file is an image
        if (! in_array($file->getMimeType(), ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/svg+xml'])) {
            throw new \Exception('File must be an image (jpeg, png, jpg, gif, svg)');
        }

        // Check file size
        if ($file->getSize() > $maxSizeKb * 1024) {
            throw new \Exception("File size must not exceed {$maxSizeKb}KB");
        }
    }

    /**
     * Generate unique filename
     */
    private function generateUniqueFilename(UploadedFile $file, string $prefix = 'file'): string
    {
        $extension = $file->getClientOriginalExtension();
        $randomString = Str::random(32);
        $timestamp = time();

        return "{$prefix}_{$timestamp}_{$randomString}.{$extension}";
    }

    /**
     * Get file URL
     */
    public function getFileUrl(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }
}
