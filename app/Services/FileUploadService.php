<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\FileUploadLog;
use App\Models\Setting;
use Exception;

/**
 * Secure File Upload Service
 *
 * Handles:
 * - Secure file storage outside web root
 * - Random filename generation
 * - File validation
 * - Audit logging
 * - File cleanup
 */
class FileUploadService
{
    /**
     * Allowed file extensions
     *
     * @var array<string>
     */
    private array $allowedExtensions = [
        'pdf',
        'doc',
        'docx',
        'xls',
        'xlsx',
        'jpg',
        'jpeg',
        'png',
        'gif',
    ];

    /**
     * Max file size in bytes (10MB)
     *
     * @var int
     */
    private int $maxFileSize = 10485760; // 10MB

    /**
     * Storage disk (configured outside web root)
     *
     * @var string
     */
    private string $disk = 'secure';

    /**
     * Upload a file securely
     *
     * @param  UploadedFile  $file
     * @param  string  $category  Category/folder for organizing uploads
     * @param  array<string, mixed>  $metadata  Additional metadata to store
     * @return array<string, mixed>  Upload result with file ID and path
     * @throws Exception
     */
    public function upload(UploadedFile $file, string $category = 'general', array $metadata = []): array
    {
        // Validate file
        $this->validateFile($file);

        // Generate secure filename
        $filename = $this->generateSecureFilename($file);
        $path = "{$category}/{$filename}";

        try {
            // Get file content
            $fileContent = file_get_contents($file->getRealPath());

            // Strip EXIF metadata from images (privacy protection)
            if ($this->isImageFile($file)) {
                $fileContent = $this->stripExifMetadata($fileContent, $file->getMimeType());
            }

            // Store file
            Storage::disk($this->disk)->put($path, $fileContent);

            // Log file upload
            $uploadLog = FileUploadLog::create([
                'user_id' => auth()->id(),
                'original_filename' => $file->getClientOriginalName(),
                'stored_filename' => $filename,
                'file_path' => $path,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'category' => $category,
                'ip_address' => request()->ip(),
                'user_agent' => request()->header('User-Agent'),
                'metadata' => $metadata,
            ]);

            return [
                'success' => true,
                'file_id' => $uploadLog->id,
                'filename' => $filename,
                'original_filename' => $file->getClientOriginalName(),
                'path' => $path,
                'size' => $file->getSize(),
                'message' => 'File uploaded successfully',
            ];
        } catch (Exception $e) {
            // Log error
            Log::error('File upload failed', [
                'user_id' => auth()->id(),
                'file' => $file->getClientOriginalName(),
                'error' => $e->getMessage(),
            ]);

            throw new Exception('Failed to upload file. Please try again.');
        }
    }

    /**
     * Download a file securely
     *
     * @param  int  $fileId
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     * @throws Exception
     */
    public function download(int $fileId)
    {
        $uploadLog = FileUploadLog::findOrFail($fileId);

        // Verify user can download this file
        if ($uploadLog->user_id !== auth()->id()) {
            throw new Exception('Unauthorized to download this file.');
        }

        // Check file exists
        if (!Storage::disk($this->disk)->exists($uploadLog->file_path)) {
            throw new Exception('File not found.');
        }

        // Log download
        $uploadLog->update([
            'last_downloaded_at' => now(),
            'download_count' => $uploadLog->download_count + 1,
        ]);

        // Download file
        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk($this->disk);

        return $disk->download(
            $uploadLog->file_path,
            $uploadLog->original_filename
        );
    }

    /**
     * Delete a file securely
     *
     * @param  int  $fileId
     * @return bool
     * @throws Exception
     */
    public function delete(int $fileId): bool
    {
        $uploadLog = FileUploadLog::findOrFail($fileId);

        // Verify user can delete this file
        if ($uploadLog->user_id !== auth()->id() && !auth()->user()->is_admin) {
            throw new Exception('Unauthorized to delete this file.');
        }

        try {
            // Delete from storage
            if (Storage::disk($this->disk)->exists($uploadLog->file_path)) {
                Storage::disk($this->disk)->delete($uploadLog->file_path);
            }

            // Delete from database
            $uploadLog->delete();

            Log::info('File deleted', [
                'file_id' => $fileId,
                'user_id' => auth()->id(),
                'filename' => $uploadLog->original_filename,
            ]);

            return true;
        } catch (Exception $e) {
            Log::error('File deletion failed', [
                'file_id' => $fileId,
                'error' => $e->getMessage(),
            ]);

            throw new Exception('Failed to delete file.');
        }
    }

