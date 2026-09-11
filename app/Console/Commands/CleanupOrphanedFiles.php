<?php

namespace App\Console\Commands;

use App\Models\FileUploadLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Exception;

class CleanupOrphanedFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'files:cleanup-orphaned
                            {--dry-run : Show what would be deleted without actually deleting}
                            {--days=90 : Delete files not accessed in this many days}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove orphaned files from storage that are no longer referenced in the database or are too old';

    /**
     * Execute the command.
     */
    public function handle(): int
    {
        $disk = 'secure';
        $dryRun = $this->option('dry-run');
        $daysOld = (int) $this->option('days');
        $cutoffDate = now()->subDays($daysOld);

        $this->info("🔍 Scanning for orphaned files in '{$disk}' disk...");
        $this->info("Dry-run mode: " . ($dryRun ? 'YES' : 'NO'));
        $this->info("Cutoff date: {$cutoffDate->format('Y-m-d H:i:s')} ({$daysOld} days ago)");
        $this->info('');

        try {
            // Get all files from storage
            $allFiles = $this->getAllFiles($disk);
            $this->info("Found {$allFiles->count()} total files in storage");

            // Get all tracked files from database
            $trackedFiles = FileUploadLog::pluck('file_path')->toArray();
            $this->info("Found " . count($trackedFiles) . " tracked files in database");

            // Find orphaned files (not in database)
            $orphanedFiles = $allFiles->filter(fn($file) => !in_array($file, $trackedFiles));
            $this->info("Found {$orphanedFiles->count()} orphaned files (not in database)");

            // Find old unaccessed files (in database but old)
            $oldFiles = FileUploadLog::where(function ($query) use ($cutoffDate) {
                $query->whereNull('last_downloaded_at')
                    ->where('created_at', '<', $cutoffDate);
            })
                ->orWhere(function ($query) use ($cutoffDate) {
                    $query->whereNotNull('last_downloaded_at')
                        ->where('last_downloaded_at', '<', $cutoffDate);
                })
                ->get();

            $this->info("Found {$oldFiles->count()} old, unaccessed tracked files");

            // Display what will be deleted
            $this->line('');
            $this->info('📋 Orphaned Files (to be deleted):');
            foreach ($orphanedFiles as $file) {
                $size = $this->getFileSize($disk, $file);
                $this->info("  - {$file} ({$size})");
            }

            $this->line('');
            $this->info('📋 Old Unaccessed Files (to be deleted):');
            foreach ($oldFiles as $log) {
                $size = $this->formatBytes($log->file_size);
                $this->info("  - {$log->file_path} ({$size})");
                if ($log->last_downloaded_at) {
                    $this->info("    Last accessed: {$log->last_downloaded_at->format('Y-m-d H:i:s')}");
                } else {
                    $this->info("    Created: {$log->created_at->format('Y-m-d H:i:s')} (never accessed)");
                }
            }

            $totalFiles = $orphanedFiles->count() + $oldFiles->count();
            $totalSize = $this->calculateTotalSize($disk, $orphanedFiles) + $oldFiles->sum('file_size');

            $this->line('');
            $this->line("-------------------------------------------");
            $this->info("Total files to delete: {$totalFiles}");
            $this->info("Total space to free: " . $this->formatBytes($totalSize));
            $this->line("-------------------------------------------");

            // Return early if dry-run
            if ($dryRun) {
                $this->line('');
                $this->warn('⚠️  DRY-RUN MODE: No files were actually deleted.');
                $this->info('Run without --dry-run to actually delete files.');
                return 0;
            }

            // Confirm before deletion
            if (!$this->confirm('❌ Delete these files permanently?')) {
                $this->info('Operation cancelled.');
                return 0;
            }

            $deletedCount = 0;
            $deletedSize = 0;

            // Delete orphaned files from storage
            foreach ($orphanedFiles as $file) {
                try {
                    if (Storage::disk($disk)->exists($file)) {
                        $size = Storage::disk($disk)->size($file);
                        Storage::disk($disk)->delete($file);
                        $deletedCount++;
                        $deletedSize += $size;
                    }
                } catch (Exception $e) {
                    $this->error("Failed to delete {$file}: " . $e->getMessage());
                    Log::error('Failed to delete orphaned file', [
                        'file' => $file,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Delete old unaccessed file records and their files
            foreach ($oldFiles as $log) {
                try {
                    if (Storage::disk($disk)->exists($log->file_path)) {
                        $size = Storage::disk($disk)->size($log->file_path);
                        Storage::disk($disk)->delete($log->file_path);
                        $deletedSize += $size;
                    }
                    $log->delete();
                    $deletedCount++;
                } catch (Exception $e) {
                    $this->error("Failed to delete {$log->file_path}: " . $e->getMessage());
                    Log::error('Failed to delete old file', [
                        'file_id' => $log->id,
                        'file' => $log->file_path,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $this->line('');
            $this->info("✅ Cleanup complete!");
            $this->info("Deleted: {$deletedCount} files");
            $this->info("Freed: " . $this->formatBytes($deletedSize));

            Log::info('File cleanup completed', [
                'files_deleted' => $deletedCount,
                'bytes_freed' => $deletedSize,
                'orphaned_count' => $orphanedFiles->count(),
                'old_files_count' => $oldFiles->count(),
            ]);

            return 0;
        } catch (Exception $e) {
            $this->error("❌ Error during cleanup: " . $e->getMessage());
            Log::error('File cleanup failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return 1;
        }
    }

    /**
     * Get all files recursively from the disk
     */
    private function getAllFiles(string $disk): Collection
    {
        try {
            return collect(Storage::disk($disk)->allFiles());
        } catch (Exception $e) {
            Log::warning("Failed to scan disk '{$disk}'", ['error' => $e->getMessage()]);
            return collect();
        }
    }

    /**
     * Get file size for display
     */
    private function getFileSize(string $disk, string $filePath): string
    {
        try {
            $size = Storage::disk($disk)->size($filePath);
            return $this->formatBytes($size);
        } catch (Exception $e) {
            return 'unknown';
        }
    }

    /**
     * Calculate total size of files
     */
    private function calculateTotalSize(string $disk, Collection $files): int
    {
        $total = 0;

        foreach ($files as $file) {
            try {
                $total += Storage::disk($disk)->size($file);
            } catch (Exception $e) {
                // Skip if file doesn't exist or can't be accessed
            }
        }

        return $total;
    }

    /**
     * Format bytes to human-readable size
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
