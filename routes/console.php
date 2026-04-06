<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Storage;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('avatars:migrate {--from=public}', function () {
    $sourceDisk = (string) $this->option('from');
    $targetDisk = (string) config('filesystems.avatar_disk', 'public');

    if ($sourceDisk === $targetDisk) {
        $this->warn("Nguon va dich giong nhau ({$sourceDisk}). Khong can migrate.");

        return self::SUCCESS;
    }

    $this->info("Dang migrate avatar tu disk '{$sourceDisk}' sang '{$targetDisk}'...");

    $files = Storage::disk($sourceDisk)->allFiles('avatars');
    $copied = 0;
    $skipped = 0;
    $failed = 0;

    foreach ($files as $file) {
        try {
            if (Storage::disk($targetDisk)->exists($file)) {
                $skipped++;
                continue;
            }

            Storage::disk($targetDisk)->put(
                $file,
                Storage::disk($sourceDisk)->get($file),
                ['visibility' => 'public']
            );

            $copied++;
        } catch (\Throwable $e) {
            $failed++;
            $this->error("Loi file {$file}: {$e->getMessage()}");
        }
    }

    $this->newLine();
    $this->info("Hoan tat. Copied: {$copied}, Skipped: {$skipped}, Failed: {$failed}");

    return $failed > 0 ? self::FAILURE : self::SUCCESS;
})->purpose('Migrate avatar files to the configured avatar disk');

Artisan::command('uploads:migrate {--from=public} {--path=*}', function () {
    $sourceDisk = (string) $this->option('from');
    $targetDisk = (string) config('filesystems.upload_disk', 'public');

    if ($sourceDisk === $targetDisk) {
        $this->warn("Nguon va dich giong nhau ({$sourceDisk}). Khong can migrate.");

        return self::SUCCESS;
    }

    $paths = (array) $this->option('path');
    if (empty($paths)) {
        $paths = ['internal-docs', 'contract-files', 'sales/renewal'];
    }

    $this->info("Dang migrate upload files tu disk '{$sourceDisk}' sang '{$targetDisk}'...");

    $copied = 0;
    $skipped = 0;
    $failed = 0;

    foreach ($paths as $pathPrefix) {
        $files = Storage::disk($sourceDisk)->allFiles($pathPrefix);

        foreach ($files as $file) {
            try {
                if (Storage::disk($targetDisk)->exists($file)) {
                    $skipped++;
                    continue;
                }

                Storage::disk($targetDisk)->put(
                    $file,
                    Storage::disk($sourceDisk)->get($file),
                    ['visibility' => 'public']
                );

                $copied++;
            } catch (\Throwable $e) {
                $failed++;
                $this->error("Loi file {$file}: {$e->getMessage()}");
            }
        }
    }

    $this->newLine();
    $this->info("Hoan tat. Copied: {$copied}, Skipped: {$skipped}, Failed: {$failed}");

    return $failed > 0 ? self::FAILURE : self::SUCCESS;
})->purpose('Migrate uploaded documents/workflow files to the configured upload disk');

// Nhắc nhở báo cáo ngày lúc 16:30 hằng ngày (trừ Chủ nhật)
Schedule::call(function () {
    $usersWithoutReport = \App\Models\User::where('is_active', true)
        ->whereDoesntHave('roles', fn ($q) => $q->where('name', 'giam-doc'))
        ->whereDoesntHave('dailyReports', fn ($q) => $q->whereDate('date', today()))
        ->get();

    foreach ($usersWithoutReport as $user) {
        $user->notify(new \App\Notifications\DailyReportReminderNotification());
    }
})->weekdays()->dailyAt('16:30')->name('daily-report-reminder');
