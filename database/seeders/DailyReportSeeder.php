<?php

namespace Database\Seeders;

use App\Models\DailyReport;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DailyReportSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $today = Carbon::today();
        $count = 0;
        $maxReports = 60;

        // Generate reports for the last 30 days
        for ($i = 0; $i < 30; $i++) {
            $date = $today->copy()->subDays($i);
            if ($date->isWeekend()) continue;

            // Randomly pick 2-4 users each day until we reach ~60
            $dailyUsers = $users->random(min($users->count(), rand(2, 4)));

            foreach ($dailyUsers as $user) {
                if ($count >= $maxReports) break 2;

                $status = 'Hoàn thành đúng kế hoạch';
                $issues = null;
                
                // Randomly assign some issues
                if (rand(1, 10) > 8) {
                    $status = 'Gặp vấn đề, cần hỗ trợ';
                    $issues = 'Gặp khó khăn trong việc kết nối với khách hàng hoặc tài liệu chưa đầy đủ.';
                } elseif (rand(1, 10) > 7) {
                    $status = 'Hoàn thành một phần';
                }

                DailyReport::create([
                    'user_id' => $user->id,
                    'date'    => $date,
                    'content' => "Thực hiện công việc ngày " . $date->format('d/m/Y') . " cho dự án " . rand(100, 999),
                    'status'  => $status,
                    'plan'    => 'Tiếp tục triển khai các bước tiếp theo trong quy trình.',
                    'issues'  => $issues,
                ]);

                $count++;
            }
        }
    }
}
