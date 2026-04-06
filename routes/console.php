<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

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
