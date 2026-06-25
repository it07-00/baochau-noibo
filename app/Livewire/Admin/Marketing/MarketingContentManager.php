<?php

namespace App\Livewire\Admin\Marketing;

use App\Enums\Permission;
use App\Enums\Role;
use App\Models\MarketingContent;
use App\Models\User;
use App\Notifications\MarketingContentNotification;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class MarketingContentManager extends Component
{
    use WithFileUploads, WithPagination;

    // Form fields
    public string $formTitle = '';

    public string $formContent = '';

    public string $formScheduledAt = '';

    public array $newImages = [];

    public array $existingImages = [];

    public array $imagesToRemove = [];

    // State
    public bool $isEditing = false;

    public ?int $editingId = null;

    // Review
    public ?int $reviewingId = null;

    public string $reviewNote = '';

    // Detail
    public ?int $detailId = null;

    // Calendar
    public string $calendarMonth = '';

    public function mount(): void
    {
        $this->calendarMonth = now()->format('Y-m');
    }

    protected function rules(): array
    {
        return [
            'formTitle' => 'required|string|max:200',
            'formContent' => 'required|string',
            'formScheduledAt' => 'required|date',
            'newImages.*' => 'nullable|image|max:51200',
        ];
    }

    protected function messages(): array
    {
        return [
            'formTitle.required' => 'Tiêu đề không được để trống.',
            'formContent.required' => 'Nội dung không được để trống.',
            'formScheduledAt.required' => 'Vui lòng chọn ngày đăng dự kiến.',
            'newImages.*.image' => 'Tệp phải là hình ảnh.',
            'newImages.*.max' => 'Ảnh không được vượt quá 50MB.',
        ];
    }

    public function previousCalendarMonth(): void
    {
        $this->calendarMonth = $this->calendarMonthStart()
            ->subMonthNoOverflow()
            ->format('Y-m');
    }

    public function nextCalendarMonth(): void
    {
        $this->calendarMonth = $this->calendarMonthStart()
            ->addMonthNoOverflow()
            ->format('Y-m');
    }

    public function goToCurrentCalendarMonth(): void
    {
        $this->calendarMonth = now()->format('Y-m');
    }

    public function openCreate(): void
    {
        $this->authorizeMarketingPermission(Permission::ARTICLES_CREATE);

        $this->resetForm();
        $this->isEditing = false;
        $this->editingId = null;
        $this->dispatch('openContentFormModal');
    }

    public function openCreateForDate(string $date): void
    {
        $this->authorizeMarketingPermission(Permission::ARTICLES_CREATE);

        $scheduledAt = $this->parseCalendarDate($date);
        if (! $scheduledAt) {
            return;
        }

        $this->resetForm();
        $this->isEditing = false;
        $this->editingId = null;
        $this->formScheduledAt = $scheduledAt->toDateString();
        $this->dispatch('openContentFormModal');
    }

    public function openEdit(int $id): void
    {
        $this->authorizeMarketingPermission(Permission::ARTICLES_EDIT);

        $record = $this->authorizeOwn($id);
        if (! $record || ! $record->isEditable()) {
            return;
        }

        $this->isEditing = true;
        $this->editingId = $id;
        $this->formTitle = $record->title;
        $this->formContent = $record->content;
        $this->formScheduledAt = $record->scheduled_at?->format('Y-m-d') ?? '';
        $this->existingImages = $record->images ?? [];
        $this->newImages = [];
        $this->imagesToRemove = [];

        $this->dispatch('openContentFormModal');
    }

    public function openCalendarContent(int $id): void
    {
        $record = $this->authorizeOwn($id);
        if (! $record) {
            return;
        }

        $user = auth()->user();
        $isMarketing = $user && ($user->can(Permission::ARTICLES_CREATE->value) || $user->can(Permission::ARTICLES_EDIT->value)) && ! $user->hasRole(Role::TP_KINH_DOANH->value);

        if ($isMarketing && $user->can(Permission::ARTICLES_EDIT->value) && $record->isEditable()) {
            $this->openEdit($record->id);

            return;
        }

        $this->detailId = $record->id;
        $this->dispatch('openDetailModal');
    }

    public function save(): void
    {
        $this->authorizeMarketingPermission(
            $this->isEditing ? Permission::ARTICLES_EDIT : Permission::ARTICLES_CREATE
        );

        $this->validate();

        $user = auth()->user();
        $data = $this->formData();

        if ($this->isEditing) {
            if (! $this->editingId) {
                return;
            }

            $record = $this->authorizeOwn($this->editingId);
            if (! $record || ! $record->isEditable()) {
                return;
            }
            // Reset to draft when re-editing a rejected post
            if ($record->status === 'rejected') {
                $data['status'] = 'draft';
                $data['reviewer_note'] = null;
                $data['reviewer_id'] = null;
                $data['reviewed_at'] = null;
            }
            $record->update($data);
            $message = 'Đã cập nhật bài content.';
        } else {
            $data['user_id'] = $user->id;
            $data['status'] = 'draft';
            MarketingContent::create($data);
            $message = 'Đã tạo bài content mới.';
        }

        $this->resetForm();
        $this->dispatch('closeContentFormModal');
        $this->dispatch('swal:toast', ['type' => 'success', 'message' => $message]);
    }

    public function saveAndSubmitForReview(): void
    {
        $this->authorizeMarketingPermission(Permission::ARTICLES_EDIT);

        if (! $this->isEditing || ! $this->editingId) {
            return;
        }

        $record = $this->authorizeOwn($this->editingId);
        if (! $record || ! $record->isDraft()) {
            return;
        }

        $this->validate();
        $record->update($this->formData());

        $this->isEditing = false;
        $this->editingId = null;
        $this->resetForm();
        $this->dispatch('closeContentFormModal');

        $this->submitForReview($record->id);
    }

    public function submitForReview(int $id): void
    {
        $this->authorizeMarketingPermission(Permission::ARTICLES_EDIT);

        $record = $this->authorizeOwn($id);
        if (! $record || ! $record->isDraft()) {
            return;
        }

        $record->update(['status' => 'pending']);

        // Gửi thông báo cho tất cả TPKD
        $tpkdUsers = User::role(Role::TP_KINH_DOANH->value)->get();
        foreach ($tpkdUsers as $tpkdUser) {
            $tpkdUser->notify(new MarketingContentNotification($record, 'submitted'));
        }

        $this->detailId = null;
        $this->dispatch('closeDetailModal');
        $this->dispatch('swal:toast', ['type' => 'success', 'message' => 'Đã gửi duyệt thành công.']);
    }

    public function openReview(int $id): void
    {
        $this->authorizeReviewerAction();

        $record = MarketingContent::find($id);
        if (! $record || ! $record->isPending()) {
            return;
        }

        $this->reviewingId = $record->id;
        $this->reviewNote = '';
        $this->detailId = null;
        $this->dispatch('closeDetailModal');
        $this->dispatch('openReviewModal');
    }

    public function approve(): void
    {
        $this->authorizeReviewerAction();

        $record = MarketingContent::find($this->reviewingId);
        if (! $record || ! $record->isPending()) {
            return;
        }

        $record->update([
            'status' => 'approved',
            'reviewer_id' => auth()->id(),
            'reviewed_at' => now(),
            'reviewer_note' => null,
        ]);

        // Gửi thông báo cho người tạo bài viết
        if ($record->user) {
            $record->user->notify(new MarketingContentNotification($record, 'approved'));
        }

        $this->reviewingId = null;
        $this->dispatch('closeReviewModal');
        $this->dispatch('swal:toast', ['type' => 'success', 'message' => 'Đã duyệt bài content.']);
    }

    public function reject(): void
    {
        $this->authorizeReviewerAction();

        $this->validate(['reviewNote' => 'required|string|max:500'], [
            'reviewNote.required' => 'Vui lòng nhập lý do từ chối.',
        ]);

        $record = MarketingContent::find($this->reviewingId);
        if (! $record || ! $record->isPending()) {
            return;
        }

        $record->update([
            'status' => 'rejected',
            'reviewer_id' => auth()->id(),
            'reviewed_at' => now(),
            'reviewer_note' => $this->reviewNote,
        ]);

        // Gửi thông báo cho người tạo bài viết
        if ($record->user) {
            $record->user->notify(new MarketingContentNotification($record, 'rejected', $this->reviewNote));
        }

        $this->reviewingId = null;
        $this->reviewNote = '';
        $this->dispatch('closeReviewModal');
        $this->dispatch('swal:toast', ['type' => 'warning', 'message' => 'Đã từ chối bài content.']);
    }

    public function openDetail(int $id): void
    {
        $record = $this->authorizeOwn($id);
        if (! $record) {
            return;
        }

        $this->detailId = $record->id;
        $this->dispatch('openDetailModal');
    }

    public function deleteContent(int $id): void
    {
        $this->authorizeMarketingPermission(Permission::ARTICLES_DELETE);

        $record = $this->authorizeOwn($id);
        if (! $record || ! $record->isDraft()) {
            return;
        }

        foreach ($record->images ?? [] as $path) {
            Storage::disk('public')->delete($path);
        }

        $record->delete();
        $this->dispatch('swal:toast', ['type' => 'success', 'message' => 'Đã xóa bài content.']);
    }

    public function removeExistingImage(string $path): void
    {
        $this->authorizeMarketingPermission(Permission::ARTICLES_EDIT);

        $this->imagesToRemove[] = $path;
        $this->existingImages = array_values(array_filter($this->existingImages, fn ($p) => $p !== $path));
    }

    public function removeNewImage(int $index): void
    {
        $this->authorizeMarketingPermission(
            $this->isEditing ? Permission::ARTICLES_EDIT : Permission::ARTICLES_CREATE
        );

        array_splice($this->newImages, $index, 1);
    }

    private function authorizeMarketingPermission(Permission $permission): void
    {
        $user = auth()->user();

        abort_unless($user && $user->can($permission->value) && ! $user->hasRole(Role::TP_KINH_DOANH->value), 403);
    }

    private function authorizeReviewerAction(): void
    {
        abort_unless(auth()->check() && $this->isReviewer(), 403);
    }

    private function buildScopedQuery(bool $isMarketing): Builder
    {
        return MarketingContent::query();
    }

    private function authorizeOwn(int $id): ?MarketingContent
    {
        return MarketingContent::find($id);
    }

    private function resetForm(): void
    {
        $this->formTitle = '';
        $this->formContent = '';
        $this->formScheduledAt = '';
        $this->newImages = [];
        $this->existingImages = [];
        $this->imagesToRemove = [];
        $this->resetErrorBag();
    }

    private function formData(): array
    {
        return [
            'title' => $this->formTitle,
            'content' => $this->formContent,
            'scheduled_at' => $this->formScheduledAt,
            'images' => $this->storeFormImages() ?: null,
        ];
    }

    private function storeFormImages(): array
    {
        $storedPaths = $this->existingImages;

        // Remove images marked for deletion
        foreach ($this->imagesToRemove as $path) {
            Storage::disk('public')->delete($path);
            $storedPaths = array_values(array_filter($storedPaths, fn ($p) => $p !== $path));
        }

        // Store new images
        foreach ($this->newImages as $image) {
            $webpData = null;
            try {
                $sourcePath = $image->getRealPath();
                $imgData = file_get_contents($sourcePath);
                $im = imagecreatefromstring($imgData);
                if ($im !== false) {
                    imagealphablending($im, false);
                    imagesavealpha($im, true);

                    ob_start();
                    imagewebp($im, null, 85);
                    $webpData = ob_get_clean();
                    imagedestroy($im);
                }
            } catch (\Throwable $e) {
                logger()->error('WebP conversion failed: '.$e->getMessage());
            }

            if ($webpData !== null) {
                $filename = 'marketing-content/'.Str::random(40).'.webp';
                Storage::disk('public')->put($filename, $webpData);
                $storedPaths[] = $filename;
            } else {
                $storedPaths[] = $image->store('marketing-content', 'public');
            }
        }

        return $storedPaths;
    }

    private function isReviewer(): bool
    {
        return auth()->user()->hasRole(Role::TP_KINH_DOANH->value);
    }

    private function calendarMonthStart(): CarbonImmutable
    {
        if (! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $this->calendarMonth)) {
            $this->calendarMonth = now()->format('Y-m');
        }

        return CarbonImmutable::createFromFormat('!Y-m-d', $this->calendarMonth.'-01')
            ->startOfMonth();
    }

    private function daysUntilSchedule(CarbonInterface $scheduledAt): int
    {
        return (int) CarbonImmutable::today()
            ->diffInDays(CarbonImmutable::parse($scheduledAt->toDateString()), true);
    }

    private function buildCalendarDays(CarbonImmutable $monthStart, Collection $contents): array
    {
        $gridStart = $monthStart->startOfWeek(CarbonInterface::MONDAY);
        $gridEnd = $monthStart->endOfMonth()->endOfWeek(CarbonInterface::SUNDAY);
        $contentsByDate = $contents->groupBy(fn (MarketingContent $item) => $item->scheduled_at?->toDateString());
        $days = [];

        for ($day = $gridStart; $day->lessThanOrEqualTo($gridEnd); $day = $day->addDay()) {
            $days[] = [
                'date' => $day,
                'key' => $day->toDateString(),
                'isCurrentMonth' => $day->isSameMonth($monthStart),
                'isToday' => $day->isToday(),
                'items' => $contentsByDate->get($day->toDateString(), collect()),
            ];
        }

        return $days;
    }

    private function parseCalendarDate(string $date): ?CarbonImmutable
    {
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return null;
        }

        try {
            $parsed = CarbonImmutable::createFromFormat('!Y-m-d', $date);
        } catch (\Throwable) {
            return null;
        }

        if ($parsed->format('Y-m-d') !== $date) {
            return null;
        }

        return $parsed;
    }

    public function listScheduleIcon(?CarbonInterface $scheduledAt): string
    {
        if (! $scheduledAt) {
            return 'bi bi-calendar3';
        }

        if ($scheduledAt->isToday()) {
            return 'bi bi-lightning-charge';
        }

        if ($scheduledAt->isPast()) {
            return 'bi bi-exclamation-circle';
        }

        if ($this->daysUntilSchedule($scheduledAt) <= 7) {
            return 'bi bi-calendar-week';
        }

        return 'bi bi-calendar3';
    }

    public function listScheduleText(?CarbonInterface $scheduledAt): string
    {
        if (! $scheduledAt) {
            return 'Chưa chốt lịch đăng';
        }

        if ($scheduledAt->isToday()) {
            return 'Đăng hôm nay';
        }

        if ($scheduledAt->isPast()) {
            return 'Quá lịch '.$scheduledAt->format('d/m');
        }

        $daysUntil = $this->daysUntilSchedule($scheduledAt);

        if ($daysUntil <= 7) {
            return 'Trong '.$daysUntil.' ngày';
        }

        return 'Lên lịch '.$scheduledAt->format('d/m');
    }

    public function detailScheduleValue(?CarbonInterface $scheduledAt): string
    {
        return $scheduledAt?->format('d/m/Y') ?? 'Chưa chốt lịch';
    }

    public function detailScheduleHint(?CarbonInterface $scheduledAt): string
    {
        if (! $scheduledAt) {
            return 'Chưa có ngày đăng chính thức';
        }

        if ($scheduledAt->isToday()) {
            return 'Dự kiến đăng hôm nay';
        }

        if ($scheduledAt->isPast()) {
            return 'Đã quá lịch đăng';
        }

        $daysUntil = $this->daysUntilSchedule($scheduledAt);

        if ($daysUntil <= 7) {
            return 'Dự kiến đăng trong '.$daysUntil.' ngày';
        }

        return 'Đã lên lịch đăng';
    }

    public function formSchedulePreviewValue(): string
    {
        if (! $this->formScheduledAt) {
            return 'Chọn ngày đăng';
        }

        return CarbonImmutable::parse($this->formScheduledAt)->format('d/m/Y');
    }

    public function formSchedulePreviewHint(): string
    {
        if (! $this->formScheduledAt) {
            return 'Bài viết sẽ nằm trên lịch sau khi chọn ngày.';
        }

        return $this->detailScheduleHint(CarbonImmutable::parse($this->formScheduledAt));
    }

    public function render()
    {
        $user = auth()->user();
        $isMarketing = ($user->can(Permission::ARTICLES_CREATE->value) || $user->can(Permission::ARTICLES_EDIT->value)) && ! $user->hasRole(Role::TP_KINH_DOANH->value);

        $monthStart = $this->calendarMonthStart();
        $contents = $this->buildScopedQuery($isMarketing)
            ->with(['user', 'reviewer'])
            ->whereBetween('scheduled_at', [$monthStart->toDateString(), $monthStart->endOfMonth()->toDateString()])
            ->orderBy('scheduled_at')
            ->orderBy('created_at')
            ->get();

        $monthContents = $contents
            ->filter(fn (MarketingContent $item) => $item->scheduled_at?->isSameMonth($monthStart))
            ->values();

        $unscheduledContents = $this->buildScopedQuery($isMarketing)
            ->with(['user', 'reviewer'])
            ->whereNull('scheduled_at')
            ->latest()
            ->limit(8)
            ->get();

        $detailRecord = $this->detailId ? $this->authorizeOwn($this->detailId) : null;
        $editingRecord = $this->editingId ? $this->authorizeOwn($this->editingId) : null;
        $reviewRecord = ($this->reviewingId && $this->isReviewer())
            ? MarketingContent::with('user')->find($this->reviewingId)
            : null;

        return view('livewire.admin.marketing.marketing-content-manager', [
            'contents' => $contents,
            'calendarDays' => $this->buildCalendarDays($monthStart, $contents),
            'calendarMonthLabel' => 'Tháng '.$monthStart->format('m/Y'),
            'calendarStatusCounts' => $monthContents->countBy('status'),
            'monthContentsCount' => $monthContents->count(),
            'unscheduledContents' => $unscheduledContents,
            'statusLabels' => MarketingContent::$statusLabels,
            'statusColors' => MarketingContent::$statusColors,
            'isMarketing' => $isMarketing,
            'isReviewer' => $this->isReviewer(),
            'detailRecord' => $detailRecord,
            'editingRecord' => $editingRecord,
            'reviewRecord' => $reviewRecord,
        ])->layout('admin.layouts.app');
    }
}
