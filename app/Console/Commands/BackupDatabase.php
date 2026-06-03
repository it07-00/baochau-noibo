<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class BackupDatabase extends Command
{
    protected $signature = 'db:backup
                            {--keep=30 : Số ngày giữ lại backup (xóa cũ hơn)}';

    protected $description = 'Sao lưu database MySQL ra file .sql và xóa backup cũ';

    public function handle(): int
    {
        $backupDir = storage_path('app/backups');

        if (!File::isDirectory($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        $mysqldump = $this->findMysqldump();
        if (!$mysqldump) {
            $this->error('Không tìm thấy mysqldump. Đặt MYSQLDUMP_PATH trong .env.');
            Log::error('[db:backup] Không tìm thấy mysqldump.');
            return self::FAILURE;
        }

        $db       = config('database.connections.mysql');
        $filename = 'backup_' . now()->format('Y-m-d_His') . '.sql';
        $fullPath = $backupDir . DIRECTORY_SEPARATOR . $filename;

        // Ghi credentials vào file tạm để tránh lộ password trên command line
        $cnfPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'mysql_bc_' . uniqid() . '.cnf';
        file_put_contents($cnfPath, implode("\n", [
            '[client]',
            'host='     . $db['host'],
            'port='     . ($db['port'] ?? '3306'),
            'user='     . $db['username'],
            'password=' . $db['password'],
        ]));
        if (PHP_OS_FAMILY !== 'Windows') {
            chmod($cnfPath, 0600);
        }

        $exitCode = null;
        $stderr   = '';

        try {
            $cmd = [
                $mysqldump,
                '--defaults-extra-file=' . $cnfPath,
                '--single-transaction',
                '--routines',
                '--triggers',
                '--add-drop-table',
                $db['database'],
            ];

            $descriptors = [
                0 => ['pipe', 'r'],
                1 => ['file', $fullPath, 'w'],
                2 => ['pipe', 'w'],
            ];

            $process = proc_open($cmd, $descriptors, $pipes);
            if (!is_resource($process)) {
                throw new \RuntimeException('Không thể khởi chạy mysqldump.');
            }

            fclose($pipes[0]);
            $stderr   = stream_get_contents($pipes[2]);
            fclose($pipes[2]);
            $exitCode = proc_close($process);
        } finally {
            if (file_exists($cnfPath)) {
                unlink($cnfPath);
            }
        }

        if ($exitCode !== 0 || !File::exists($fullPath) || File::size($fullPath) === 0) {
            if (File::exists($fullPath)) {
                File::delete($fullPath);
            }
            $this->error("Backup thất bại (exit={$exitCode}): {$stderr}");
            Log::error("[db:backup] Thất bại (exit={$exitCode}): {$stderr}");
            return self::FAILURE;
        }

        $sizeMb = round(File::size($fullPath) / 1024 / 1024, 2);
        $this->info("Backup thành công: {$filename} ({$sizeMb} MB)");
        Log::info("[db:backup] Thành công: {$filename} ({$sizeMb} MB)");

        // Xóa backup cũ
        $keepDays = max(1, (int) $this->option('keep'));
        $this->pruneOldBackups($backupDir, $keepDays);

        return self::SUCCESS;
    }

    private function pruneOldBackups(string $backupDir, int $keepDays): void
    {
        $cutoff = now()->subDays($keepDays)->timestamp;
        $files  = File::glob($backupDir . DIRECTORY_SEPARATOR . 'backup_*.sql');
        $deleted = 0;

        foreach ($files as $file) {
            if (File::lastModified($file) < $cutoff) {
                File::delete($file);
                $deleted++;
            }
        }

        if ($deleted > 0) {
            $this->line("Đã xóa {$deleted} backup cũ hơn {$keepDays} ngày.");
            Log::info("[db:backup] Đã xóa {$deleted} backup cũ.");
        }
    }

    private function findMysqldump(): ?string
    {
        $envPath = env('MYSQLDUMP_PATH');
        if ($envPath && file_exists($envPath)) {
            return $envPath;
        }

        if (PHP_OS_FAMILY === 'Windows') {
            $candidates = glob('C:\\laragon\\bin\\mysql\\*\\bin\\mysqldump.exe');
            if (!empty($candidates)) {
                return end($candidates);
            }
        }

        $which = PHP_OS_FAMILY === 'Windows' ? 'where mysqldump 2>nul' : 'which mysqldump 2>/dev/null';
        exec($which, $out, $code);
        if ($code === 0 && !empty($out[0]) && trim($out[0]) !== '') {
            return trim($out[0]);
        }

        return null;
    }
}
