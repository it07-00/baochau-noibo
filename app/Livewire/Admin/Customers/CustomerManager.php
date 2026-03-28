<?php

namespace App\Livewire\Admin\Customers;

use App\Models\Customer;
use Livewire\Component;
use Livewire\WithPagination;

class CustomerManager extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public string $search = '';
    public bool $showModal = false;
    public bool $isEditing = false;
    public ?int $editingId = null;

    public array $formData = [
        'name' => '',
        'tax_code' => '',
        'phone' => '',
        'email' => '',
        'address' => '',
        'province' => '',
        'representative' => '',
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
        $this->dispatch('openCustomerFormModal');
    }

    public function openEdit(int $id): void
    {
        $customer = Customer::findOrFail($id);

        $this->editingId = $customer->id;
        $this->formData = [
            'name' => (string) $customer->name,
            'tax_code' => (string) ($customer->tax_code ?? ''),
            'phone' => (string) ($customer->phone ?? ''),
            'email' => (string) ($customer->email ?? ''),
            'address' => (string) ($customer->address ?? ''),
            'province' => (string) ($customer->province ?? ''),
            'representative' => (string) ($customer->representative ?? ''),
        ];

        $this->isEditing = true;
        $this->showModal = true;
        $this->dispatch('openCustomerFormModal');
    }

    public function save(): void
    {
        $this->validate([
            'formData.name' => 'required|string|max:255|unique:customers,name' . ($this->editingId ? ',' . $this->editingId : ''),
            'formData.tax_code' => 'nullable|string|max:50',
            'formData.phone' => 'nullable|string|max:30',
            'formData.email' => 'nullable|email|max:255',
            'formData.address' => 'nullable|string|max:2000',
            'formData.province' => 'nullable|string|max:255',
            'formData.representative' => 'nullable|string|max:255',
        ], [], [
            'formData.name' => 'tên khách hàng',
            'formData.tax_code' => 'mã số thuế',
            'formData.phone' => 'số điện thoại',
            'formData.email' => 'email',
            'formData.address' => 'địa chỉ',
            'formData.province' => 'tỉnh thành',
            'formData.representative' => 'người đại diện',
        ]);

        $data = [
            'name' => trim((string) $this->formData['name']),
            'tax_code' => $this->formData['tax_code'] !== '' ? trim((string) $this->formData['tax_code']) : null,
            'phone' => $this->formData['phone'] !== '' ? trim((string) $this->formData['phone']) : null,
            'email' => $this->formData['email'] !== '' ? trim((string) $this->formData['email']) : null,
            'address' => $this->formData['address'] !== '' ? trim((string) $this->formData['address']) : null,
            'province' => $this->formData['province'] !== '' ? trim((string) $this->formData['province']) : null,
            'representative' => $this->formData['representative'] !== '' ? trim((string) $this->formData['representative']) : null,
        ];

        if ($this->isEditing && $this->editingId) {
            Customer::whereKey($this->editingId)->update($data);
            $message = 'Cập nhật khách hàng thành công.';
        } else {
            Customer::create($data);
            $message = 'Thêm khách hàng thành công.';
        }

        $this->dispatch('closeCustomerFormModal');
        $this->dispatch('swal:toast', ['type' => 'success', 'message' => $message]);
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        $customer = Customer::findOrFail($id);

        $contractCount = $customer->contracts()->count()
            + $customer->contractsConsulting()->count()
            + $customer->contractsCommercial()->count()
            + $customer->contractsProject()->count()
            + $customer->contractsEnergy()->count()
            + $customer->contractsSustainability()->count();

        if ($contractCount > 0) {
            $this->dispatch('swal:toast', [
                'type' => 'error',
                'message' => 'Không thể xóa vì khách hàng đang được dùng trong hợp đồng.',
            ]);
            return;
        }

        $customer->delete();
        $this->dispatch('swal:toast', ['type' => 'success', 'message' => 'Đã xóa khách hàng.']);
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->formData = [
            'name' => '',
            'tax_code' => '',
            'phone' => '',
            'email' => '',
            'address' => '',
            'province' => '',
            'representative' => '',
        ];
        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function render()
    {
        $customers = Customer::query()
            ->withCount(['contracts', 'contractsConsulting', 'contractsCommercial', 'contractsProject', 'contractsEnergy', 'contractsSustainability'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('tax_code', 'like', '%' . $this->search . '%')
                        ->orWhere('phone', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('name')
            ->paginate(15);

        return view('livewire.admin.customers.customer-manager', [
            'customers' => $customers,
            'provinces' => \App\Support\VietnamProvinces::list(),
        ])->layout('admin.layouts.app');
    }
}