    /**
     * Validate file before upload
     *
     * @param  UploadedFile  $file
     * @return void
     * @throws Exception
     */
    private function validateFile(UploadedFile $file): void
    {
        // Check file size
        $maxMegabytes = max(1, min(20, (int) Setting::get('max_upload_size', 10)));
        if ($file->getSize() > $maxMegabytes * 1024 * 1024) {
            throw new Exception("File size exceeds maximum limit of {$maxMegabytes}MB.");
        }

        // Check file extension
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, $this->allowedExtensions, true)) {
            throw new Exception('File type not allowed. Allowed types: ' . implode(', ', $this->allowedExtensions));
        }

        // Check MIME type
        if (!$this->isValidMimeType($file)) {
            throw new Exception('File MIME type does not match extension.');
        }
    }

    /**
     * Validate MIME type matches extension
     *
     * @param  UploadedFile  $file
     * @return bool
     */
    private function isValidMimeType(UploadedFile $file): bool
    {
        $mimeType = $file->getMimeType();
        $extension = strtolower($file->getClientOriginalExtension());

        $allowedMimes = [
            'pdf' => ['application/pdf'],
            'doc' => ['application/msword'],
            'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
            'xls' => ['application/vnd.ms-excel'],
            'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
            'jpg' => ['image/jpeg'],
            'jpeg' => ['image/jpeg'],
            'png' => ['image/png'],
            'gif' => ['image/gif'],
        ];

        if (!isset($allowedMimes[$extension])) {
            return false;
        }

        return in_array($mimeType, $allowedMimes[$extension], true);
    }

    /**
     * Generate a secure random filename
     *
     * @param  UploadedFile  $file
     * @return string
     */
    private function generateSecureFilename(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $randomName = Str::random(32);

        return "{$randomName}.{$extension}";
    }

    /**
     * Get user's uploaded files
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserFiles()
    {
        return FileUploadLog::where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Clean up old/abandoned files (older than 90 days)
     *
     * @return int  Number of files deleted
     */
    public function cleanupOldFiles(): int
    {
        $cutoffDate = now()->subDays(90);

        $oldFiles = FileUploadLog::where('created_at', '<', $cutoffDate)
            ->where('is_archived', true)
            ->get();

        $count = 0;

        foreach ($oldFiles as $file) {
            try {
                if (Storage::disk($this->disk)->exists($file->file_path)) {
                    Storage::disk($this->disk)->delete($file->file_path);
                }
                $file->delete();
                $count++;
            } catch (Exception $e) {
                Log::error('Failed to delete old file', [
                    'file_id' => $file->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $count;
    }

    public function downloadByPath(string $path, string $downloadName)
    {
        if (!Storage::disk($this->disk)->exists($path)) throw new Exception('File not found.');
        FileUploadLog::where('file_path', $path)->increment('download_count');
        FileUploadLog::where('file_path', $path)->update(['last_downloaded_at' => now()]);

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk($this->disk);

        return $disk->download($path, $downloadName);
    }

    public function deleteByPath(string $path): void
    {
        if (Storage::disk($this->disk)->exists($path)) Storage::disk($this->disk)->delete($path);
        FileUploadLog::where('file_path', $path)->update(['is_archived' => true]);
    }

    /**
     * Check if file is an image that may contain EXIF data
     *
     * @param  UploadedFile  $file
     * @return bool
     */
    private function isImageFile(UploadedFile $file): bool
    {
        $mimeType = strtolower($file->getMimeType());
        return in_array($mimeType, ['image/jpeg', 'image/png'], true);
    }

    /**
     * Strip EXIF metadata from image to protect user privacy
     * Removes location, timestamp, device info, and other sensitive metadata
     *
     * @param  string  $imageData  Raw image file content
     * @param  string  $mimeType   Image MIME type
     * @return string              Image data with EXIF stripped
     */
    private function stripExifMetadata(string $imageData, string $mimeType): string
    {
        try {
            $mimeType = strtolower($mimeType);

            // Only strip EXIF from JPEG and PNG
            if ($mimeType === 'image/jpeg') {
                return $this->stripJpegExif($imageData);
            } elseif ($mimeType === 'image/png') {
                return $this->stripPngExif($imageData);
            }

            return $imageData;
        } catch (Exception $e) {
            Log::warning('Failed to strip EXIF metadata', [
                'mime_type' => $mimeType,
                'error' => $e->getMessage(),
            ]);
            // Return original image if stripping fails
            return $imageData;
        }
    }

    /**
     * Strip EXIF data from JPEG image using GD library
     *
     * @param  string  $jpegData  Raw JPEG data
     * @return string             JPEG data with EXIF removed
     */
    private function stripJpegExif(string $jpegData): string
    {
        if (!extension_loaded('gd')) {
            Log::warning('GD extension not loaded; EXIF data may not be stripped');
            return $jpegData;
        }

        try {
            $image = @imagecreatefromstring($jpegData);
            if ($image === false) {
                return $jpegData;
            }

            // Re-encode JPEG without EXIF (imagejpeg strips all metadata)
            ob_start();
            imagejpeg($image, null, 85);
            $cleanedData = ob_get_clean();
            imagedestroy($image);

            return $cleanedData ?: $jpegData;
        } catch (Exception $e) {
            return $jpegData;
        }
    }

    /**
     * Strip EXIF data from PNG image using GD library
     *
     * @param  string  $pngData  Raw PNG data
     * @return string            PNG data with EXIF removed
     */
    private function stripPngExif(string $pngData): string
    {
        if (!extension_loaded('gd')) {
            Log::warning('GD extension not loaded; EXIF data may not be stripped');
            return $pngData;
        }

        try {
            $image = @imagecreatefromstring($pngData);
            if ($image === false) {
                return $pngData;
            }

            // Re-encode PNG without EXIF (imagepng strips all metadata)
            ob_start();
            imagepng($image, null, 9);
            $cleanedData = ob_get_clean();
            imagedestroy($image);

            return $cleanedData ?: $pngData;
        } catch (Exception $e) {
            return $pngData;
        }
    }
}
