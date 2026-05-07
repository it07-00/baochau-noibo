<?php

namespace App\Services;

use App\Enums\CommissionRequestStatus;
use App\Enums\Role;
use App\Models\CommissionRequest;
use App\Models\User;
use App\Notifications\CommissionRequestStatusUpdatedNotification;
use App\Notifications\CommissionRequestSubmittedNotification;

class CommissionService
{
    public function approve(CommissionRequest $request, User $actor): void
    {
        $request->update([
            'status'       => CommissionRequestStatus::DA_CHI->value,
            'processed_at' => now(),
        ]);

        $this->notifyRequesterStatusUpdate($request, CommissionRequestStatus::DA_CHI->value, $actor);
    }

    public function reject(CommissionRequest $request, string $reason, User $actor): void
    {
        $mergedNotes = trim(
            ($request->notes ? rtrim($request->notes) . "\n\n" : '')
            . 'Lý do từ chối (kế toán): ' . $reason
        );

        $request->update([
            'status'       => CommissionRequestStatus::TU_CHOI->value,
            'processed_at' => now(),
            'notes'        => $mergedNotes,
        ]);

        $this->notifyRequesterStatusUpdate($request, CommissionRequestStatus::TU_CHOI->value, $actor, $reason);
    }

    public function createRequest(array $data, User $creator): CommissionRequest
    {
        $request = CommissionRequest::create($data);
        $this->notifyAccountants($request, $creator);
        return $request;
    }

    private function notifyRequesterStatusUpdate(
        CommissionRequest $request,
        string $status,
        User $actor,
        ?string $reason = null
    ): void {
        $requester = $request->user;
        if (!$requester) {
            return;
        }

        $contractLabel = (string) ($request->contract?->shd_bc ?: ('#' . $request->id));

        $requester->notify(new CommissionRequestStatusUpdatedNotification(
            status: $status,
            processedByName: (string) $actor->name,
            contractLabel: $contractLabel,
            amount: (string) $request->amount,
            requestId: (int) $request->id,
            reason: $reason,
        ));
    }

    private function notifyAccountants(CommissionRequest $request, User $creator): void
    {
        $contractLabel = (string) ($request->contract?->shd_bc ?: ('#' . $request->id));

        $recipients = User::role(Role::KE_TOAN->value)->get()->unique('id');

        foreach ($recipients as $recipient) {
            /** @var User $recipient */
            if ($recipient->id === $creator->id) {
                continue;
            }

            $recipient->notify(new CommissionRequestSubmittedNotification(
                requesterName: (string) $creator->name,
                contractLabel: $contractLabel,
                amount: (string) $request->amount,
                requestId: (int) $request->id,
            ));
        }
    }
}
