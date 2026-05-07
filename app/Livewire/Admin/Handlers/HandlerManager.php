<?php

namespace App\Livewire\Admin\Handlers;

use App\Enums\Permission;
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
        abort_unless(
            auth()->user()->can($this->isEditing ? Permission::HANDLERS_EDIT->value : Permission::HANDLERS_CREATE->value),
            403
        );

        $this->validate([
            'formData.name' => 'required|string|max:255|unique:handlers,name' . ($this->editingId ? ',' . $this->editingId : ''),
            'formData.phone' => 'nullable|string|max:30',
            'formData.address' => 'nullable|string|max:2000',
        ], [], [
            'formData.name' => 'tên nhà thầu phụ',
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
            $message = 'Cập nhật nhà thầu phụ thành công.';
        } else {
            Handler::create($data);
            $message = 'Thêm nhà thầu phụ thành công.';
        }

        $this->dispatch('closeHandlerFormModal');
        $this->dispatch('swal:toast', ['type' => 'success', 'message' => $message]);
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        abort_unless(auth()->user()->can(Permission::HANDLERS_DELETE->value), 403);

        $handler = Handler::findOrFail($id);

        $usedCount = \App\Models\ContractWaste::where('handler_id', $id)->count()
            + \App\Models\ContractLegal::where('handler_id', $id)->count()
            + \App\Models\ContractTechnical::where('handler_id', $id)->count()
            + \App\Models\ContractResearch::where('handler_id', $id)->count()
            + \App\Models\ContractSustainability::where('handler_id', $id)->count()
            + \App\Models\ContractEmission::where('handler_id', $id)->count();

        if ($usedCount > 0) {
            $this->dispatch('swal:toast', [
                'type' => 'error',
                'message' => 'Không thể xóa vì nhà thầu phụ đang được dùng trong hợp đồng.',
            ]);
            return;
        }

        $handler->delete();
        $this->dispatch('swal:toast', ['type' => 'success', 'message' => 'Đã xóa nhà thầu phụ.']);
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
            ->withCount([
                'contracts',
                'contractLegals',
                'contractTechnicals',
                'contractResearches',
                'contractSustainabilities',
                'contractEmissions',
            ])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('phone', 'like', '%' . $this->search . '%')
                        ->orWhere('address', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('name')
            ->paginate(10)
            ->through(function ($h) {
                $h->contracts_count =
                    $h->contracts_count +
                    $h->contract_legals_count +
                    $h->contract_technicals_count +
                    $h->contract_researches_count +
                    $h->contract_sustainabilities_count +
                    $h->contract_emissions_count;
                return $h;
            });

        return view('livewire.admin.handlers.handler-manager', [
            'handlers' => $handlers,
            'totalHandlers' => Handler::count(),
        ])->layout('admin.layouts.app');
    }
}
