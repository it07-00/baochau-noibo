<?php

namespace App\Actions\DailyReports;

use App\Enums\Role;
use App\Models\DailyReport;
use App\Models\User;
use App\Notifications\DailyReportSubmittedNotification;

final class SubmitDailyReportAction
{
    /**
     * Lưu hoặc cập nhật báo cáo ngày, rồi gửi thông báo cho Giám đốc/TPKD.
     */
    public function execute(
        User   $reporter,
        string $date,
        string $content,
        string $status,
        string $plan = '',
        string $issues = '',
    ): DailyReport {
        $report = DailyReport::updateOrCreate(
            ['user_id' => $reporter->id, 'date' => $date],
            [
                'content' => clean($content),
                'status'  => $status,
                'plan'    => $plan ?: null,
                'issues'  => $issues ?: null,
            ]
        );

        $this->notifyManagers($reporter, $date);

        return $report;
    }

    private function notifyManagers(User $reporter, string $date): void
    {
        $recipients = User::role(Role::GIAM_DOC->value)->get();

        if ($reporter->hasRole(Role::KINH_DOANH->value)) {
            $recipients = $recipients->merge(User::role(Role::TP_KINH_DOANH->value)->get());
        }

        foreach ($recipients->unique('id') as $recipient) {
            /** @var User $recipient */
            if ($recipient->id !== $reporter->id) {
                $recipient->notify(new DailyReportSubmittedNotification($reporter->name, $date));
            }
        }
    }
}
