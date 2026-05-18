<?php

namespace App\Livewire\Admin\Marketing;

use App\Enums\Role;
use Illuminate\Database\Eloquent\Builder;
use App\Models\MarketingContent;
use Illuminate\Support\Facades\Storage;
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

    protected function rules(): array
    {
        return [
            'formTitle'        => 'required|string|max:200',
            'formContent'      => 'required|string',
            'formScheduledAt'  => 'required|date',
            'newImages.*'      => 'nullable|image|max:10240',
        ];
    }

    protected function messages(): array
    {
        return [
            'formTitle.required'     => 'Tiêu đề không được để trống.',
            'formContent.required'   => 'Nội dung không được để trống.',
            'formScheduledAt.required' => 'Vui lòng chọn ngày đăng dự kiến.',
            'newImages.*.image'      => 'Tệp phải là hình ảnh.',
            'newImages.*.max'        => 'Ảnh không được vượt quá 10MB.',
        ];
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->isEditing = false;
        $this->editingId = null;
        $this->dispatch('openContentFormModal');
    }

    public function openEdit(int $id): void
    {
        $record = $this->authorizeOwn($id);
        if (!$record || !$record->isEditable()) {
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

    public function save(): void
    {
        $this->validate();

        $user = auth()->user();

        $storedPaths = $this->existingImages;

        // Remove images marked for deletion
        foreach ($this->imagesToRemove as $path) {
            Storage::disk('public')->delete($path);
            $storedPaths = array_values(array_filter($storedPaths, fn ($p) => $p !== $path));
        }

        // Store new images
        foreach ($this->newImages as $image) {
            $storedPaths[] = $image->store('marketing-content', 'public');
        }

        $data = [
            'title'        => $this->formTitle,
            'content'      => $this->formContent,
            'scheduled_at' => $this->formScheduledAt,
            'images'       => $storedPaths ?: null,
        ];

        if ($this->isEditing) {
            $record = $this->authorizeOwn($this->editingId);
            if (!$record || !$record->isEditable()) {
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

    public function submitForReview(int $id): void
    {
        $record = $this->authorizeOwn($id);
        if (!$record || !$record->isDraft()) {
            return;
        }

        $record->update(['status' => 'pending']);
        $this->dispatch('swal:toast', ['type' => 'success', 'message' => 'Đã gửi duyệt thành công.']);
    }

    public function openReview(int $id): void
    {
        $this->reviewingId = $id;
        $this->reviewNote = '';
        $this->dispatch('openReviewModal');
    }

    public function approve(): void
    {
        $record = MarketingContent::find($this->reviewingId);
        if (!$record || !$record->isPending()) {
            return;
        }

        $record->update([
            'status'      => 'approved',
            'reviewer_id' => auth()->id(),
            'reviewed_at' => now(),
            'reviewer_note' => null,
        ]);

        $this->reviewingId = null;
        $this->dispatch('closeReviewModal');
        $this->dispatch('swal:toast', ['type' => 'success', 'message' => 'Đã duyệt bài content.']);
    }

    public function reject(): void
    {
        $this->validate(['reviewNote' => 'required|string|max:500'], [
            'reviewNote.required' => 'Vui lòng nhập lý do từ chối.',
        ]);

        $record = MarketingContent::find($this->reviewingId);
        if (!$record || !$record->isPending()) {
            return;
        }

        $record->update([
            'status'        => 'rejected',
            'reviewer_id'   => auth()->id(),
            'reviewed_at'   => now(),
            'reviewer_note' => $this->reviewNote,
        ]);

        $this->reviewingId = null;
        $this->reviewNote = '';
        $this->dispatch('closeReviewModal');
        $this->dispatch('swal:toast', ['type' => 'warning', 'message' => 'Đã từ chối bài content.']);
    }

    public function openDetail(int $id): void
    {
        $this->detailId = $id;
        $this->dispatch('openDetailModal');
    }

    public function deleteContent(int $id): void
    {
        $record = $this->authorizeOwn($id);
        if (!$record || !$record->isDraft()) {
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
        $this->imagesToRemove[] = $path;
        $this->existingImages = array_values(array_filter($this->existingImages, fn ($p) => $p !== $path));
    }

    public function removeNewImage(int $index): void
    {
        array_splice($this->newImages, $index, 1);
    }

    private function buildScopedQuery(bool $isMarketing): Builder
    {
        $user = auth()->user();

        return MarketingContent::query()
            ->when($isMarketing, fn (Builder $builder) => $builder->where('user_id', $user->id));
    }

    private function authorizeOwn(int $id): ?MarketingContent
    {
        $user = auth()->user();
        $record = MarketingContent::find($id);
        if (!$record) {
            return null;
        }
        if ($user->hasRole(Role::MARKETING->value) && $record->user_id !== $user->id) {
            return null;
        }
        return $record;
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

    private function isReviewer(): bool
    {
        return auth()->user()->hasAnyRole([
            Role::TP_KINH_DOANH->value,
            Role::GIAM_DOC->value,
            Role::IT->value,
        ]);
    }

    public function render()
    {
        $user = auth()->user();
        $isMarketing = $user->hasRole(Role::MARKETING->value);

        $scopedQuery = $this->buildScopedQuery($isMarketing);

        $contents = $scopedQuery
            ->with(['user', 'reviewer'])
            ->latest()
            ->paginate(12);

        $detailRecord = $this->detailId ? MarketingContent::with(['user', 'reviewer'])->find($this->detailId) : null;
        $reviewRecord = $this->reviewingId ? MarketingContent::with('user')->find($this->reviewingId) : null;

        return view('livewire.admin.marketing.marketing-content-manager', [
            'contents'     => $contents,
            'statusLabels' => MarketingContent::$statusLabels,
            'statusColors' => MarketingContent::$statusColors,
            'isMarketing'  => $isMarketing,
            'isReviewer'   => $this->isReviewer(),
            'detailRecord' => $detailRecord,
            'reviewRecord' => $reviewRecord,
        ])->layout('admin.layouts.app');
    }
}
