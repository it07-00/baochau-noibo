<?php

namespace App\Livewire\Admin\Handlers;

use App\Models\ContractWaste;
use App\Models\Handler;
use Livewire\Component;
use Livewire\WithPagination;

class HandlerManager extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public string $search = '';
    public bool $showModal = false;
    public bool $isEditing = false;
    public ?int $editingId = null;

    public array $formData = [
        'name' => '',
        'phone' => '',
        'address' => '',
    ];

    public function paginationView()
    {
        return 'livewire.admin.users.pagination';
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->isEditing = false;
        $this->showModal = true;
        $this->dispatch('openHandlerFormModal');
    }

    public function openEdit(int $id): void
    {
        $handler = Handler::findOrFail($id);

        $this->editingId = $handler->id;
        $this->formData = [
            'name' => (string) $handler->name,
            'phone' => (string) ($handler->phone ?? ''),
            'address' => (string) ($handler->address ?? ''),
        ];

        $this->isEditing = true;
        $this->showModal = true;
        $this->dispatch('openHandlerFormModal');
    }

    public function save(): void
    {
        $this->validate([
            'formData.name' => 'required|string|max:255|unique:handlers,name' . ($this->editingId ? ',' . $this->editingId : ''),
            'formData.phone' => 'nullable|string|max:30',
            'formData.address' => 'nullable|string|max:2000',
        ], [], [
            'formData.name' => 'tên chủ xử lý',
            'formData.phone' => 'số điện thoại',
            'formData.address' => 'địa chỉ',
        ]);

        $data = [
            'name' => trim((string) $this->formData['name']),
            'phone' => $this->formData['phone'] !== '' ? trim((string) $this->formData['phone']) : null,
            'address' => $this->formData['address'] !== '' ? trim((string) $this->formData['address']) : null,
        ];

        if ($this->isEditing && $this->editingId) {
            Handler::whereKey($this->editingId)->update($data);
            $message = 'Cập nhật chủ xử lý thành công.';
        } else {
            Handler::create($data);
            $message = 'Thêm chủ xử lý thành công.';
        }

        $this->dispatch('closeHandlerFormModal');
        $this->dispatch('swal:toast', ['type' => 'success', 'message' => $message]);
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        $handler = Handler::findOrFail($id);
        $usedContracts = ContractWaste::where('handler_id', $id)->count();

        if ($usedContracts > 0) {
            $this->dispatch('swal:toast', [
                'type' => 'error',
                'message' => 'Không thể xóa vì Chủ xử lý đang được dùng trong hợp đồng.',
            ]);
            return;
        }

        $handler->delete();
        $this->dispatch('swal:toast', ['type' => 'success', 'message' => 'Đã xóa Chủ xử lý.']);
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->formData = [
            'name' => '',
            'phone' => '',
            'address' => '',
        ];
        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function render()
    {
        $handlers = Handler::query()
            ->withCount('contracts')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('phone', 'like', '%' . $this->search . '%')
                        ->orWhere('address', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.admin.handlers.handler-manager', [
            'handlers' => $handlers,
            'totalHandlers' => Handler::count(),
        ])->layout('admin.layouts.app');
    }
}
