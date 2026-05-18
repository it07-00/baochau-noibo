<?php

namespace App\Livewire\Admin\InternalNotifications;

use App\Enums\Role;
use App\Models\User;
use App\Notifications\InternalNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;

class InternalNotificationManager extends Component
{
    public string $title = '';
    public string $body = '';
    public string $recipientType = 'all';
    public array $selectedRoles = [];
    public array $selectedUsers = [];

    protected function rules(): array
    {
        return [
            'title'          => 'required|string|max:200',
            'body'           => 'required|string|max:5000',
            'recipientType'  => 'required|in:all,roles,users',
            'selectedRoles'  => 'array',
            'selectedUsers'  => 'array',
        ];
    }

    protected function messages(): array
    {
        return [
            'title.required' => 'Tiêu đề không được để trống.',
            'body.required'  => 'Nội dung không được để trống.',
        ];
    }

    public function send(): void
    {
        $this->validate();

        if ($this->recipientType === 'roles' && empty($this->selectedRoles)) {
            $this->addError('selectedRoles', 'Vui lòng chọn ít nhất một vai trò.');
            return;
        }

        if ($this->recipientType === 'users' && empty($this->selectedUsers)) {
            $this->addError('selectedUsers', 'Vui lòng chọn ít nhất một người nhận.');
            return;
        }

        $authUser = auth()->user();
        $batchId = (string) Str::uuid();
        $recipients = $this->resolveRecipients();

        if ($recipients->isEmpty()) {
            $this->dispatch('swal:toast', ['type' => 'warning', 'message' => 'Không có người nhận phù hợp.']);
            return;
        }

        $notification = new InternalNotification(
            title: $this->title,
            body: $this->body,
            senderId: $authUser->id,
            senderName: $authUser->name,
            batchId: $batchId,
            recipientsLabel: $this->buildRecipientsLabel(),
        );

        foreach ($recipients as $recipient) {
            $recipient->notify($notification);
        }

        $this->reset(['title', 'body', 'selectedRoles', 'selectedUsers']);
        $this->recipientType = 'all';

        $this->dispatch('closeComposeModal');
        $this->dispatch('swal:toast', [
            'type'    => 'success',
            'message' => 'Đã gửi thông báo đến ' . $recipients->count() . ' người.',
        ]);
    }

    private function resolveRecipients()
    {
        $query = User::where('is_active', true)->where('id', '!=', auth()->id());

        if ($this->recipientType === 'roles' && !empty($this->selectedRoles)) {
            $query->whereHas('roles', fn ($q) => $q->whereIn('name', $this->selectedRoles));
        } elseif ($this->recipientType === 'users' && !empty($this->selectedUsers)) {
            $query->whereIn('id', $this->selectedUsers);
        }

        return $query->get();
    }

    private function buildRecipientsLabel(): string
    {
        if ($this->recipientType === 'all') {
            return 'Tất cả';
        }

        if ($this->recipientType === 'roles') {
            return 'Vai trò: ' . collect($this->selectedRoles)
                ->map(fn ($r) => Role::tryFrom($r)?->label() ?? $r)
                ->join(', ');
        }

        return count($this->selectedUsers) . ' người dùng được chọn';
    }

    public function render()
    {
        $authUser = auth()->user();

        $sentNotifications = DB::table('notifications')
            ->selectRaw("
                JSON_UNQUOTE(JSON_EXTRACT(data, '$.batch_id')) as batch_id,
                ANY_VALUE(JSON_UNQUOTE(JSON_EXTRACT(data, '$.contract_label'))) as title,
                ANY_VALUE(JSON_UNQUOTE(JSON_EXTRACT(data, '$.message'))) as message,
                ANY_VALUE(JSON_UNQUOTE(JSON_EXTRACT(data, '$.recipients_label'))) as recipients_label,
                COUNT(*) as recipient_count,
                MIN(created_at) as sent_at
            ")
            ->where('type', InternalNotification::class)
            ->whereRaw("CAST(JSON_UNQUOTE(JSON_EXTRACT(data, '$.sender_id')) AS UNSIGNED) = ?", [$authUser->id])
            ->groupByRaw("JSON_UNQUOTE(JSON_EXTRACT(data, '$.batch_id'))")
            ->orderByDesc('sent_at')
            ->limit(50)
            ->get();

        $allRoles = collect(Role::cases())
            ->map(fn ($r) => ['value' => $r->value, 'label' => $r->label()]);

        $allUsers = User::where('is_active', true)
            ->where('id', '!=', $authUser->id)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('livewire.admin.internal-notifications.manager', [
            'sentNotifications' => $sentNotifications,
            'allRoles'          => $allRoles,
            'allUsers'          => $allUsers,
        ])->layout('admin.layouts.app');
    }
}
