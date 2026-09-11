<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use ZipArchive;

class BackupSystem extends Command
{
    protected $signature = 'system:backup
        {--database-only : Exclude uploaded files}
        {--retention= : Days to retain local archives}
        {--secondary= : Optional secondary directory or network share for a verified copy}';
    protected $description = 'Create and verify a database and secure-file backup archive';

    public function handle(): int
    {
        $backupDirectory = storage_path('app/backups');
        File::ensureDirectoryExists($backupDirectory);

        $stamp = now()->format('Ymd-His');
        $archivePath = $backupDirectory . DIRECTORY_SEPARATOR . "mis-backup-{$stamp}.zip";
        $sqlPath = storage_path("app/backup-{$stamp}.sql");

        try {
            $process = new Process($this->dumpArguments());
            $process->setTimeout(300);
            $process->setEnv(['MYSQL_PWD' => (string) config('database.connections.mysql.password')]);
            $process->run();

            if (!$process->isSuccessful()) {
                $this->error('Database backup failed: ' . trim($process->getErrorOutput()));
                return self::FAILURE;
            }

            File::put($sqlPath, $process->getOutput());

            $zip = new ZipArchive();
            if ($zip->open($archivePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new \RuntimeException('Unable to create the backup archive.');
            }

            $zip->addFile($sqlPath, 'database.sql');
            $zip->addFromString('manifest.json', json_encode([
                'application' => config('app.name'),
                'created_at' => now()->toIso8601String(),
                'database' => config('database.connections.mysql.database'),
                'database_only' => (bool) $this->option('database-only'),
                'format_version' => 1,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            if (!$this->option('database-only')) {
                $this->addDirectory($zip, storage_path('app/secure'), 'files/secure');
                $this->addDirectory($zip, storage_path('app/private'), 'files/private', [
                    storage_path('app/backups'),
                ]);
            }

            $zip->close();
            File::delete($sqlPath);

            if (!$this->verifyArchive($archivePath)) {
                File::delete($archivePath);
                $this->error('Backup verification failed; the incomplete archive was removed.');
                return self::FAILURE;
            }

            $retention = max(1, (int) ($this->option('retention') ?: config('backup.retention_days', 30)));
            $this->prune($backupDirectory, $retention, $archivePath);
            $secondaryCopy = $this->mirrorArchive($archivePath);
            $this->info("Verified backup created: {$archivePath}");
            if ($secondaryCopy) {
                $this->info("Verified secondary copy: {$secondaryCopy}");
            }

            return self::SUCCESS;
        } catch (\Throwable $exception) {
            File::delete($sqlPath);
            if (File::exists($archivePath) && !$this->verifyArchive($archivePath)) {
                File::delete($archivePath);
            }
            report($exception);
            $this->error('Backup failed: ' . $exception->getMessage());
            return self::FAILURE;
        }
    }

    private function dumpArguments(): array
    {
        $binary = $this->mysqlBinary('MYSQLDUMP_PATH', 'mysqldump');
        $connection = config('database.connections.mysql');

        return [
            $binary,
            '--host=' . env('DB_WRITE_HOST', $connection['host']),
            '--port=' . $connection['port'],
            '--user=' . $connection['username'],
            '--single-transaction',
            '--routines',
            '--triggers',
            '--events',
            '--default-character-set=utf8mb4',
            $connection['database'],
        ];
    }

    private function mysqlBinary(string $environmentKey, string $command): string
    {
        $configured = config($environmentKey === 'MYSQLDUMP_PATH' ? 'backup.mysqldump_path' : 'backup.mysql_path');
        if ($configured) {
            return $configured;
        }

        $candidate = realpath(dirname(PHP_BINARY) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'mysql' . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . $command . '.exe');
        return $candidate ?: $command;
    }

    private function addDirectory(ZipArchive $zip, string $directory, string $prefix, array $excluded = []): void
    {
        if (!File::isDirectory($directory)) {
            return;
        }

        foreach (File::allFiles($directory) as $file) {
            $path = $file->getRealPath();
            if (collect($excluded)->contains(fn (string $excludedPath) => str_starts_with($path, $excludedPath))) {
                continue;
            }
            $relative = str_replace('\\', '/', $file->getRelativePathname());
            $zip->addFile($path, "{$prefix}/{$relative}");
        }
    }

    private function verifyArchive(string $path): bool
    {
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            return false;
        }
        $valid = $zip->locateName('manifest.json') !== false
            && $zip->locateName('database.sql') !== false
            && strlen((string) $zip->getFromName('database.sql')) > 100;
        $zip->close();
        return $valid;
    }

    private function prune(string $directory, int $days, string $current): void
    {
        foreach (File::glob($directory . DIRECTORY_SEPARATOR . 'mis-backup-*.zip') as $archive) {
            if ($archive !== $current && File::lastModified($archive) < now()->subDays($days)->timestamp) {
                File::delete($archive);
            }
        }
    }

    private function mirrorArchive(string $archivePath): ?string
    {
        $secondaryDirectory = trim((string) ($this->option('secondary') ?: config('backup.secondary_path')));
        if ($secondaryDirectory === '') {
            return null;
        }

        File::ensureDirectoryExists($secondaryDirectory);
        if (realpath(dirname($archivePath)) === realpath($secondaryDirectory)) {
            throw new \RuntimeException('The secondary backup path must be different from the local backup directory.');
        }

        $secondaryPath = rtrim($secondaryDirectory, '\\/')
            . DIRECTORY_SEPARATOR
            . basename($archivePath);

        if (!File::copy($archivePath, $secondaryPath)) {
            throw new \RuntimeException('Unable to copy the verified archive to the secondary backup path.');
        }

        $localHash = hash_file('sha256', $archivePath);
        $secondaryHash = hash_file('sha256', $secondaryPath);
        if (!$localHash || !$secondaryHash || !hash_equals($localHash, $secondaryHash)) {
            File::delete($secondaryPath);
            throw new \RuntimeException('The secondary backup checksum did not match the local archive.');
        }

        return $secondaryPath;
    }
}
