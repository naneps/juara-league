<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileService
{
    /**
     * Upload a file to the specified disk and path.
     *
     * @param UploadedFile $file
     * @param string $folder
     * @param string|null $disk
     * @return array
     */
    public function upload(UploadedFile $file, string $folder = 'uploads', ?string $disk = null): array
    {
        $disk = $disk ?: config('filesystems.default');
        
        // Generate unique filename
        $filename = Str::random(40) . '.' . $file->getClientOriginalExtension();
        
        // Store the file
        $path = $file->storeAs($folder, $filename, $disk);
        
        return [
            'path' => $path,
            'url' => Storage::disk($disk)->url($path),
            'filename' => $filename,
            'original_name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
        ];
    }

    /**
     * Delete a file from the specified disk.
     *
     * @param string|null $path
     * @param string|null $disk
     * @return bool
     */
    public function delete(?string $path, ?string $disk = null): bool
    {
        if (!$path) {
            return false;
        }

        $disk = $disk ?: config('filesystems.default');

        if (Storage::disk($disk)->exists($path)) {
            return Storage::disk($disk)->delete($path);
        }

        return false;
    }

    /**
     * Convert relative storage path or URL to just the path for deletion.
     *
     * @param string $url
     * @return string
     */
    public function getPathFromUrl(string $url): string
    {
        $storagePath = 'storage/';
        if (str_contains($url, $storagePath)) {
            return explode($storagePath, $url)[1];
        }
        
        return $url;
    }
}
